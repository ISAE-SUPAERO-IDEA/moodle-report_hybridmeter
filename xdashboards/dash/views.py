from django.shortcuts import render
from django.http import JsonResponse, HttpResponse


from .helpers import AdnHelper, LmsHelper, UnitHelper, ZoomHelper

def learner(request):
    helper = UnitHelper(request)
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
        user_id = helper.unanonymize(params["id"])
        # traces ranges
        # activity data
        activity_buckets = helper.get_activity( filters={"term" : {"actor.account.login.keyword" : user_id} } )
        
        # traces
        traces = helper.get_traces(user_id=user_id)

    return render(request, 'dash/learners_view.html', {
        'choices': choices,
        "activity_buckets": activity_buckets,
        "traces": traces,
        "params": params
        })


def resource(request):
    helper = UnitHelper(request)
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
        activity_buckets = helper.get_activity( filters={"term" : {"object.id.keyword" : params["id"]} } )
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

def path(request):
    helper = LmsHelper(request)
    if helper.error_response:
        return helper.error_response

    activity_buckets = []
    user_choices = []
    traces = []

    # Courses list
    choices_courses = helper.aggregate(id_field="object.id.keyword", description_field="object.definition.name.fr.keyword", anonymize=False,size=50)
    for choice in choices_courses:
        prefix = ""
        choice["name"] = "{} - {} - {}".format(prefix, choice["type"], choice["name"] if choice["name"] else choice["key"])

    choices_courses.sort(key=lambda choice: choice["name"])

    params = {}

    params["course_id"] = request.GET.get('courseId')
    params["user_id"] = request.GET.get('userId')

    # L'utilisateur a choisi un cours
    if params["course_id"]:

        filter = { "term" : {"object.id.keyword" : params["course_id"]} }
        # actor list
        user_choices = helper.aggregate(id_field="actor.account.uuid.keyword", description_field="actor.account.uuid.keyword",filter= filter, anonymize=False)

    # L'utilisateur a choisi un cours et un élève
    if params["course_id"] and params["user_id"]:

        #activity_buckets = helper.get_tree_activity("object.id.keyword", id)
        filters = [ 
            {"term" : {"object.id.keyword" : params["course_id"]} },
            {"term" : {"actor.account.uuid.keyword" : params["user_id"]} }
         ]
        activity_buckets = helper.get_activity(filters=filters)
        # traces
        traces = helper.get_traces(user=params["user_id"], course=params["course_id"])

    return render(request, 'dash/path_view.html', {
        'choices_courses': choices_courses,
        'user_choices': user_choices,
        "activity_buckets": activity_buckets,
        "traces": traces,
        "title": "LMS ISAE-SUPAERO",
        "params": params
        })

def lms(request):
    helper = LmsHelper(request)
    if helper.error_response:
        return helper.error_response

    selected = None
    # object list
    choices = helper.aggregate(id_field="object.id.keyword", description_field="object.definition.name.fr.keyword", anonymize=False,size=50)
    for choice in choices:
        prefix = ""
        choice["name"] = "{} - {} - {}".format(prefix, choice["type"], choice["name"] if choice["name"] else choice["key"])

    choices.sort(key=lambda choice: choice["name"])

    dashboard = helper.dashboard(request.GET.get('id'))
    return render(request, 'dash/lms_view.html', {
        'choices': choices,
        "selected": selected,
        "activity_buckets": dashboard["activity_buckets"],
        "hits_buckets": dashboard["hits_buckets"],
        "hits_buckets_hvp": dashboard["hits_buckets_hvp"],
        "uniques_buckets": dashboard["uniques_buckets"],
        "title": dashboard["title"]
        })

def adn(request):
    helper = AdnHelper(request)
    if helper.error_response:
        return helper.error_response

    selected = None
    # object list
    choices = helper.aggregate(id_field="object.id.keyword", description_field="object.definition.name.fr.keyword", anonymize=False,size=50)
    for choice in choices:
        prefix = ""
        choice["name"] = "{} - {} - {}".format(prefix, choice["type"], choice["name"] if choice["name"] else choice["key"])

    choices.sort(key=lambda choice: choice["name"])

    dashboard = helper.dashboard(request.GET.get('id'))
    return render(request, 'dash/adn_view.html', {
        'choices': choices,
        "selected": selected,
        "activity_buckets": dashboard["activity_buckets"],
        "hits_buckets": dashboard["hits_buckets"],
        "hits_buckets_hvp": dashboard["hits_buckets_hvp"],
        "uniques_buckets": dashboard["uniques_buckets"],
        "title": dashboard["title"]
        })


def api_search_course(request, query=""):
    repository = request.GET.get('repository')
    if (repository=="adn"):
      helper = AdnHelper(request)
    if (repository=="lms"):
      helper = LmsHelper(request)

    if helper.error_response:
        return helper.error_response

    courses = helper.aggregate(
        id_field="object.id.keyword",
        description_field="object.definition.name.any.keyword",
        filter=[
            {"term": {"object.definition.type.keyword": "http://vocab.xapi.fr/activities/course"}},
            {"match_phrase_prefix": {"object.definition.name.any": query}}
        ],
        range="full",
        anonymize=False)

    return JsonResponse({ "data": courses})


