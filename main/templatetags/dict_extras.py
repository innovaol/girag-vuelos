# main/templatetags/dict_extras.py
from django import template

register = template.Library()

@register.filter
def get_item(dictionary, key):
    """Devuelve dictionary[key]"""
    return dictionary.get(key)
