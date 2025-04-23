# /home/innovaol/girapp/main/forms/user_forms.py

from django import forms
from django.contrib.auth.forms import UserCreationForm, UserChangeForm, AdminPasswordChangeForm
from main.models.custom_user import CustomUser  # Importa tu modelo personalizado
from main.models.custom_group import CustomGroup

class CustomUserCreationForm(UserCreationForm):
    username = forms.CharField(max_length=150, required=True, label="Nombre de usuario")
    first_name = forms.CharField(max_length=30, required=True, label="Nombre")
    last_name = forms.CharField(max_length=30, required=True, label="Apellido")
    email = forms.EmailField(required=True, label="Correo electrónico")
    password1 = forms.CharField(widget=forms.PasswordInput(), label="Contraseña")
    password2 = forms.CharField(widget=forms.PasswordInput(), label="Confirmar contraseña")
    is_active = forms.BooleanField(required=False, initial=True, label="Usuario activo")
    is_superuser = forms.BooleanField(required=False, label="Superusuario")
    is_flight_supervisor = forms.BooleanField(required=False, label="Supervisor de Vuelos")
    is_billing_supervisor = forms.BooleanField(required=False, label="Facturador de Vuelos")
    is_admin_vuelos = forms.BooleanField(required=False, label="Admin de Vuelos")
    group = forms.ModelChoiceField(
        queryset=CustomGroup.objects.all(),
        required=False,
        empty_label="Sin grupo",
        label="Grupo"
    )


    
    class Meta:
        model = CustomUser
        fields = ['username', 'first_name', 'last_name', 'email', 'password1', 'password2',
                  'is_active', 'is_superuser', 'is_flight_supervisor', 'is_billing_supervisor', 'is_admin_vuelos', 'group']

    def clean_username(self):
        username = self.cleaned_data.get('username')
        if CustomUser.objects.filter(username=username).exists():
            raise forms.ValidationError("El nombre de usuario ya existe. Por favor, elige otro.")
        return username

    def save(self, commit=True):
        user = super().save(commit=False)
        # Los valores de is_flight_supervisor, is_billing_supervisor e is_admin_vuelos se guardarán directamente.
        if commit:
            user.save()
        return user


class CustomUserChangeForm(UserChangeForm):
    username = forms.CharField(max_length=150, required=True, label="Nombre de usuario")
    first_name = forms.CharField(max_length=30, required=True, label="Nombre")
    last_name = forms.CharField(max_length=30, required=True, label="Apellido")
    email = forms.EmailField(required=True, label="Correo electrónico")
    is_active = forms.BooleanField(required=False, label="Usuario activo")
    is_superuser = forms.BooleanField(required=False, label="Superusuario")
    is_flight_supervisor = forms.BooleanField(required=False, label="Supervisor de Vuelos")
    is_billing_supervisor = forms.BooleanField(required=False, label="Facturador de Vuelos")
    is_admin_vuelos = forms.BooleanField(required=False, label="Admin de Vuelos")
    group = forms.ModelChoiceField(
        queryset=CustomGroup.objects.all(),
        required=False,
        empty_label="Sin grupo",
        label="Grupo",
        widget=forms.Select(attrs={'class': 'form-control'})
    )
    
    class Meta:
        model = CustomUser
        fields = ['username', 'first_name', 'last_name', 'email', 'is_active', 'is_superuser',
                  'is_flight_supervisor', 'is_billing_supervisor', 'is_admin_vuelos', 'group']

    def __init__(self, *args, **kwargs):
        instance = kwargs.get('instance')
        if instance:
            initial = kwargs.setdefault('initial', {})
            groups = instance.groups_custom.all()
            if groups.exists():
                initial['group'] = groups[0]
            else:
                initial['group'] = None
            # Inicializamos el campo is_admin_vuelos
            initial['is_admin_vuelos'] = instance.is_admin_vuelos
        super().__init__(*args, **kwargs)
    
    def save(self, commit=True):
        user = super().save(commit=False)
        group = self.cleaned_data.get('group')
        if commit:
            user.save()
            if group:
                user.groups_custom.set([group])
            else:
                user.groups_custom.clear()
        else:
            def save_m2m():
                if group:
                    user.groups_custom.set([group])
                else:
                    user.groups_custom.clear()
            self.save_m2m = save_m2m
        return user
        
    def clean(self):
        cleaned_data = super().clean()
        is_superuser = cleaned_data.get('is_superuser')
    
        if self.instance.is_superuser and not is_superuser:
            from django.contrib.auth import get_user_model
            User = get_user_model()
            superusers = User.objects.filter(is_superuser=True).exclude(id=self.instance.id)
            if not superusers.exists():
                self.add_error('is_superuser', "Debe haber al menos un superusuario en el sistema.")
    
        return cleaned_data


class CustomAdminPasswordChangeForm(AdminPasswordChangeForm):
    def __init__(self, *args, **kwargs):
        super().__init__(*args, **kwargs)
        try:
            self.fields['new_password2'].label = "Repetir contraseña"
        except KeyError:
            pass

    def clean(self):
        cleaned_data = super().clean()
        new_password1 = cleaned_data.get("new_password1")
        new_password2 = cleaned_data.get("new_password2")
        if new_password1 and new_password2 and new_password1 != new_password2:
            self.add_error("new_password2", "Las contraseñas no coinciden.")
        return cleaned_data
