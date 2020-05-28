from django.template import engines, TemplateSyntaxError
from django.shortcuts import render, redirect
import pytz
import json
import datetime as dt
from django.conf import settings
from elasticsearch import Elasticsearch
import os
import io
import math
import sys

curdir = os.path.dirname(os.path.abspath(__file__))

clear_file = io.open(curdir + "/anonymize/clear.txt", "r", encoding="utf-8")
hashed_file = io.open(curdir + "/anonymize/hashed.txt", "r", encoding="utf-8")
ANONYMOUS_DB = {}
ANONYMOUS_REVERSE_DB = {}
for line in clear_file:
    hashed = hashed_file.readline().rstrip('\n')
    clear = line.rstrip('\n')
    ANONYMOUS_DB[clear] = hashed
    ANONYMOUS_REVERSE_DB[hashed] = clear
    #ANONYMOUS_DB[clear] = clear
    #ANONYMOUS_REVERSE_DB[clear] = clear

clear_file.close()
hashed_file.close()

def anonymize(key, force=True):
    if key in ANONYMOUS_DB:
        key = ANONYMOUS_DB.get(key)
    elif force:
        key = "?"
    return key

convert_paths = {
    "id": {
        "field": "_id"
    },
    "actor": {
        "field": "_source.actor.account.uuid"
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
    "verb_id": {
        "field": "_source.verb.id"
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
    "object_type_link": {
        "field": "_source.object.definition.type"
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


def anonymize_trace(trace):
    """
    def clear_path(path):
        item = trace
        paths = path.split(".")
        i = 0
        while i < len(paths) - 1
            item = item[paths[i]]
            i = i +1
        del item[paths[i]]
    clear_path("actor.account.name")
    """

    return anonymize_trace_be(trace)

# Create your views here.
def anonymize_trace_be(trace):
    if type(trace) == dict:

        for key in trace:
            trace[key] = anonymize_trace_be(trace[key])
        return trace
    elif type(trace) == list:
        return [anonymize_trace_be(e) for e in trace]
    elif type(trace) == str:
        return anonymize(trace, False)
    elif type(trace) == int or type(trace) == float or type(trace) == bool or trace is None:
        return trace
    else:
        raise(Exception("Unknown trace type"))


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
    def __init__(self, request, es, index, global_range_start, global_range_end, time_field="timestamp", authorized_users=None):
        self.request = request
        self.es = es
        self.index = index
        self.global_range_end = global_range_end
        self.global_range_start = global_range_start
        self.time_field = time_field

        self.daterangequery = {self.time_field: {
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
        self.daterangequery_traces = { self.time_field: {
                        "gte": self.traces_range_start,
                        "lt": self.traces_range_end
                    }
                }
        print(self.daterangequery_traces)
        self.error_response = None
        if not request.user.is_authenticated:
            self.error_response = redirect("cas_ng_login")

        elif authorized_users and request.user.username not in authorized_users:
            self.error_response = render(request, 'dash/error.html', {"error": "Not authorized: {}".format(request.user.username)})

    def convert_traces(self, traces):
        traces = [anonymize_trace(trace) for trace in traces]
        traces = [convert_trace(trace) for trace in traces]
        return traces

    def aggregate(self, id_field, description_field, range="full", filter=None, size=5000, anonymize=True):
        query = {"range": self.daterangequery} if range == "full" else {"range": self.daterangequery_traces}
        if filter:
            query = {
                "bool": {
                    "must": query,
                    "filter": filter
                    }
                }
        #query = {'range': {'timestamp': {'gte': 1571927112456.732, 'lte': 1578407112456.732}}}
        choices = self.es.search(index=self.index, size=0, filter_path="aggregations.agg.buckets", body={
            "query": query,
            "aggs": {
                "agg": {
                    "terms": {
                        "field": id_field,
                        "size": size
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
            #print("{} / {}".format(choice["name"], self.anonymize(choice["key"])))
            if anonymize:
                choice["name"] = self.anonymize(choice["key"])
                choice["key"] = self.anonymize(choice["key"])

        return choices

    def anonymize(self, key):
        return anonymize(key, True)

    def unanonymize(self, key):
        return ANONYMOUS_REVERSE_DB.get(key)

    def get_object_occurences(self, id, size = 1000000):
        obj = self.es.search(index=self.index, size=500, body={
            "query": {
                "bool": {
                    "must": {"range": self.daterangequery_traces},
                    "filter": {
                        "term": {
                            "object.id.keyword": id
                        }
                    }
                }
            },
            "sort": {self.time_field: "desc"}})
        obj = obj["hits"]["hits"]
        return obj

    def get_object_definition(self, id):
        objs = self.get_object_occurences(id, 1)
        if objs:
            obj = objs[0]["_source"]
            defn = obj["object"]
            if "system" in obj:
                defn["system"] = obj["system"]["id"]
            return defn

    def get_activity(self, field, id, intervalParent="week", intervalChild="3h", adn=False):

        adn_query = ".*"
        if adn :
            adn_query = "hvp.*"


        activity = self.es.search(index=self.index, size=25, filter_path="aggregations.activity_parent.buckets", body={
            "sort": {self.time_field: "desc"},
            "aggs": {
                "activity_parent": {
                    "date_histogram": {
                        "field": self.time_field,
                        "interval": intervalParent,
                        "time_zone": "Europe/Paris"
                    },
                    "aggs": {
                        "activity_child": {
                            "date_histogram": {
                                "field": self.time_field,
                                "interval": intervalChild,
                                "time_zone": "Europe/Paris"
                            }
                        }
                    }
                }
            },
            "query": {
                "bool": {
                    "must": [
                        {
                            "range": self.daterangequery
                        },
                        {
                            "regexp": {
                                "object.definition.extensions.http://vocab.xapi.fr/extensions/platform-concept.keyword": {
                                    "value": adn_query
                                }
                            }
                        }
                    ],
                    "must_not": {"term": {"verb.id.keyword": "http://id.tincanapi.com/verb/defined"}},
                    "filter": {
                        "term": {
                            field: id
                        }
                    }
                }
            }
        })
        print
        activity_buckets = activity["aggregations"]["activity_parent"]["buckets"]
        for i, bucket in enumerate(activity_buckets):
            key = activity_buckets[i]["key"]
            activity_buckets[i]["active"] = True if key >= self.traces_range_start and key < self.traces_range_end else False

            activity_children = activity_buckets[i]["activity_child"]["buckets"]
            for j, bucket_child in enumerate(activity_children):
                key = activity_children[j]["key"]
                activity_children[j]["active"] = True if key >= self.traces_range_start and key < self.traces_range_end else False

            activity_buckets[i]["activity_children"] = activity_children

        return activity_buckets

    def get_uniques(self, field, id, intervalParent="week", intervalChild="3h"):
        activity = self.es.search(index=self.index, size=25, filter_path="aggregations.activity_parent.buckets", body={
            "sort": {self.time_field: "desc"},
            "aggs": {
                "activity_parent": {
                    "date_histogram": {
                        "field": self.time_field,
                        "interval": intervalParent,
                        "time_zone": "Europe/Paris"
                    },
                    "aggs": {
                        "activity_child": {
                            "date_histogram": {
                                "field": self.time_field,
                                "interval": intervalChild,
                                "time_zone": "Europe/Paris"
                            },
                            "aggs": {
                                "actor": {
                                    "cardinality": {
                                        "field": "actor.account.name.keyword"
                                    }
                                }
                            }
                        }
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
        activity_buckets = activity["aggregations"]["activity_parent"]["buckets"]
        for i, bucket in enumerate(activity_buckets):
            key = activity_buckets[i]["key"]
            activity_buckets[i]["active"] = True if key >= self.traces_range_start and key < self.traces_range_end else False

            activity_children = activity_buckets[i]["activity_child"]["buckets"]
            for j, bucket in enumerate(activity_children):
                activity_children[j]["active"] = True if key >= self.traces_range_start and key < self.traces_range_end else False
                activity_children[j]["doc_count"] = activity_children[j]["actor"]["value"]

            activity_buckets[i]["activity_children"] = activity_children

        return activity_buckets

    def get_tree_activity(self, field, id):
        obj = self.get_object_definition(id)
        activity_buckets = []
        if obj:
            return
        if obj["type"] == "system":
            activity_buckets = self.get_activity("system.id.keyword", id)
        elif obj["type"] == "course":
            activity_buckets = self.get_activity("course.id.keyword", id)
        else:
            activity_buckets = self.get_activity("object.id.keyword", id)

        return activity_buckets

    def get_traces(self, user):
        traces = self.es.search(index=self.index, size=100, filter_path="hits.hits", body={
            "sort": {self.time_field: "desc"},
            "script_fields": {
              "timestamp": {
                "script": "doc[\"timestamp\"].value.toInstant().toEpochMilli();"
              }
            },
            "_source": {
                "excludes": ["actor.name", "actor.account.name"]
            },
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

    def add_way(self, source, adjency, ways):
        nodes = ways["nodes"]
        edges = ways["edges"]
        edge_groups = ways["edge_groups"]
        #print(json.dumps(source, sort_keys=True, indent=4, separators=(',', ': ')))
        if adjency in source:
            adjacent = source[adjency]
            node_id = adjacent["object"]["id"]
            source_node_id = source["object"]["id"]
            if adjency == "previous":
                node_from = node_id
                node_to = source_node_id
            else:
                node_from = source_node_id
                node_to = node_id
            edge_id = "{}|{}".format(node_from, node_to)

            if adjacent["distance"] < 3600 and node_id != source_node_id and node_id != self.request.GET.get('id'):
                if not node_id in nodes:
                    nodes[node_id] = {
                        "name": adjacent["object"]["definition"]["name"]["any"],
                        "system": adjacent["system_id"],
                        "type_link": adjacent["object"]["definition"]["type"],
                        "type": adjacent["object"]["type"] if "type" in adjacent["object"] else "",
                    }
                if not edge_id in edges:
                    edges[edge_id] = {
                        "hits": 0,
                        "total_distance": 0,
                        "average_distance": 0,
                        "adjency": adjency,
                        "node_from": node_from,
                        "node_to": node_to,
                    }
                edges[edge_id]["total_distance"] += adjacent["distance"]
                edges[edge_id]["hits"] += 1

                def add_to_edge_group(adjency, name, edge_id):
                    key = "{}|{}".format(adjency, name)
                    if not key in edge_groups:
                        edge_groups[key] = { "edges": {} }
                    if not edge_id in edge_groups[key]["edges"]:
                        edge_groups[key]["edges"][edge_id] = edges[edge_id]

                if adjency == "next":
                    add_to_edge_group("from", node_from, edge_id)
                else:
                    add_to_edge_group("to", node_to, edge_id)

    def add_node_ways(self, ways, id, previous, next, recursion, cull):
        occurences = self.get_object_occurences(id)
        for oc in occurences:
            oc = oc["_source"]
            if previous:
                self.add_way(oc, "previous", ways)
            if next:
                self.add_way(oc, "next", ways)

        for edge_group_key in ways["edge_groups"]:
            edge_group = ways["edge_groups"][edge_group_key]
            if not "done" in edge_group:
                edge_group["nb_edges"] = len(list(edge_group.keys()))
                edge_group["total_hits"] = sum(map(lambda e: e["hits"], edge_group["edges"].values()))
                edge_group["total_distance"] = sum(map(lambda e: e["total_distance"], edge_group["edges"].values()))
                edge_group["average_distance"] = edge_group["total_distance"] / edge_group["total_hits"]
                for key in edge_group["edges"]:
                    edge = edge_group["edges"][key]
                    edge["relative_hits"] = edge["hits"] / edge_group["total_hits"]
                    edge["average_distance"] = edge["total_distance"] / edge["hits"]
                    if edge["relative_hits"] < cull:
                        edge["cull"] = True
                edge_group["done"] = True

        # delete culled nodes
        edge_ids = list(ways["edges"].keys())
        for edge_id in edge_ids:
            edge = ways["edges"][edge_id]
            if "cull" in edge:
                del ways["edges"][edge_id]

        def node_has_edge(node_id, type):
            found = False
            for edge_id in ways["edges"]:
                edge = ways["edges"][edge_id]
                if not "cull" in edge and (edge["node_" + type] == node_id):
                    found = True
                    break
            return found

        # delete culled nodes
        node_ids = list(ways["nodes"].keys())
        for node_id in node_ids:
            if not (node_has_edge(node_id, "from") or node_has_edge(node_id, "to")):
                del ways["nodes"][node_id]

        if recursion:
            node_ids = list(ways["nodes"].keys())
            for node_id in node_ids:
                if not ways["nodes"][node_id]["type_link"] == "http://vocab.xapi.fr/activities/system":
                    self.add_node_ways(
                        ways,
                        node_id,
                        previous=node_has_edge(node_id, "from"),
                        next=node_has_edge(node_id, "to"),
                        recursion=recursion-1,
                        cull=0.15)

    def get_ways(self, id, previous=True, next=True, recursion=1, cull=0.1):
        # get all occurences
        #occurences = self.get_object_occurences(id)
        print(id)
        ways = {
            "nodes": {},
            "edges": {},
            "edge_groups": {}
        }
        self.add_node_ways(ways, id, previous=previous, next=next, recursion=recursion, cull=cull)
        return ways


class UnitHelper(Helper):
    def __init__(self, request):
        es = Elasticsearch(["idea-db.isae.fr"])
        index = "xapi_adn_enriched"
        global_range_end = 1572566400 * 1000 # 1er novembre 2019
        #global_range_end = math.floor(dt.datetime.now().timestamp() * 1000)
        global_range_start = global_range_end - 60 * 24 * 60 * 60 * 1000
        super(UnitHelper, self).__init__(request, es, index, global_range_start, global_range_end, authorized_users=settings.AUTHORIZED_USERS)


class AdnHelper(Helper):
    def __init__(self, request):
        es = Elasticsearch(["idea-db.isae.fr"])
        index = "xapi_adn_enriched"
        # global_range_end = 1572566400 * 1000 # 1er novembre 2019
        global_range_end = math.floor(dt.datetime.now().timestamp() * 1000)
        global_range_start = global_range_end - 60 * 24 * 60 * 60 * 1000
        super(AdnHelper, self).__init__(request, es, index, global_range_start, global_range_end)

    def dashboard(self, course_id=None):
        title = "ADN ISAE-SUPAERO"
        if course_id:
            filter_field = "object.id.keyword"
            filter_id = course_id
            object_ = self.get_object_definition(course_id)
            title = object_["definition"]["name"]["any"]
        else:
            filter_field = "context.platform.keyword"
            filter_id = "Moodle"

        activity_buckets = {
            "day": self.get_activity(filter_field, filter_id),
            "week": self.get_activity(filter_field, filter_id, intervalParent="month")
        }
        hits_buckets = {
            "day": self.get_activity(filter_field, filter_id, intervalChild="1d"),
            "week": self.get_activity(filter_field, filter_id, intervalParent="month", intervalChild="week")
        }
        hits_buckets_hvp = {
            "day": self.get_activity(filter_field, filter_id, intervalChild="1d", adn=True),
            "week": self.get_activity(filter_field, filter_id, intervalParent="month", intervalChild="week", adn=True)
        }
        uniques_buckets = {
            "day": self.get_uniques(filter_field, filter_id, intervalChild="1d"),
            "week": self.get_uniques(filter_field, filter_id, intervalParent="month", intervalChild="week")
        }
        return {
            "title": title,
            "activity_buckets": {
                "day": activity_buckets["day"],
                "week": activity_buckets["week"]
            },
            "hits_buckets": {
                "day": hits_buckets["day"],
                "week": hits_buckets["week"]
            },
            "uniques_buckets": {
                "day": uniques_buckets["day"],
                "week": uniques_buckets["week"]
            },
            "hits_buckets_hvp": {
                "day": hits_buckets_hvp["day"],
                "week": hits_buckets_hvp["week"]
            },
        }

class LmsHelper(Helper):
    def __init__(self, request):
        es = Elasticsearch(["idea-db.isae.fr"])
        index = "xapi_enriched"
        global_range_end = math.floor(dt.datetime.now().timestamp() * 1000)
        global_range_start = global_range_end - 60 * 24 * 60 * 60 * 1000
        super(LmsHelper, self).__init__(request, es, index, global_range_start, global_range_end)

    def dashboard(self, course_id=None):
        title = "LMS ISAE-SUPAERO"
        if course_id:
            filter_field = "object.id.keyword"
            filter_id = course_id
            object_ = self.get_object_definition(course_id)
            title = object_["definition"]["name"]["any"]
        else:
            filter_field = "context.platform.keyword"
            filter_id = "Moodle"

        activity_buckets = {
            "day": self.get_activity(filter_field, filter_id),
            "week": self.get_activity(filter_field, filter_id, intervalParent="month")
        }
        hits_buckets = {
            "day": self.get_activity(filter_field, filter_id, intervalChild="1d"),
            "week": self.get_activity(filter_field, filter_id, intervalParent="month", intervalChild="week")
        }
        hits_buckets_hvp = {
            "day": self.get_activity(filter_field, filter_id, intervalChild="1d", adn=True),
            "week": self.get_activity(filter_field, filter_id, intervalParent="month", intervalChild="week", adn=True)
        }
        uniques_buckets = {
            "day": self.get_uniques(filter_field, filter_id, intervalChild="1d"),
            "week": self.get_uniques(filter_field, filter_id, intervalParent="month", intervalChild="week")
        }
        return {
            "title": title,
            "activity_buckets": {
                "day": activity_buckets["day"],
                "week": activity_buckets["week"]
            },
            "hits_buckets": {
                "day": hits_buckets["day"],
                "week": hits_buckets["week"]
            },
            "uniques_buckets": {
                "day": uniques_buckets["day"],
                "week": uniques_buckets["week"]
            },
            "hits_buckets_hvp": {
                "day": hits_buckets_hvp["day"],
                "week": hits_buckets_hvp["week"]
            },
        }

class ZoomHelper(Helper):
    def __init__(self, request):
        es = Elasticsearch(["idea-db.isae.fr"])
        index = "zoom_meetings"
        global_range_end = math.floor(dt.datetime.now().timestamp() * 1000)
        global_range_start = global_range_end - 365 * 24 * 60 * 60 * 1000
        global_range_end += 365 * 24 * 60 * 60 * 1000
        super(ZoomHelper, self).__init__(request, es, index, global_range_start, global_range_end, time_field="start_time")

    def dashboard(self, course_id=None):
        title = "ZOOM"
        interval = "1d"
        activity = self.es.search(index=self.index, size=365 * 2, filter_path="aggregations.activity.buckets", body={
            "sort": {"start_time": "desc"},
            "aggs": {
                "activity": {
                    "date_histogram": {
                        "field": self.time_field,
                        "interval": interval,
                        "time_zone": "Europe/Paris"
                    }
                }
            },
            "query": {
                "bool": {
                    "must": {"range": self.daterangequery},
                }
            }
        })
        activity_buckets = activity["aggregations"]["activity"]["buckets"]

        return {
            "title": title,
            "activity_buckets": activity_buckets,
        }