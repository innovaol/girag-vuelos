from django import forms
from django.contrib.auth.models import User, Group
from django.contrib.auth.forms import UserCreationForm, UserChangeForm

class CustomUserCreationForm(UserCreationForm):
    email = forms.EmailField(required=True)
    group = forms.ModelChoiceField(
        queryset=Group.objects.all(),
        required=False,
        empty_label="Sin grupo"
    )
    
    class Meta:
        model = User
        fields = ['username', 'email', 'password1', 'password2', 'group']

class CustomUserChangeForm(UserChangeForm):
    email = forms.EmailField(required=True)
    
    class Meta:
        model = User
        fields = ['username', 'email', 'first_name', 'last_name', 'is_active', 'is_staff']
