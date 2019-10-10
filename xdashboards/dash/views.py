from django.shortcuts import render
import json

from .helpers import Helper


def learner(request):
    helper = Helper(request)
    if helper.error_response:
        return helper.error_response

    activity_buckets = []
    traces = []
    # actor list
    choices = helper.aggregate(id_field="actor.account.login.keyword", description_field="actor.account.name.keyword")

    id = request.GET.get('id')
    if id:
        # traces ranges
        # activity data
        activity_buckets = helper.get_activity("actor.account.login.keyword", id)

        # traces
        traces = helper.get_traces(id)

    return render(request, 'dash/learners_view.html', {
        'choices': choices,
        "activity_buckets": activity_buckets,
        "traces": traces,
        })


def resource(request):
    helper = Helper(request)
    if helper.error_response:
        return helper.error_response

    activity_buckets = []
    learners = []
    ways =[]
    selected = None
    # object list
    choices = helper.aggregate(id_field="object.id.keyword", description_field="object.definition.name.any.keyword")
    for choice in choices:
        prefix = "Online" if choice["system"] == "https://online.isae-supaero.fr" else "ADN"
        choice["name"] = "{} - {} - {} - ({})".format(prefix, choice["type"], choice["name"], choice["key"])

    choices.sort(key=lambda choice: choice["name"])

    id = request.GET.get('id')
    if id:
        # traces ranges
        # activity data
        ways = helper.get_ways(id)
        #activity_buckets = helper.get_tree_activity("object.id.keyword", id)
        activity_buckets = helper.get_activity("object.id.keyword", id)
        selected = helper.get_object_definition(id)
        learners = helper.aggregate(
            id_field="actor.account.login.keyword",
            description_field="actor.account.name.keyword",
            filter={
                "term": {"object.id.keyword": id}
            },range="filtered")

        # traces
        #traces = helper.get_traces(id)

    return render(request, 'dash/resources_view.html', {
        'choices': choices,
        "selected": selected,
        "activity_buckets": activity_buckets,
        "ways": ways,
        "learners": learners,
        })
