from django.shortcuts import render
from elasticsearch import Elasticsearch
import datetime as dt
import pytz
from django.template import engines, TemplateSyntaxError, Context
import json

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
        "field": "_source.object.definition.name.en|_source.object.definition.name.en-US"
    },
    "score": {
        "field": "_source.result.score.raw"
    },
    "score_max": {
        "field": "_source.result.score.max"
    },
    "source": {
        "field": "_source",
        "transform": "stringify"
    }

}
verbs_to_phrases = {
    "https://w3id.org/xapi/adl/verbs/logged-in": "{{ t.actor }} {{ t.verb }}",
    "https://w3id.org/xapi/adl/verbs/logged-out": "{{ t.actor }} {{ t.verb }}",
    "http://vocab.xapi.fr/verbs/navigated-in": "{{ t.actor }} {{ t.verb }} {{ t.object }}",
    "http://adlnet.gov/expapi/verbs/answered": "{{ t.actor }} {{ t.verb }} {{ t.object }} ({{t.score}}/{{t.score_max}})",
    "http://vocab.xapi.fr/verbs/graded": "{{ t.actor }} {{ t.verb }}  {{t.score}}/{{t.score_max}} sur {{ t.object }}"
}

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
                dt_object = dt.datetime.fromtimestamp(val[0]/1000)
                val = dt_object.strftime("%d/%m/%Y")
            if config["transform"] == "hour":
                dt_object = dt.datetime.fromtimestamp(val[0]/1000)
                val = dt_object.strftime("%H:%M:%S")
            if config["transform"] == "timestamp":
                val = val[0]
            if config["transform"] == "stringify":
                val = json.dumps(val, indent=4, sort_keys=True)

        res[key] = val
    verb = trace["_source"]["verb"]["id"]
    if verb in verbs_to_phrases:
        t_string = verbs_to_phrases[verb]
        template = template_from_string(t_string)
        context = { "t": res }
        res["phrase"] = template.render(context)
    else:
        res["phrase"] = res

    return res

def convert_traces(traces):
    return [convert_trace(trace) for trace in traces]

def index(request):
    es = Elasticsearch(["idea-db"])
    index = "xapi_adn_enriched"
    daterangequery = { "timestamp": {
                            "gte": "now-1M/d",
                            "lte": "now/d"
                        }
                    }
    # actor list
    actors = es.search(index=index, size=0, filter_path="aggregations.actor.buckets", body={
        "query": { "range": daterangequery },
        "aggs": {
            "actor": {
                "terms": {"field": "actor.account.login.keyword",
                "size": 50
                },
                "aggs": {
                    "name": {"terms": {"field": "actor.account.name.keyword"}}
                }
            }
        }
    })
    actors = actors["aggregations"]["actor"]["buckets"]

    user = request.GET.get('user')
    if user:
        # activity data
        activity = es.search(index=index, size=25, filter_path="aggregations.activity.buckets", body={
            "sort": {"timestamp": "desc"},
            "aggs": {
                "activity": {
                    "date_histogram": {
                        "field": "timestamp",
                        "interval": "3h"
                    }
                }
            },
            "query": {
                "bool": {
                    "must": {"range": daterangequery},
                    "filter": {
                        "term": {
                            "actor.account.login.keyword": user
                        }
                    }
                },
            }})

        # traces
        daterangequery_traces = daterangequery
        traces_range = request.GET.get('traces_range')
        if traces_range:
            traces_range = int(traces_range)
            daterangequery_traces = { "timestamp": {
                            "gte": traces_range,
                            "lt": traces_range + 3 * 60 * 60 * 1000
                        }
                    }

        traces = es.search(index=index, size=100, filter_path="hits.hits", body={
            "sort": {"timestamp": "desc"},
            "script_fields": {
              "timestamp": {
                "script": "doc[\"timestamp\"].value.toInstant().toEpochMilli();"
              }
            },
            "_source": True,
            "query": {
                "bool": {
                    "must": {"range": daterangequery_traces},
                    "filter": {
                        "term": {
                            "actor.account.login.keyword": user
                        }
                    }
                },
            }})

    activity_data_json = json.dumps(activity["aggregations"]["activity"]["buckets"])
    traces = convert_traces(traces["hits"]["hits"])
    return render(request, 'dash/dashboard.html', {
        'actors': actors,
        "activity_data_json": activity_data_json,
        "traces": traces,
        "traces_json": json.dumps(traces)
        })