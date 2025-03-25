# /home/innovaol/girapp/main/views/error_views.py

from django.shortcuts import render
import logging

logger = logging.getLogger(__name__)

def custom_page_not_found_view(request, exception=None):
    logger.debug(f"404 handler triggered: {request.path}")
    return render(request, "404_authenticated.html", status=404)
