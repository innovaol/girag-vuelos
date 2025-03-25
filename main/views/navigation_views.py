# /home/innovaol/AppVuelos/main/views/navigation_views.py
import os
from django.shortcuts import render, redirect
from django.contrib.auth.decorators import login_required
from django.contrib import messages
from django.urls import get_resolver
from main.models.navigation_section import NavigationSection

@login_required
def manage_navigation_sections(request):
    if not request.user.is_superuser:
        messages.error(request, "Acceso denegado.")
        return redirect('dashboard')

    # Botón para sincronizar secciones
    if request.method == 'POST' and 'sync' in request.POST:
        resolver = get_resolver()
        url_patterns = resolver.url_patterns
        new_count = 0

        for pattern in url_patterns:
            if hasattr(pattern, 'name') and pattern.name:
                obj, created = NavigationSection.objects.get_or_create(
                    url_name=pattern.name,
                    defaults={'display_name': pattern.name.replace('_', ' ').capitalize(), 'is_active': False}
                )
                if created:
                    new_count += 1

        messages.success(request, f"Se sincronizaron {new_count} nuevas secciones.")
        return redirect('manage_navigation_sections')

    # Guardar cambios de secciones activas
    if request.method == 'POST' and 'save' in request.POST:
        active_ids = request.POST.getlist('active')
        sections = NavigationSection.objects.all()
        for section in sections:
            section.is_active = str(section.id) in active_ids
            section.save()
        messages.success(request, "Se actualizaron las secciones.")
        return redirect('manage_navigation_sections')

    # Traer secciones existentes para renderizar en la tabla
    sections = NavigationSection.objects.all().order_by('display_name')
    context = {'sections': sections}
    return render(request, "manage_navigation_sections.html", context)
