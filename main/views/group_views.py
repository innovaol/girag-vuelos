# /home/innovaol/girapp/main/views/group_views.py

from django.contrib.auth.decorators import login_required
from django.shortcuts import render, redirect, get_object_or_404
from django.http import JsonResponse
from django.views.decorators.http import require_POST
from django.contrib import messages
from django.db import IntegrityError
import json, re

from main.models.custom_group import CustomGroup
from main.models.custom_user import CustomUser
from main.models.group import GroupExtension
from main.forms.group_forms import GroupForm
from main.views.permissions_views import get_available_permissions
from main.utils.audit import log_action
from main.utils.utils import check_group_name


@login_required
def manage_groups(request):
    if not request.user.has_perm('main.view_group'):
        return redirect('unauthorized')

    groups = CustomGroup.objects.all().order_by('name')
    return render(request, 'manage_groups.html', {'groups': groups})


@login_required
def create_group(request):
    if not request.user.has_perm('main.create_group'):
        return redirect('unauthorized')
    return group_form_view(request)


@login_required
def edit_group(request, group_id):
    if not request.user.has_perm('main.edit_group'):
        return redirect('unauthorized')
    return group_form_view(request, group_id)


def group_form_view(request, group_id=None):
    grp = get_object_or_404(CustomGroup, pk=group_id) if group_id else None
    form = GroupForm(request.POST or None, instance=grp)

    if request.method == 'POST':
        if form.is_valid():
            group = form.save()
            group.permissions.set(request.POST.getlist('permissions'))
            GroupExtension.objects.get_or_create(group=group)
            messages.success(request, f'Grupo "{group.name}" {"actualizado" if group_id else "creado"} correctamente.')
            return redirect('manage_groups')
        else:
            print("❌ Errores del formulario:", form.errors)

    selected_permissions = list(grp.permissions.values_list("id", flat=True)) if grp else []
    permissions = get_available_permissions()

    return render(request, 'group_form.html', {
        'form': form,
        'group': grp,
        'permissions': permissions,
        'selected_permissions': selected_permissions
    })


@require_POST
@login_required
def delete_group(request, group_id):
    if not request.user.has_perm('main.delete_group'):
        return JsonResponse({'success': False, 'error': 'No tienes permisos para eliminar grupos.'})

    try:
        group = CustomGroup.objects.get(pk=group_id)
    except CustomGroup.DoesNotExist:
        return JsonResponse({'success': False, 'error': 'El grupo no existe o ya fue eliminado.'})

    if CustomUser.objects.filter(groups_custom=group).exists():
        return JsonResponse({
            'success': False,
            'archivable': True,
            'error': f'El grupo "{group.name}" está asociado a usuarios y no puede eliminarse.'
        })

    try:
        group.delete()
        log_action(request.user, "Eliminó grupo", f"Grupo {group.name} eliminado correctamente.")
        return JsonResponse({'success': True, 'message': f'Grupo "{group.name}" eliminado correctamente.'})

    except IntegrityError as e:
        log_action(request.user, "Error al eliminar grupo", f"No se pudo eliminar '{group.name}': {str(e)}")
        return JsonResponse({'success': False, 'error': str(e)})

    except Exception as e:
        log_action(request.user, "Error inesperado al eliminar grupo", f"{group.name}: {str(e)}")
        return JsonResponse({'success': False, 'error': str(e)})


@require_POST
@login_required
def archive_group(request, group_id):
    if not request.user.has_perm('main.delete_group'):
        return JsonResponse({'success': False, 'error': 'No tienes permisos para archivar grupos.'})

    try:
        group = CustomGroup.objects.get(pk=group_id)
    except CustomGroup.DoesNotExist:
        return JsonResponse({'success': False, 'error': 'El grupo no existe o ya fue eliminado.'})

    group.is_archived = True
    group.save()
    log_action(request.user, "Archivó grupo", f"Grupo {group.name} archivado correctamente.")
    return JsonResponse({'success': True, 'message': f'Grupo "{group.name}" archivado correctamente.'})


@login_required
def archived_groups(request):
    if not request.user.has_perm('main.restore_group'):
        return redirect('unauthorized')

    groups = CustomGroup.all_objects.filter(is_archived=True).order_by('name')
    return render(request, 'archived_groups.html', {'groups': groups})


@login_required
def restore_group(request, group_id):
    if not request.user.has_perm('main.restore_group'):
        return redirect('unauthorized')

    group = get_object_or_404(CustomGroup.all_objects, pk=group_id, is_archived=True)
    group.restore()
    log_action(request.user, "Restauró grupo", f"Grupo {group.name} restaurado.")
    messages.success(request, f'Grupo "{group.name}" restaurado correctamente.')
    return redirect('archived_groups')


def check_group_name(request):
    if request.method == "POST":
        try:
            data = json.loads(request.body)
            group_name = data.get("group_name", "").strip()

            if not group_name:
                return JsonResponse({"valid": False, "error": "El nombre del grupo no puede estar vacío."})

            if not re.match(r"^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s]+$", group_name):
                return JsonResponse({"valid": False, "error": "El nombre del grupo solo puede contener letras, números y espacios."})

            if CustomGroup.objects.filter(name__iexact=group_name).exists():
                return JsonResponse({"valid": False, "error": f"El grupo '{group_name}' ya existe."})

            return JsonResponse({"valid": True, "success": "✔ Nombre de grupo disponible."})

        except json.JSONDecodeError:
            return JsonResponse({"valid": False, "error": "⚠️ Error al procesar la solicitud."}, status=400)

    return JsonResponse({"valid": False, "error": "⚠️ Método no permitido."}, status=405)
