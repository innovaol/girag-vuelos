from django.contrib import admin
from .models import Airline, Flight, Document

admin.site.register(Airline)
admin.site.register(Flight)
admin.site.register(Document)
