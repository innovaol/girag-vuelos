# /home/innovaol/AppVuelos/main/views/user_views.py

from django.contrib.auth.decorators import permission_required
from django.shortcuts import render, redirect, get_object_or_404
from django.contrib import messages
from django.http import JsonResponse
import json
from django.contrib.auth import get_user_model
from main.views.permissions_views import get_available_permissions
from django.contrib.auth.models import Permission

# Define los codenames especiales que no quieres en la lista regular
# special_codenames = ['admin_vuelos', 'approve_flight', 'mark_as_billed']

# Obtén solo los permisos regulares (excluyendo los especiales)
# regular_permissions = Permission.objects.exclude(codename__in=special_codenames)
    
User = get_user_model()

def manage_users(request):
    """ Vista para listar usuarios """
    users = User.objects.all().order_by('username')
    user_data = [
        {
            'id': u.id,
            'username': u.username,
            'email': u.email,
            'is_active': u.is_active,
            'group': u.groups.first().name if u.groups.exists() else "Sin grupo",
            'last_login': u.last_login
        }
        for u in users
    ]
    return render(request, 'manage_users.html', {'users': user_data})

@permission_required('auth.create_user', login_url='unauthorized')
def create_user(request):
    from main.forms.user_forms import CustomUserCreationForm

    if request.method == 'POST':
        form = CustomUserCreationForm(request.POST)
        if form.is_valid():
            user = form.save()

            # Admin de vuelos
            if form.cleaned_data.get('is_admin_vuelos'):
                perm = Permission.objects.get(codename='admin_vuelos')
                user.user_permissions.add(perm)

            # Supervisores
            if form.cleaned_data.get('is_flight_supervisor'):
                try:
                    Group.objects.get(name="Supervisores de Vuelos").user_set.add(user)
                except Group.DoesNotExist:
                    messages.warning(request, "No existe el grupo 'Supervisores de Vuelos'.")
            if form.cleaned_data.get('is_billing_supervisor'):
                try:
                    Group.objects.get(name="Facturadores de Vuelos").user_set.add(user)
                except Group.DoesNotExist:
                    messages.warning(request, "No existe el grupo 'Facturadores de Vuelos'.")
            
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

@permission_required('auth.edit_user', login_url='unauthorized')
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
                user.groups.set([group])
            else:
                user.groups.clear()

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

@permission_required('auth.delete_user', login_url='unauthorized')
def delete_user(request, user_id):
    """ Vista para eliminar un usuario """
    user_obj = get_object_or_404(User, pk=user_id)
    user_obj.delete()
    messages.warning(request, f'Usuario {user_obj.username} eliminado.')
    return redirect('manage_users')

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

@permission_required('auth.change_password', login_url='unauthorized')
def change_user_password(request, user_id):
    """Vista para cambiar la contraseña de un usuario sin AJAX."""
    from main.forms.user_forms import CustomAdminPasswordChangeForm
    user_instance = get_object_or_404(User, pk=user_id)

    if request.user != user_instance and not request.user.has_perm('auth.change_password'):
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
