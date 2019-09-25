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

convert_paths= {
    "_id": {
        "field": "id"
    },
    "_source.actor.account.name": {
        "field": "actor"
    },
    "_source.verb.id": {
        "field": "verb",
        "translations": {
            "https://w3id.org/xapi/adl/verbs/logged-in": "s'est connecté(e)",
            "http://vocab.xapi.fr/verbs/navigated-in": "a navigué vers",
            "http://adlnet.gov/expapi/verbs/answered": "a répondu à",
            "http://vocab.xapi.fr/verbs/graded": "a obtenu",
        }
    },
    "_source.system.definition.name.en": {
        "field": "system"
    },
    "fields.timestamp": {
        "field": "timestamp",
        "transform": "date"
    },
    "_source.object.definition.name.en-US": {
        "field": "object"
    },
    "_source.object.definition.name.en": {
        "field": "object"
    },
    "_source.result.score.raw": {
        "field": "score"
    },
    "_source.result.score.max": {
        "field": "score_max"
    }
}
verbs_to_phrases = {
    "https://w3id.org/xapi/adl/verbs/logged-in": "{{ t.actor }} {{ t.verb }}",
    "http://vocab.xapi.fr/verbs/navigated-in": "{{ t.actor }} {{ t.verb }} {{ t.object }}",
    "http://adlnet.gov/expapi/verbs/answered": "{{ t.actor }} {{ t.verb }} {{ t.object }} ({{t.score}}/{{t.score_max}})",
    "http://vocab.xapi.fr/verbs/graded": "{{ t.actor }} {{ t.verb }}  {{t.score}}/{{t.score_max}} sur {{ t.object }}"
}

# Create your views here.
def convert_trace(trace):
    res = {}
    for key in convert_paths.keys():
        convert_path = convert_paths[key]
        paths = key.split(".")
        val = trace
        i = 0
        while val and i<len(paths):
            val = val.get(paths[i])
            i = i + 1

        if "translations" in convert_path and val in convert_path["translations"]:
            val = convert_path["translations"][val]

        if "transform" in convert_path and convert_path["transform"] =="date" and val:
            dt_object = dt.datetime.fromtimestamp(val[0]/1000)
            val = dt_object.strftime("le %d/%m/%Y à %H:%M:%S")
        field = convert_path["field"]
        if val or not field in res:
            res[field] = val

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
                            "lt": "now/d"
                        }
                    }
    actors = es.search(index=index, size=0, filter_path="aggregations.actor.buckets", body={
        "query": { "range": daterangequery },
        "aggs": {
            "actor": {
                "terms": {"field": "actor.account.login.keyword"},
                "aggs": {
                    "name": {"terms": {"field": "actor.account.name.keyword"}}
                }
            }
        }
    })
    actors = actors["aggregations"]["actor"]["buckets"]

    user = request.GET.get('user')
    if user:
        
        traces = es.search(index=index, size=25, filter_path="hits.hits", body={
            "sort": {"timestamp": "desc"},
            "script_fields": {
              "timestamp": {
                "script": "doc[\"timestamp\"].value.toInstant().toEpochMilli();"
              }
            },
            "_source": True,
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

    activity_data = json.dumps(activity["aggregations"]["activity"]["buckets"])
    traces = convert_traces(traces["hits"]["hits"])
    return render(request, 'dash/dashboard.html', {'actors': actors, "activity_data": activity_data, "traces": traces })