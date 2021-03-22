from .helpers import AdnHelper, LmsHelper, UnitHelper, ZoomHelper
from django.http import HttpResponse
import csv
from datetime import datetime
import pytz
import json

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

def api_lms_traces(request):
    helper = LmsHelper(request)
    zoom_helper = ZoomHelper(request)
    if helper.error_response:
        return helper.error_response
    course_id = request.GET.get('id')

    traces = helper.get_traces(course_id=course_id)
    response = HttpResponse(content_type='application/json')
    response['Content-Disposition'] = 'attachment; filename="lms_traces.json"'
    response['charset'] = 'utf-8'
    for trace in traces:
        response.write(json.dumps(trace))
        response.write("\n")

    
    return response