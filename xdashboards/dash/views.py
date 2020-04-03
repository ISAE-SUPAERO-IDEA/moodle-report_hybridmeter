from django.shortcuts import render
from django.http import JsonResponse, HttpResponse


from .helpers import AdnHelper, LmsHelper, ZoomHelper
from datetime import datetime
import csv
import pytz

def timezone_convert(input_dt, current_tz='UTC', target_tz='Europe/Paris'):
    current_tz = pytz.timezone(current_tz)
    target_tz = pytz.timezone(target_tz)
    target_dt = current_tz.localize(input_dt).astimezone(target_tz)
    return target_tz.normalize(target_dt)


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
        "uniques_buckets": dashboard["uniques_buckets"],
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
    zoom_helper = ZoomHelper(request)
    if helper.error_response:
        return helper.error_response

    course_id = request.GET.get('id')

    dashboard_zoom = zoom_helper.dashboard()
    dashboard = helper.dashboard()

    if course_id:
        dashboard_pcp = helper.dashboard(course_id)

    response = HttpResponse(content_type='text/csv')
    response['Content-Disposition'] = 'attachment; filename="lms_statistics.csv"'
    response['charset'] = 'utf-8'
    writer = csv.writer(response, delimiter=';')

    def timestamp_as_date(timestamp):
        date = datetime.fromtimestamp(timestamp/1000)
        return timezone_convert(date)

    def timestamp_as_string(timestamp):
        date = timestamp_as_date(timestamp)
        return date.strftime("%d/%m/%Y")

    dates = [timestamp_as_date(bucket["key"]) for bucket in dashboard["uniques_buckets"]]
    dates_string = [timestamp_as_string(bucket["key"]) for bucket in dashboard["uniques_buckets"]]

    def writerow(writer, title, buckets):
        values = ["0" for a in range(len(dates))]
        for bucket in buckets:
            try:
                index = dates_string.index(timestamp_as_string(bucket["key"]))
                values[index] = bucket["doc_count"]
            except:
                pass
        writer.writerow([title] + values)


    writer.writerow(["Jour"] + dates_string)
    writer.writerow(["LMS"])
    writerow(writer, " - Nombre d'acces uniques", dashboard["uniques_buckets"])
    writerow(writer, " - Nombre d'acces", dashboard["hits_buckets"])
    if course_id:
        writer.writerow([dashboard_pcp["title"]])
        writerow(writer, " - Nombre d'acces uniques", dashboard_pcp["uniques_buckets"])
        writerow(writer, " - Nombre d'acces", dashboard_pcp["hits_buckets"])
    writer.writerow([dashboard_zoom["title"]])
    writerow(writer, " - Nombre de réunions", dashboard_zoom["activity_buckets"])
    
    return response