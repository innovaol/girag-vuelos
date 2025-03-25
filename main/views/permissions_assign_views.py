# /home/innovaol/AppVuelos/main/views/permissions_assign_views.py

from django.shortcuts import render, redirect, get_object_or_404
from django.contrib.auth.models import Group, User, Permission
from django.contrib import messages
from django.contrib.auth.decorators import login_required

@login_required
def assign_permissions(request):
    groups = Group.objects.all()
    users = User.objects.all()
    permissions = Permission.objects.all()

    if request.method == 'POST':
        selected_group_id = request.POST.get('group')
        selected_user_id = request.POST.get('user')
        selected_perms = request.POST.getlist('permissions')  # IDs de Permission

        if selected_group_id:
            group = get_object_or_404(Group, id=selected_group_id)
            group.permissions.set(Permission.objects.filter(id__in=selected_perms))
            messages.success(request, f"Permisos actualizados para el grupo {group.name}.")

        elif selected_user_id:
            user = get_object_or_404(User, id=selected_user_id)
            user.user_permissions.set(Permission.objects.filter(id__in=selected_perms))
            messages.success(request, f"Permisos actualizados para el usuario {user.username}.")

        return redirect('assign_permissions')

    context = {
        'groups': groups,
        'users': users,
        'permissions': permissions
    }
    return render(request, 'assign_permissions.html', context)
