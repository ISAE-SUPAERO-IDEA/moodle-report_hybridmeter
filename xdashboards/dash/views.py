from django.shortcuts import render
from django.http import JsonResponse, HttpResponse


from .helpers import AdnHelper, LmsHelper, UnitHelper, ZoomHelper
from datetime import datetime
import csv
import pytz

def timezone_convert(input_dt, current_tz='UTC', target_tz='Europe/Paris'):
    current_tz = pytz.timezone(current_tz)
    target_tz = pytz.timezone(target_tz)
    target_dt = current_tz.localize(input_dt).astimezone(target_tz)
    return target_tz.normalize(target_dt)


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



def api_lms_summary(request,):
    helper = LmsHelper(request)
    zoom_helper = ZoomHelper(request)
    if helper.error_response:
        return helper.error_response

    course_id = request.GET.get('id')
    aggs = request.GET.get('aggs')

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

    dates = []
    dates_string = [] 
    for buckets in dashboard["uniques_buckets"][aggs]:
        for child in buckets["activity_children"]:
            dates.append(timestamp_as_date(child["key"]))
            dates_string.append(timestamp_as_string(child["key"]))


    def writerow(writer, title, buckets, zoom=False):
        values = ["0" for a in range(len(dates))]
        if(zoom):
            for bucket in buckets:
                try:
                    index = dates_string.index(timestamp_as_string(bucket["key"]))
                    values[index] = bucket["doc_count"]
                except:
                    pass
        else:
            for bucket in buckets:
                for child in bucket["activity_children"]:
                    try:
                        index = dates_string.index(timestamp_as_string(child["key"]))
                        values[index] = child["doc_count"]
                    except:
                        pass

        writer.writerow([title] + values)


    writer.writerow(["Jour"] + dates_string)
    writer.writerow(["LMS"])

    writerow(writer, " - Nombre d'acces uniques", dashboard["uniques_buckets"][aggs])
    writerow(writer, " - Nombre d'acces", dashboard["hits_buckets"][aggs])

    if course_id:
        writer.writerow([dashboard_pcp["title"]])
        writerow(writer, " - Nombre d'acces uniques", dashboard_pcp["uniques_buckets"][aggs])
    
        writerow(writer, " - Nombre d'acces", dashboard_pcp["hits_buckets"][aggs])

    writer.writerow([dashboard_zoom["title"]])
    writerow(writer, " - Nombre de réunions", dashboard_zoom["activity_buckets"], zoom=True)
    
    return response

# NINJA: A retirer dès que le port du naas est ouvert

def ninjaproxy(request, path):
    from urllib.parse import urlencode
    from urllib.request import urlopen, Request
    import requests
    host = "naas.isae.fr"
    protocol = "http"
    #server = "http://icampus.isae.fr"
    url = "%s://%s/%s" % (protocol, host, path)
    # add get parameters
    if request.GET:
        url += '?' + urlencode(request.GET)

    # add headers of the incoming request
    # see https://docs.djangoproject.com/en/1.7/ref/request-response/#django.http.HttpRequest.META for details about the request.META dict
    def convert(s):
        s = s.replace('HTTP_','',1)
        s = s.replace('_','-')
        return s


    request_headers = dict((convert(k),v) for k,v in request.META.items() if k.startswith('HTTP_'))
    # add content-type and and content-length
    content_type = request.META.get('CONTENT-TYPE')
    if content_type: request_headers['CONTENT-TYPE'] = content_type
    content_length = request.META.get('CONTENT_LENGTH')
    if content_length: request_headers['CONTENT-LENGTH'] = content_length
    request_headers['HOST'] = host

    # get original request payload
    if request.method == "GET":
        data = None
    else:
        data = request.raw_post_data

    downstream_request = Request(url, data)
    page = requests.get(url, data, headers=request_headers, allow_redirects=False, verify=False)
    response = HttpResponse(page)
    return response
    # NINJA
