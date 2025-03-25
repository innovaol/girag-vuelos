# /home/innovaol/AppVuelos/main/views/permissions_manage_views.py

from django.shortcuts import render, redirect
from django.contrib import messages
from django.core.management import call_command
from django.contrib.auth.decorators import login_required
from django.contrib.auth.models import Permission

@login_required
def manage_permissions(request):
    if request.method == "POST":
        if "sync_permissions" in request.POST:
            # Llamamos a nuestro nuevo comando para sincronizar permisos
            call_command("sync_permissions")
            messages.success(request, "Permisos sincronizados correctamente.")

        elif "delete_permissions" in request.POST:
            # Eliminamos todos los permisos globales
            try:
                Permission.objects.all().delete()
                messages.success(request, "Todos los permisos han sido eliminados correctamente.")
            except Exception as e:
                messages.error(request, f"Error al eliminar permisos: {str(e)}")

        return redirect("manage_permissions")

    # Listamos todos los permisos existentes
    grouped_permissions = Permission.objects.all()

    return render(request, "manage_permissions.html", {
        "grouped_permissions": grouped_permissions
    })
