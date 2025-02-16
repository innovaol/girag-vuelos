from django import forms
from main.models.airline import Airline

class AirlineForm(forms.ModelForm):
    class Meta:
        model = Airline
        fields = ['name']
        labels = {
            'name': 'Aerolínea'
        }
        widgets = {
            'name': forms.TextInput(attrs={
                'class': 'form-control',
                'placeholder': 'Ingrese el nombre de la aerolínea'
            }),
        }
