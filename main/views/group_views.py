from django.shortcuts import render, redirect, get_object_or_404
from django.contrib.auth.decorators import login_required
from django.contrib.auth.models import Group
from django.contrib import messages
from main.forms.group_forms import GroupForm

@login_required
def manage_groups(request):
    if not request.user.is_superuser:
        return redirect('unauthorized')
    groups = Group.objects.all().order_by('name')
    return render(request, 'main/manage_groups.html', {'groups': groups})

@login_required
def create_group(request):
    if not request.user.is_superuser:
        return redirect('unauthorized')
    if request.method == 'POST':
        form = GroupForm(request.POST)
        if form.is_valid():
            form.save()
            messages.success(request, 'Grupo creado correctamente.')
            return redirect('manage_groups')
    else:
        form = GroupForm()
    return render(request, 'main/create_group.html', {'form': form})

@login_required
def edit_group(request, group_id):
    if not request.user.is_superuser:
        return redirect('unauthorized')
    grp = get_object_or_404(Group, pk=group_id)
    if request.method == 'POST':
        form = GroupForm(request.POST, instance=grp)
        if form.is_valid():
            form.save()
            messages.success(request, 'Grupo actualizado correctamente.')
            return redirect('manage_groups')
    else:
        form = GroupForm(instance=grp)
    return render(request, 'main/edit_group.html', {'form': form, 'group': grp})

@login_required
def delete_group(request, group_id):
    if not request.user.is_superuser:
        return redirect('unauthorized')
    grp = get_object_or_404(Group, pk=group_id)
    grp.delete()
    messages.warning(request, f'Grupo {grp.name} eliminado.')
    return redirect('manage_groups')
