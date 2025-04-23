# /home/innovaol/girapp/main/forms/group_forms.py

from django import forms
from django.contrib.auth.models import Permission
from main.models.custom_group import CustomGroup

class GroupForm(forms.ModelForm):
    permissions = forms.ModelMultipleChoiceField(
        queryset=Permission.objects.all(),
        widget=forms.CheckboxSelectMultiple,
        required=False
    )

    class Meta:
        model = CustomGroup
        fields = ['name', 'permissions']
        labels = {
            'name': 'Nombre',
            'permissions': 'Permisos'
        }

    def __init__(self, *args, **kwargs):
        super(GroupForm, self).__init__(*args, **kwargs)
        grouped = {}

        # Cargar permisos existentes y marcarlos si están asignados
        selected_permissions = self.instance.permissions.all() if self.instance.pk else []

        for perm in self.fields['permissions'].queryset:
            # Solo mostramos el nombre del modelo (sin el app_label)
            model_name = perm.content_type.model.capitalize()

            if model_name not in grouped:
                grouped[model_name] = {'view': None, 'create': None, 'edit': None, 'delete': None}

            perm_data = {
                'id': perm.id,
                'selected': perm in selected_permissions
            }

            if 'view' in perm.codename:
                grouped[model_name]['view'] = perm_data
            elif 'add' in perm.codename:
                grouped[model_name]['create'] = perm_data
            elif 'change' in perm.codename:
                grouped[model_name]['edit'] = perm_data
            elif 'delete' in perm.codename:
                grouped[model_name]['delete'] = perm_data

        self.grouped_permissions = grouped
