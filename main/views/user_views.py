# /home/innovaol/AppVuelos/main/views/user_views.py

from django.contrib.auth.decorators import login_required, permission_required
from django.shortcuts import render, redirect, get_object_or_404
from django.contrib import messages
from django.http import JsonResponse
from django.db import IntegrityError
from django.contrib.auth import get_user_model
from django.contrib.auth.models import Permission
from main.models.custom_group import CustomGroup
from main.models.flight import Flight  # ✅ Para validar vuelos asociados
from main.models.custom_user import CustomUser  # ✅ Ruta corregida para CustomUser
from main.models.audit_log import AuditLog  # ✅ Importación corregida de AuditLog
from main.views.permissions_views import get_available_permissions
from main.utils.audit import log_action  # ✅ Para registrar acciones de auditoría
import json
from django.urls import reverse
from django.views.decorators.http import require_POST


# Define los codenames especiales que no quieres en la lista regular
# special_codenames = ['admin_vuelos', 'approve_flight', 'mark_as_billed']

# Obtén solo los permisos regulares (excluyendo los especiales)
# regular_permissions = Permission.objects.exclude(codename__in=special_codenames)
    
User = get_user_model()

def manage_users(request):
    """ Vista para listar usuarios """
    users = User.objects.filter(is_archived=False).order_by('username')
    user_data = [
        {
            'id': u.id,
            'username': u.username,
            'email': u.email,
            'is_active': u.is_active,
            'group': u.groups_custom.first().name if u.groups_custom.exists() else "Sin grupo",
            'last_login': u.last_login
        }
        for u in users
    ]
    return render(request, 'manage_users.html', {'users': user_data})

@permission_required('main.create_user', login_url='unauthorized')
def create_user(request):
    from main.forms.user_forms import CustomUserCreationForm

    if request.method == 'POST':
        form = CustomUserCreationForm(request.POST)
        if form.is_valid():
            username = form.cleaned_data.get('username')
            # Log para ver cuántos registros existen con ese username
            existing = User.objects.filter(username=username).count()
            print("Número de registros existentes para username '{}': {}".format(username, existing))
                
            try:
                user = form.save()
                # Asignar grupo si se seleccionó
                group = form.cleaned_data.get('group')
                if group:
                    user.groups_custom.set([group])

            except Exception as e:
                print("Error al guardar usuario:", e)
                raise

            # Admin de vuelos
            if form.cleaned_data.get('is_admin_vuelos'):
                perm = Permission.objects.get(codename='admin_vuelos')
                user.user_permissions.add(perm)

            # Supervisores
            if form.cleaned_data.get('is_flight_supervisor'):
                try:
                    CustomGroup.objects.get(name="Supervisores de Vuelos").users_custom.add(user)
                except Group.DoesNotExist:
                    messages.warning(request, "No existe el grupo 'Supervisores de Vuelos'.")
                    
            if form.cleaned_data.get('is_billing_supervisor'):
                try:
                    Group.objects.get(name="Facturadores de Vuelos").user_set.add(user)
                except Group.DoesNotExist:
                    messages.warning(request, "No existe el grupo 'Facturadores de Vuelos'.")
            
            # Permisos normales:
            user.user_permissions.set(request.POST.getlist('permissions'))
            
            messages.success(request, 'Usuario creado correctamente.')
            return redirect('manage_users')

        messages.error(request, 'Error al crear el usuario.')
    else:
        form = CustomUserCreationForm()

    return render(request, 'user_form.html', {
        'form': form,
        'permissions': get_available_permissions(),
        'selected_permissions': []
    })

@permission_required('main.edit_user', login_url='unauthorized')
def edit_user(request, user_id):
    from main.forms.user_forms import CustomUserChangeForm
    user_obj = get_object_or_404(User, pk=user_id)

    if request.method == 'POST':
        form = CustomUserChangeForm(request.POST, instance=user_obj)
        if form.is_valid():
            user = form.save()

            # Grupo
            group = form.cleaned_data.get('group')
            if group:
                user.groups_custom.set([group])
            else:
                user.groups_custom.clear()

            # Permisos normales
            user.user_permissions.set(request.POST.getlist('permissions'))

            # Admin de vuelos
            perm = Permission.objects.get(codename='admin_vuelos')
            if form.cleaned_data.get('is_admin_vuelos'):
                user.user_permissions.add(perm)
            else:
                user.user_permissions.remove(perm)

            messages.success(request, 'Usuario actualizado correctamente.')
            return redirect('manage_users')

        messages.error(request, 'Error al actualizar el usuario.')

    else:
        form = CustomUserChangeForm(instance=user_obj)

    return render(request, 'user_form.html', {
        'form': form,
        'user_obj': user_obj,
        'permissions': get_available_permissions(),
        'selected_permissions': list(user_obj.user_permissions.values_list("id", flat=True))
    })

@require_POST
@login_required
@permission_required('main.delete_user', raise_exception=True)
def delete_user(request, user_id):
    """Vista para eliminar un usuario. Si tiene vuelos asociados, sugiere archivarlo."""
    user = get_object_or_404(CustomUser, pk=user_id)

    vuelos_asociados = Flight.objects.filter(created_by=user).exists() or \
                       Flight.objects.filter(approved_by=user).exists() or \
                       Flight.objects.filter(billed_by=user).exists()

    if vuelos_asociados:
        return JsonResponse({
            'success': False,
            'archivable': True,
            'error': f'El usuario "{user.username}" está asociado a vuelos y no puede eliminarse.'
        })

    try:
        username = user.username
        user.delete()
        log_action(request.user, "Eliminó usuario", f"Usuario {username} eliminado correctamente.")

        return JsonResponse({
            'success': True,
            'message': f'Usuario "{username}" eliminado correctamente.'
        })

    except IntegrityError as e:
        log_action(request.user, "Error al eliminar usuario", f"No se pudo eliminar {user.username}: {str(e)}")
        return JsonResponse({ 'success': False, 'error': str(e) })

    except Exception as e:
        log_action(request.user, "Error inesperado al eliminar usuario", f"No se pudo eliminar {user.username}: {str(e)}")
        return JsonResponse({ 'success': False, 'error': f'Error inesperado: {str(e)}' })

def check_username(request):
    """ Verifica si un nombre de usuario ya existe """
    if request.method == "POST":
        data = json.loads(request.body)
        username = data.get("username", "").strip()

        if User.objects.filter(username=username).exists():
            return JsonResponse({"valid": False, "error": "Este nombre de usuario ya está en uso."})
        return JsonResponse({"valid": True})

def check_email(request):
    """ Verifica si un correo electrónico ya está registrado """
    if request.method == "POST":
        data = json.loads(request.body)
        email = data.get("email", "").strip()

        if User.objects.filter(email=email).exists():
            return JsonResponse({"valid": False, "error": "Este correo electrónico ya está en uso."})
        return JsonResponse({"valid": True})

@permission_required('main.change_password', login_url='unauthorized')
def change_user_password(request, user_id):
    """Vista para cambiar la contraseña de un usuario sin AJAX."""
    from main.forms.user_forms import CustomAdminPasswordChangeForm
    user_instance = get_object_or_404(User, pk=user_id)

    if request.user != user_instance and not request.user.has_perm('main.change_password'):
        return HttpResponseForbidden("No tienes permisos para cambiar esta contraseña.")

    if request.method == 'POST':
        form = CustomAdminPasswordChangeForm(user_instance, request.POST)
        if form.is_valid():
            user = form.save()
            from django.contrib.auth import update_session_auth_hash
            update_session_auth_hash(request, user)
            messages.success(request, "Contraseña actualizada correctamente.")
            return redirect('manage_users')
        else:
            messages.error(request, "Error al actualizar la contraseña.")
    else:
        form = CustomAdminPasswordChangeForm(user=user_instance)

    return render(request, 'change_user_password.html', {
        'form': form,
        'user_instance': user_instance
    })

@login_required
@permission_required('auth.restore_user', raise_exception=True)
def archived_users(request):
    """
    Lista usuarios archivados (is_archived=True)
    """
    users = CustomUser.all_objects.filter(is_archived=True).order_by('username')
    user_data = [
        {
            'id': u.id,
            'username': u.username,
            'email': u.email,
            'is_active': u.is_active,
            'group': u.groups_custom.first().name if u.groups_custom.exists() else "Sin grupo",
            'last_login': u.last_login
        }
        for u in users
    ]
    return render(request, 'archived_users.html', {'users': user_data})

@require_POST
@login_required
@permission_required('main.delete_user', raise_exception=True)
def archive_user(request, user_id):
    user = get_object_or_404(CustomUser, pk=user_id)

    # ❌ No permitir archivar al único superusuario activo
    if user.is_superuser and CustomUser.objects.filter(is_superuser=True, is_active=True, is_archived=False).count() == 1:
        return JsonResponse({
            'success': False,
            'error': 'No se puede archivar al único superusuario activo del sistema.'
        })

    vuelos_asociados = Flight.objects.filter(created_by=user).exists() or \
                       Flight.objects.filter(approved_by=user).exists() or \
                       Flight.objects.filter(billed_by=user).exists()

    if vuelos_asociados:
        return JsonResponse({
            'success': False,
            'error': f'El usuario "{user.username}" está asociado a vuelos y no puede archivarse.'
        })

    user.is_archived = True
    user.is_active = False
    user.save()

    log_action(request.user, "Archivó usuario", f"Usuario {user.username} archivado.")
    return JsonResponse({ 'success': True, 'message': f'Usuario "{user.username}" archivado correctamente.' })


@login_required
@permission_required('main.restore_user', raise_exception=True)
def restore_user(request, user_id):
    user_obj = get_object_or_404(CustomUser.all_objects, pk=user_id, is_archived=True)
    # Verificar si no hay duplicado de username activo, etc. (opcional)
    user_obj.is_archived = False
    user_obj.save()
    messages.success(request, f'Usuario "{user_obj.username}" restaurado.')
    return redirect('manage_users')
