from django.template import engines, TemplateSyntaxError
from django.shortcuts import render, redirect
import pytz
import json
import datetime as dt
from django.conf import settings

convert_paths = {
    "id": {
        "field": "_id"
    },
    "actor": {
        "field": "_source.actor.account.name"
    },
    "verb": {
        "field": "_source.verb.id",
        "translations": {
            "https://w3id.org/xapi/adl/verbs/logged-in": "s'est connecté(e)",
            "https://w3id.org/xapi/adl/verbs/logged-out": "s'est déconnecté(e)",
            "http://vocab.xapi.fr/verbs/navigated-in": "a navigué vers",
            "http://adlnet.gov/expapi/verbs/answered": "a répondu à",
            "http://vocab.xapi.fr/verbs/graded": "a obtenu",
        }
    },
    "system": {
        "field": "_source.system.definition.name.en",
        "translations": {
            "ISAE-SUPAERO online": "Online",
            "ISAE-SUPAERO micro-learning": "ADN"
        }
    },
    "system_id": {
        "field": "_source.system.id",
    },
    "system_color": {
        "field": "_source.system.definition.name.en",
        "translations": {
            "ISAE-SUPAERO online": "primary",
            "ISAE-SUPAERO micro-learning": "warning"
        }
    },
    "date": {
        "field": "fields.timestamp",
        "transform": "date"
    },
    "hour": {
        "field": "fields.timestamp",
        "transform": "hour"
    },
    "timestamp": {
        "field": "fields.timestamp",
        "transform": "timestamp"
    },
    "object": {
        "field": "_source.object.definition.name.en|_source.object.definition.name.en-US|_source.object.definition.name.fr"
    },
    "score": {
        "field": "_source.result.score.raw"
    },
    "score_max": {
        "field": "_source.result.score.max"
    },
    "score_scaled": {
        "field": "_source.result.score.scaled"
    },
    "source": {
        "field": "_source",
        "transform": "stringify"
    },
    "object_type": {
        "field": "_source.object.type"
    },
    "object_id": {
        "field": "_source.object.id"
    }
}

def template_from_string(template_string, using=None):
    """
    Convert a string into a template object,
    using a given template engine or using the default backends 
    from settings.TEMPLATES if no engine was specified.
    """
    # This function is based on django.template.loader.get_template, 
    # but uses Engine.from_string instead of Engine.get_template.
    chain = []
    engine_list = engines.all() if using is None else [engines[using]]
    for engine in engine_list:
        try:
            return engine.from_string(template_string)
        except TemplateSyntaxError as e:
            chain.append(e)
    raise TemplateSyntaxError(template_string, chain=chain)

verbs_to_phrases = {
    "https://w3id.org/xapi/adl/verbs/logged-in": "{{ t.actor }} {{ t.verb }}",
    "https://w3id.org/xapi/adl/verbs/logged-out": "{{ t.actor }} {{ t.verb }}",
    "http://vocab.xapi.fr/verbs/navigated-in": "{{ t.actor }} {{ t.verb }} {{ t.object }}",
    "http://adlnet.gov/expapi/verbs/answered": "{{ t.actor }} {{ t.verb }} {{ t.object }} ({{t.score}}/{{t.score_max}})",
    "http://vocab.xapi.fr/verbs/graded": "{{ t.actor }} {{ t.verb }}  {{t.score}}/{{t.score_max}} sur {{ t.object }}"
}

def timezonize(date):
    date = date.replace(tzinfo=pytz.timezone('UTC'))
    date = date.astimezone(pytz.timezone('Europe/Paris'))
    return date

def timezonize_format(date, format):
    return timezonize(date).strftime(format)

# Create your views here.
def convert_trace(trace):
    res = {}
    for key in convert_paths.keys():
        config = convert_paths[key]
        fields = config["field"].split("|")
        for field in fields:
            paths = field.split(".")
            val = trace
            i = 0
            while val and i < len(paths):
                val = val.get(paths[i])
                i = i + 1

            if val:
                break
        
        if "translations" in config and val in config["translations"]:
            val = config["translations"][val]

        if "transform" in config and val:
            if config["transform"] == "date":
                val = timezonize_format(dt.datetime.fromtimestamp(val[0]/1000), "%d/%m/%Y")
            if config["transform"] == "hour":
                val = timezonize_format(dt.datetime.fromtimestamp(val[0]/1000), "%H:%M:%S")
            if config["transform"] == "timestamp":
                val = val[0]
            if config["transform"] == "stringify":
                val = json.dumps(val, indent=4, sort_keys=True)

        res[key] = val
    verb = trace["_source"]["verb"]["id"]
    if verb in verbs_to_phrases:
        t_string = verbs_to_phrases[verb]
        template = template_from_string(t_string)
        context = {"t": res}
        res["phrase"] = template.render(context)
    else:
        res["phrase"] = res
        del res["phrase"]["phrase"]

    return res


class Helper():
    def __init__(self, request):
        from elasticsearch import Elasticsearch
        self.request = request
        self.es = Elasticsearch(["idea-db"])
        self.index = "xapi_adn_enriched"
        self.global_range_end =  (dt.datetime.now().timestamp() * 1000) + 24 * 60 * 60 * 1000
        self.global_range_start =  self.global_range_end - 60 * 24 * 60 * 60 * 1000
        
        self.daterangequery = {"timestamp": {
                                "gte": self.global_range_start,
                                "lte": self.global_range_end
                            }
                    }
        self.traces_range = request.GET.get('traces_range')
        if self.traces_range:
            self.traces_range_start = int(self.traces_range)
            self.traces_range_end = self.traces_range_start + 3 * 60 * 60 * 1000
        else:
            self.traces_range_start = self.global_range_start
            self.traces_range_end = self.global_range_end
        self.daterangequery_traces = { "timestamp": {
                        "gte": self.traces_range_start,
                        "lt": self.traces_range_end
                    }
                }
        self.error_response = None
        if not request.user.is_authenticated:
            self.error_response = redirect("cas_ng_login")

        elif request.user.username not in settings.AUTHORIZED_USERS:
            self.error_response = render(request, 'dash/error.html', {"error": "Not authorized: {}".format(request.user.username)})



    def convert_traces(self, traces):
        return [convert_trace(trace) for trace in traces]

    def aggregate(self, id_field, description_field):
        choices = self.es.search(index=self.index, size=0, filter_path="aggregations.agg.buckets", body={
            "query": {"range": self.daterangequery},
            "aggs": {
                "agg": {
                    "terms": {
                        "field": id_field,
                        "size": 5000
                    },
                    "aggs": {
                        "name": {"terms": {"field": description_field, "size": 1}},
                        "system": {"terms": {"field": "system.id.keyword", "size": 1}},
                        "type": {"terms": {"field": "object.type.keyword", "size": 1}}
                    }
                }
            }
        })
        choices = choices["aggregations"]["agg"]["buckets"]

        for choice in choices:
            def get_(key):
                choice[key] = choice[key]["buckets"][0]["key"] if choice[key]["buckets"] else ""
            get_("name")
            get_("system")
            get_("type")

        return choices

    def get_object_occurences(self, id, size = 1000000):
        obj = self.es.search(index=self.index, size=500, filter_path="hits.hits._source", body={
        "query": {
                "bool": {
                    "must": {"range": self.daterangequery},
                    "filter": {
                        "term": {
                            "object.id.keyword": id
                        }
                    }
                }
        },
        "sort": {"timestamp": "desc"}})
        obj = obj["hits"]["hits"]
        return obj

    def get_object_definition(self, id):
        objs = self.get_object_occurences(id, 1)
        obj = objs[0]["_source"]
        print(obj)
        defn = obj["object"]
        defn["system"] = obj["system"]["id"]
        return defn

    def get_activity(self, field, id):
        activity = self.es.search(index=self.index, size=25, filter_path="aggregations.activity.buckets", body={
            "sort": {"timestamp": "desc"},
            "aggs": {
                "activity": {
                    "date_histogram": {
                        "field": "timestamp",
                        "interval": "3h",
                        "time_zone": "+02:00"
                    }
                }
            },
            "query": {
                "bool": {
                    "must": {"range": self.daterangequery},
                    "filter": {
                        "term": {
                            field: id
                        }
                    }
                }
            }
        })
        activity_buckets = activity["aggregations"]["activity"]["buckets"]
        for i, bucket in enumerate(activity_buckets):
            key = activity_buckets[i]["key"]
            activity_buckets[i]["active"] = True if key >= self.traces_range_start and key < self.traces_range_end else False

        return activity_buckets

    def get_tree_activity(self, field, id):
        obj = self.get_object_definition(id)
        activity_buckets = []
        if obj["type"] == "system":
            activity_buckets = self.get_activity("system.id.keyword", id)
        elif obj["type"] == "course":
            activity_buckets = self.get_activity("course.id.keyword", id)
        else:
            activity_buckets = self.get_activity("object.id.keyword", id)

        return activity_buckets


    def get_traces(self, user):
        traces = self.es.search(index=self.index, size=100, filter_path="hits.hits", body={
            "sort": {"timestamp": "desc"},
            "script_fields": {
              "timestamp": {
                "script": "doc[\"timestamp\"].value.toInstant().toEpochMilli();"
              }
            },
            "_source": True,
            "query": {
                "bool": {
                    "must": {"range": self.daterangequery_traces},
                    "filter": {
                        "term": {
                            "actor.account.login.keyword": user
                        }
                    }
                },
            }})
        return self.convert_traces(traces["hits"]["hits"])

    def get_ways(self, id):
        # get all occurences
        occurences = self.get_object_occurences(id)
        ways = {
            "max_count": 0,
            "previous": {"items": {}, "count": 0, "total_distance": 0},
            "next": {"items": {}, "count": 0, "total_distance": 0}
        }
        for oc in occurences:
            oc = oc["_source"]

            def add_way(key):
                if key in oc:
                    way_container = ways[key]
                    adjacent = oc[key]
                    adj_id = adjacent["object"]["id"]
                    if adjacent["distance"] <3600 and adj_id != id:
                        if not adj_id in  way_container["items"]:
                            way_container["items"][adj_id] = {
                                "name": adjacent["object"]["definition"]["name"]["any"],
                                "system": adjacent["system_id"],
                                "total_distance": adjacent["distance"],
                                "count": 1,
                            }
                        else:
                            way_container["items"][adj_id]["count"] += 1
                            way_container["items"][adj_id]["total_distance"] += adjacent["distance"]
                        if way_container["items"][adj_id]["count"] > ways["max_count"]:
                            ways["max_count"] = way_container["items"][adj_id]["count"]
                        way_container["count"] += 1
                        way_container["total_distance"] += adjacent["distance"]

            add_way("previous")
            add_way("next")

        ways["total_distance"] = ways["previous"]["total_distance"] + ways["next"]["total_distance"]
        ways["count"] = ways["previous"]["count"] + ways["next"]["count"]
        ways["keys"] = len(ways["previous"]["items"].keys()) + len(ways["next"]["items"].keys())
        ways["average_distance"] = ways["total_distance"] / ways["count"] if ways["count"] else 0
        ways["average_count"] = ways["count"] / ways["keys"] if ways["count"] else 0

        def calc_quotas(key):
            way_container = ways[key]
            if len(way_container["items"]) != 0:
                for item_key in way_container["items"]:
                    item = way_container["items"][item_key]
                    item["quota"] = item["count"] / way_container["count"]
                    item["distance"] = item["total_distance"] / item["count"]
                    item["relative_distance"] = item["distance"] / ways["average_distance"]
                    item["relative_count"] = item["count"] / ways["max_count"]


        calc_quotas("previous")
        calc_quotas("next")

        return ways

