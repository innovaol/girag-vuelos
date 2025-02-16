from django.shortcuts import render, redirect, get_object_or_404
from django.contrib.auth.decorators import login_required
from django.contrib.auth.models import User, Group
from django.contrib import messages
from main.forms.user_forms import CustomUserCreationForm, CustomUserChangeForm

@login_required
def manage_users(request):
    """
    Vista para listar usuarios. Solo accesible para superusuarios.
    """
    if not request.user.is_superuser:
        return redirect('unauthorized')
    users = User.objects.all().order_by('username')
    return render(request, 'main/manage_users.html', {'users': users})

@login_required
def create_user(request):
    """
    Vista para crear un usuario. Incluye un campo opcional para seleccionar un grupo.
    """
    if not request.user.is_superuser:
        return redirect('unauthorized')
    if request.method == 'POST':
        form = CustomUserCreationForm(request.POST)
        if form.is_valid():
            user = form.save(commit=False)
            user.save()
            # Asigna el grupo seleccionado (si lo hay)
            group = form.cleaned_data.get('group')
            if group:
                user.groups.add(group)
            messages.success(request, 'Usuario creado correctamente.')
            return redirect('manage_users')
    else:
        form = CustomUserCreationForm()
    return render(request, 'main/create_user.html', {'form': form})

@login_required
def edit_user(request, user_id):
    """
    Vista para editar un usuario existente.
    """
    if not request.user.is_superuser:
        return redirect('unauthorized')
    user_obj = get_object_or_404(User, pk=user_id)
    if request.method == 'POST':
        form = CustomUserChangeForm(request.POST, instance=user_obj)
        if form.is_valid():
            form.save()
            messages.success(request, 'Usuario actualizado correctamente.')
            return redirect('manage_users')
    else:
        form = CustomUserChangeForm(instance=user_obj)
    return render(request, 'main/edit_user.html', {'form': form, 'user_obj': user_obj})

@login_required
def delete_user(request, user_id):
    """
    Vista para eliminar un usuario existente.
    """
    if not request.user.is_superuser:
        return redirect('unauthorized')
    user_obj = get_object_or_404(User, pk=user_id)
    user_obj.delete()
    messages.warning(request, f'Usuario {user_obj.username} eliminado.')
    return redirect('manage_users')
