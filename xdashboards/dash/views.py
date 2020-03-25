from django.shortcuts import render
from django.http import JsonResponse

from .helpers import AdnHelper, LmsHelper


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

    params["id"] = request.GET.get('id')
    title = "LMS ISAE-SUPAERO"
    if params["id"]:
        filter_field = "object.id.keyword"
        filter_id = params["id"]
        object_ = helper.get_object_definition(params["id"])
        title = object_["definition"]["name"]["fr"]
    else:
        filter_field = "context.platform.keyword"
        filter_id = "Moodle"
    activity_buckets = helper.get_activity(filter_field, filter_id)
    hits_buckets = helper.get_activity(filter_field, filter_id, interval="1d")
    uniques_buckets = helper.get_uniques_activity(filter_field, filter_id, interval="1d")


    return render(request, 'dash/lms_view.html', {
        'choices': choices,
        "selected": selected,
        "activity_buckets": activity_buckets,
        "hits_buckets": hits_buckets,
        "uniques_buckets": uniques_buckets,
        "learners": learners,
        "params": params,
        "title": title
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
