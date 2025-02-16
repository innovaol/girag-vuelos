from django import forms
from django.contrib.auth.models import Group

class GroupForm(forms.ModelForm):
    class Meta:
        model = Group
        fields = ['name', 'permissions']
        labels = {
            'name': 'Nombre',
            'permissions': 'Permisos'
        }
        widgets = {
            'name': forms.TextInput(attrs={
                'class': 'form-control',
                'placeholder': 'Ingrese el nombre del grupo'
            }),
            'permissions': forms.CheckboxSelectMultiple(),
        }

    def __init__(self, *args, **kwargs):
        super(GroupForm, self).__init__(*args, **kwargs)
        grouped = {}
        # Itera sobre cada checkbox del campo permissions
        for bound in self['permissions']:
            # Si el label contiene "|" se usa la parte anterior a la primera barra para agrupar,
            # y se elimina de la visualización
            if "|" in bound.choice_label:
                prefix, rest = bound.choice_label.split("|", 1)
                group_key = prefix.strip().lower()  # clave de agrupación (por ejemplo, "admin" o "auth")
                label = rest.strip()  # se quita el prefijo
            else:
                try:
                    perm_obj = self.fields['permissions'].queryset.get(pk=bound.choice_value)
                    group_key = perm_obj.content_type.app_label.lower()
                except Exception:
                    group_key = "otros"
                label = bound.choice_label
            grouped.setdefault(group_key, []).append((bound, label))
        self.grouped_permissions = grouped

        # Para cada grupo con 10 o más elementos, se guarda una versión dividida en dos columnas
        self.split_grouped_permissions = {}
        for key, items in grouped.items():
            if len(items) >= 10:
                half = (len(items) + 1) // 2
                self.split_grouped_permissions[key] = (items[:half], items[half:])
