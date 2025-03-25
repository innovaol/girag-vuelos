# main/forms/flight_forms.py

from django import forms
from datetime import date
from main.models.flight import Flight  # Correcto
from main.models.airline import Airline
from main.models.aircraft import Aircraft
from django.contrib.auth import get_user_model

User = get_user_model()


class FlightForm(forms.ModelForm):
    billing_user = forms.ModelChoiceField(queryset=User.objects.filter(groups__name='Facturación'), required=False)
    
    # Campo extra para mostrar la fecha de creación (solo lectura)
    created_at = forms.DateField(
        label="Fecha de Creación",
        required=False,
        widget=forms.DateInput(attrs={
            'type': 'date',
            'class': 'form-control',
            'readonly': 'readonly'
        })
    )

    class Meta:
        model = Flight
        fields = ['flight_number', 'date', 'airline', 'aircraft', 'billing_user']
        labels = {
            'flight_number': 'Número de Vuelo',
            'date': 'Fecha del Vuelo',
            'airline': 'Aerolínea',
            'aircraft': 'Aeronave'
        }
        widgets = {
            'flight_number': forms.TextInput(attrs={
                'class': 'form-control',
                'placeholder': 'Ingrese el número de vuelo'
            }),
           # Modificamos el widget de 'date':
            'date': forms.DateInput(format='%d/%m/%Y', attrs={
                'type': 'text',  # Cambiado de "date" a "text" para evitar el selector nativo
                'class': 'form-control datepicker',  # Agregamos una clase para activarle un datepicker
                'placeholder': 'dd/mm/yyyy'
            }),
            'airline': forms.Select(attrs={
                'class': 'form-select',
                'id': 'airline-select'
            }),
            'aircraft': forms.Select(attrs={
                'class': 'form-select',
                'id': 'aircraft-select'
            }),
        }

    def __init__(self, *args, **kwargs):
        super(FlightForm, self).__init__(*args, **kwargs)
        # Configurar el campo created_at
        if self.instance and self.instance.pk and self.instance.created_at:
            self.fields['created_at'].initial = self.instance.created_at.strftime('%Y-%m-%d')
        else:
            self.fields['created_at'].initial = date.today().strftime('%Y-%m-%d')

        # Filtrar aeronaves si hay aerolínea seleccionada
        if 'airline' in self.data:
            try:
                airline_id = int(self.data.get('airline'))
                self.fields['aircraft'].queryset = Aircraft.objects.filter(aerolinea_id=airline_id)
            except (ValueError, TypeError):
                self.fields['aircraft'].queryset = Aircraft.objects.none()
        elif self.instance.pk:
            self.fields['aircraft'].queryset = Aircraft.objects.filter(aerolinea=self.instance.airline)
        else:
            self.fields['aircraft'].queryset = Aircraft.objects.none()

class FlightReportForm(forms.Form):
    start_date = forms.DateField(
        label="Fecha Inicial",
        required=False,
        widget=forms.DateInput(attrs={
            'type': 'date',
            'class': 'form-control'
        }),
        input_formats=['%Y-%m-%d']
    )
    end_date = forms.DateField(
        label="Fecha Final",
        required=False,
        widget=forms.DateInput(attrs={
            'type': 'date',
            'class': 'form-control'
        }),
        input_formats=['%Y-%m-%d']
    )
