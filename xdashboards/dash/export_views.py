from .helpers import AdnHelper, LmsHelper, UnitHelper, ZoomHelper
from django.http import HttpResponse
import csv
from datetime import datetime
import pytz
import json
import hashlib
from . import data

def timezone_convert(input_dt, current_tz='UTC', target_tz='Europe/Paris'):
    current_tz = pytz.timezone(current_tz)
    target_tz = pytz.timezone(target_tz)
    target_dt = current_tz.localize(input_dt).astimezone(target_tz)
    return target_tz.normalize(target_dt)


def api_lms_summary(request):
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

def prepare_response(name):
    response = HttpResponse(content_type='application/text')
    #response['Content-Disposition'] = f'attachment; filename="{name}"'
    response['charset'] = 'utf-8'
    return response

def write_traces(response, traces):
    for trace in traces:
        response.write(json.dumps(trace))
        response.write("\n")

def api_lms_traces(request):
    helper = LmsHelper(request)
    zoom_helper = ZoomHelper(request)
    if helper.error_response:
        return helper.error_response
    course_id = request.GET.get('id')
    traces = helper.get_traces(course_id=course_id)

    return write_traces(prepare_response("lms_traces.json"), traces)

def hash(name):
    return hashlib.md5(name.encode("utf-8")).hexdigest()

def api_lms_merged_traces(request):
    lms = LmsHelper(request)
    adn = AdnHelper(request)

    response = prepare_response("lms_merged_traces.json")

    """
    #traces = helper.get_source_traces(course_id=f"https://lms.isae.fr/xapi/activities/course/{data.merged_traces_lms_course_ids[0]}")
    traces = adn.get_source_traces(user_id=hash("e.poquillon"))
    write_traces(response, traces)
    return response
    """
    def process_login(login):

        def unpack_traces(traces):
            res = []
            for trace in traces:
                trace = trace["_source"]
                trace["actor"]["account"]["login"] = login
                def rmattribute(attrib):
                    if attrib in trace:
                        del trace[attrib]
                #rmattribute("next")
                #rmattribute("previous")
                rmattribute("system")
                rmattribute("hash")
                rmattribute("context")
                rmattribute("version")
                rmattribute("authority")
                #rmattribute("activity")

                res.append(trace)
            return res
        user_id = hash(login)
        
        # LMS traces
        for course_id in data.merged_traces_lms_course_ids:
            traces = lms.get_raw_traces(course_id=f"https://lms.isae.fr/xapi/activities/course/{course_id}", user_id=user_id, size=10000)
            traces = unpack_traces(traces)
            write_traces(response, traces)
            
        # ADN traces
        for course_id in data.merged_traces_adn_course_ids:
            traces = adn.get_raw_traces(course_id=f"https://adn.isae-supaero.fr/xapi/activities/course/{course_id}", user_id=user_id)
            traces = unpack_traces(traces)
            write_traces(response, traces)

    for login in data.merged_traces_logins:
        process_login(login)

    for member in data.merged_traces_group_cnamisae["members"]:
        process_login(member["login"])
        
    return response

