# /home/innovaol/AppVuelos/main/views/settings_views.py

from django.shortcuts import render, redirect
from django.contrib.auth.decorators import login_required
from django.contrib import messages
from django.contrib.auth.models import Group, User, Permission
from django.contrib.contenttypes.models import ContentType

@login_required
def manage_settings(request):
    """
    Vista principal de Ajustes.
    Muestra un menú con las opciones de configuración.
    """
    return render(request, "manage_settings.html")

@login_required
def manage_access_levels(request):
    """
    Vista para administrar los niveles de acceso (permisos).
    """
    # Obtener permisos globales
    all_permissions = Permission.objects.all().order_by('codename')
    groups = Group.objects.all().order_by('name')
    users = User.objects.all().order_by('username')
    
    context = {
        'groups': groups,
        'users': users,
        'grouped_permissions': all_permissions,
    }
    return render(request, 'manage_access_levels.html', context)
