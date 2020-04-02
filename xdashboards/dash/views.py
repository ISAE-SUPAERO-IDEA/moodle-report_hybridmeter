from django.shortcuts import render
from django.http import JsonResponse, HttpResponse


from .helpers import AdnHelper, LmsHelper
from datetime import datetime
import csv


def learner(request):
    helper = AdnHelper(request)
    if helper.error_response:
        return helper.error_response

    activity_buckets = []
    traces = []
    # actor list
    choices = helper.aggregate(id_field="actor.account.login.keyword", description_field="actor.account.name.keyword", anonymize=True)
    choices = filter(lambda x: x["name"] != "?", choices)
    params = {}

    params["id"] = request.GET.get('id')
    if params["id"]:
        id = helper.unanonymize(params["id"])
        # traces ranges
        # activity data
        activity_buckets = helper.get_activity("actor.account.login.keyword", id)

        # traces
        traces = helper.get_traces(id)

    return render(request, 'dash/learners_view.html', {
        'choices': choices,
        "activity_buckets": activity_buckets,
        "traces": traces,
        "params": params
        })


def resource(request):
    helper = AdnHelper(request)
    if helper.error_response:
        return helper.error_response

    activity_buckets = []
    learners = []
    ways =[]
    selected = None
    # object list
    choices = helper.aggregate(id_field="object.id.keyword", description_field="object.definition.name.any.keyword", anonymize=False)
    for choice in choices:
        prefix = "Online" if choice["system"] == "https://online.isae-supaero.fr" else "ADN"
        #choice["name"] = "{} - {} - {} - ({})".format(prefix, choice["type"], choice["name"], choice["key"])
        choice["name"] = "{} - {} - {}".format(prefix, choice["type"], choice["name"] if choice["name"] else choice["key"])

    choices.sort(key=lambda choice: choice["name"])

    params = {}

    params["id"] = request.GET.get('id')
    params["next_nodes"] = False if request.GET.get('next_nodes') == "false" else True
    params["previous_nodes"] = False if request.GET.get('previous_nodes') == "false" else True
    if params["id"]:
        # traces ranges
        # activity data
        ways = helper.get_ways(params["id"], previous=params["previous_nodes"], next=params["next_nodes"])
        #activity_buckets = helper.get_tree_activity("object.id.keyword", id)
        activity_buckets = helper.get_activity("object.id.keyword", params["id"])
        selected = helper.get_object_definition(params["id"])
        learners = helper.aggregate(
            id_field="actor.account.login.keyword",
            description_field="actor.account.name.keyword",
            filter={
                "term": {"object.id.keyword": params["id"]}
            },range="filtered")

        # traces
        #traces = helper.get_traces(id)

    return render(request, 'dash/resources_view.html', {
        'choices': choices,
        "selected": selected,
        "activity_buckets": activity_buckets,
        "ways": ways,
        "learners": learners,
        "params": params
        })

def lms(request):
    helper = LmsHelper(request)
    if helper.error_response:
        return helper.error_response

    activity_buckets = []
    learners = []
    selected = None
    # object list
    choices = helper.aggregate(id_field="object.id.keyword", description_field="object.definition.name.fr.keyword", anonymize=False,size=50)
    for choice in choices:
        prefix = ""
        choice["name"] = "{} - {} - {}".format(prefix, choice["type"], choice["name"] if choice["name"] else choice["key"])

    choices.sort(key=lambda choice: choice["name"])

    params = {}
    dashboard = helper.dashboard(request.GET.get('id'))
    return render(request, 'dash/lms_view.html', {
        'choices': choices,
        "selected": selected,
        "activity_buckets": activity_buckets,
        "hits_buckets": dashboard["hits_buckets"],
        "uniques_buckets": dashboard["uniques_buckets"],
        "params": params,
        "title": dashboard["title"]
        })

def api_search_course(request, query=""):
    helper = LmsHelper(request)
    if helper.error_response:
        return helper.error_response

    courses = helper.aggregate(
        id_field="object.id.keyword",
        description_field="object.definition.name.fr.keyword",
        filter=[
            {"term": {"object.definition.type.keyword": "http://vocab.xapi.fr/activities/course"}},
            {"match_phrase_prefix": {"object.definition.name.fr": query}}
        ],
        range="full",
        anonymize=False)

    return JsonResponse({ "data": courses})

def api_lms_summary(request):
    helper = LmsHelper(request)
    if helper.error_response:
        return helper.error_response

    dashboard = helper.dashboard(request.GET.get('id'))

    response = HttpResponse(content_type='text/csv')
    response['Content-Disposition'] = 'attachment; filename="lms_statistics.csv"'
    response['charset'] = 'utf-8'
    writer = csv.writer(response)

    def timestamp_ss_tring(timestamp):
        return datetime.fromtimestamp(timestamp/1000).strftime("%d/%m/%Y")

    dates = [timestamp_ss_tring(bucket["key"]) for bucket in dashboard["uniques_buckets"]]
    uniques = [bucket["doc_count"] for bucket in dashboard["uniques_buckets"]]
    hits = [bucket["doc_count"] for bucket in dashboard["hits_buckets"]]
    writer.writerow(["Jour"] + dates)
    writer.writerow(["Accès uniques"] + uniques)
    writer.writerow(["Nombre d'accès"] + hits)

    return response