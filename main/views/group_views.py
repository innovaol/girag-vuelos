from django.contrib.auth.decorators import permission_required
from django.shortcuts import render, redirect, get_object_or_404
from django.contrib.auth.models import Group, Permission
from django.contrib import messages
from main.forms.group_forms import GroupForm
from main.views.permissions_views import get_available_permissions  # Importamos la función centralizada
import json
import re
from django.http import JsonResponse
from main.utils.utils import check_group_name  # ✅ Importamos la validación de utils.py



@permission_required('auth.view_group', login_url='unauthorized')
def manage_groups(request):
    """
    Vista para listar grupos, usando 'auth.view_group'.
    """
    groups = Group.objects.all().order_by('name')
    return render(request, 'manage_groups.html', {'groups': groups})

@permission_required('auth.create_group', login_url='unauthorized')
def create_group(request):
    """
    Vista para crear un grupo, usando 'auth.create_group'.
    """
    return group_form_view(request)


@permission_required('auth.edit_group', login_url='unauthorized')
def edit_group(request, group_id):
    """
    Vista para editar un grupo, usando 'auth.edit_group'.
    """
    return group_form_view(request, group_id)


def group_form_view(request, group_id=None):
    """
    Vista compartida para crear y editar grupos.
    """
    grp = get_object_or_404(Group, pk=group_id) if group_id else None
    form = GroupForm(request.POST or None, instance=grp)

    if request.method == 'POST' and form.is_valid():
        group = form.save()
        group.permissions.set(request.POST.getlist('permissions'))
        messages.success(request, f'Grupo "{group.name}" {"actualizado" if group_id else "creado"} correctamente.')
        return redirect('manage_groups')

    # Obtener todos los permisos
    all_permissions = Permission.objects.all()
    selected_permissions = list(grp.permissions.values_list("id", flat=True)) if grp else []

    # Definir categorías de permisos
    categories = {
        "Usuarios": ["user", "change_password"],
        "Vuelos": ["flight"],
        "Aerolíneas": ["airline"],
        "Aeronaves": ["aircraft"],
        "Grupos": ["group"],
        "Tipos de Documentos": ["documenttype"],
        "Auditoría": ["audit"],
        "Configuración": ["settings", "access_dashboard", "access_settings"],
    }

    # Función para ordenar permisos
    def ordenar_permisos(perms):
        prioridad = ["view_", "create_", "edit_", "delete_"]
        return sorted(perms, key=lambda p: (prioridad.index(p.codename.split("_")[0]) if "_" in p.codename and p.codename.split("_")[0] in prioridad else 99, p.codename))

    # Agrupar permisos por categoría
    permission_categories = {
        cat: ordenar_permisos([p for p in all_permissions if any(key in p.codename for key in keys)])
        for cat, keys in categories.items()
    }

    return render(request, 'group_form.html', {
        'form': form,
        'group': grp,
        'permission_categories': permission_categories,
        'selected_permissions': selected_permissions
    })

@permission_required('auth.delete_group', login_url='unauthorized')
def delete_group(request, group_id):
    """
    Vista para eliminar un grupo, usando 'auth.delete_group'.
    """
    grp = get_object_or_404(Group, pk=group_id)
    grp.delete()
    messages.warning(request, f'Grupo "{grp.name}" eliminado.')
    
    return redirect('manage_groups')

def check_group_name(request):
    """Valida si un nombre de grupo ya existe en la base de datos y proporciona un mensaje detallado."""
    if request.method == "POST":
        try:
            data = json.loads(request.body)
            group_name = data.get("group_name", "").strip()

            # Verificar si el nombre está vacío
            if not group_name:
                return JsonResponse({"valid": False, "error": "El nombre del grupo no puede estar vacío."})

            # Validar caracteres permitidos (solo letras, números y espacios)
            if not re.match(r"^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s]+$", group_name):
                return JsonResponse({"valid": False, "error": "El nombre del grupo solo puede contener letras, números y espacios."})

            # Verificar si el grupo ya existe (sin distinguir mayúsculas/minúsculas)
            if Group.objects.filter(name__iexact=group_name).exists():
                return JsonResponse({
                    "valid": False,
                    "error": f"El grupo '{group_name}' ya existe."
                })

            # Si todo está bien, el nombre es válido
            return JsonResponse({"valid": True, "success": "✔ Nombre de grupo disponible."})

        except json.JSONDecodeError:
            return JsonResponse({"valid": False, "error": "⚠️ Error al procesar la solicitud."}, status=400)

    return JsonResponse({"valid": False, "error": "⚠️ Método no permitido."}, status=405)