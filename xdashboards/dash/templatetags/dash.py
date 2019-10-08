from django.core.serializers import serialize
from django.db.models.query import QuerySet
from django.template import Library
import json
from django.utils.safestring import mark_safe

register = Library()

@register.filter(is_safe=True)
def jsonify(object):
    if isinstance(object, QuerySet):
        res = serialize('json', object)
    res =json.dumps(object)
    return mark_safe(res)

