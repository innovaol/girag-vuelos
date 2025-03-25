from django.shortcuts import render, redirect, get_object_or_404
from django.contrib.auth.decorators import login_required
from django.contrib.auth.models import Group, User, Permission
from django.contrib.contenttypes.models import ContentType
from django.contrib import messages

@login_required
def manage_permission_detail(request):
    """
    Vista que permite seleccionar un Grupo o Usuario y ver/editar
    """
    # Obtener el ContentType usado en sync_permissions
    try:
        content_type = ContentType.objects.get(app_label='main', model='dummypermissionmodel')
    except ContentType.DoesNotExist:
        return redirect('dashboard')

    # Obtener todos los permisos asociados a ese ContentType
    all_permissions = Permission.objects.filter(content_type=content_type).order_by('codename')

    # Obtener listas para llenar los selectores
    groups = Group.objects.all().order_by('name')
    users = User.objects.all().order_by('username')

    # Por defecto, se usa 'group'
    selected_type = request.GET.get('type', 'group')
    selected_id = request.GET.get('selected')
    selected_obj = None

    if selected_type == 'group' and selected_id:
        selected_obj = get_object_or_404(Group, id=selected_id)
    elif selected_type == 'user' and selected_id:
        selected_obj = get_object_or_404(User, id=selected_id)

    if request.method == 'POST':
        # Se procesa la actualización de permisos
        selected_type = request.POST.get('object_type', 'group')
        selected_id = request.POST.get('object_id')
        if selected_type == 'group' and selected_id:
            selected_obj = get_object_or_404(Group, id=selected_id)
        elif selected_type == 'user' and selected_id:
            selected_obj = get_object_or_404(User, id=selected_id)
        else:
            messages.error(request, "Debes seleccionar un objeto.")
            return redirect(request.path)

        # Limpiar los permisos actuales del objeto
        if selected_type == 'group':
            selected_obj.permissions.clear()
        else:
            selected_obj.user_permissions.clear()

        # Reasignar según los checkboxes marcados; 
        # se esperan campos con nombre "perm_<permission_id>"
        for key in request.POST:
            if key.startswith('perm_'):
                try:
                    _, perm_id = key.split('_')
                    perm_obj = Permission.objects.get(id=int(perm_id))
                    if selected_type == 'group':
                        selected_obj.permissions.add(perm_obj)
                    else:
                        selected_obj.user_permissions.add(perm_obj)
                except Exception as e:
                    print("Error asignando permiso:", e)
                    continue

        messages.success(request, "Permisos actualizados correctamente.")
        return redirect(f"{request.path}?type={selected_type}&selected={selected_id}")

    context = {
        'all_permissions': all_permissions,
        'groups': groups,
        'users': users,
        'selected_type': selected_type,
        'selected_obj': selected_obj,
    }
    return render(request, 'manage_permission_detail.html', context)
