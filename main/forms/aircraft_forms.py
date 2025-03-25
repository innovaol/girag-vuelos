# forms/aircraft_forms.py

from django import forms
from main.models.aircraft import Aircraft
from main.models.airline import Airline

class AircraftForm(forms.ModelForm):
    class Meta:
        model = Aircraft
        fields = ['aeronave', 'aerolinea']  # Cambiado a 'aeronave'
        widgets = {
            'aeronave': forms.TextInput(attrs={'class': 'form-control', 'placeholder': 'Aeronave'}),  # Usamos 'aeronave'
            'aerolinea': forms.Select(attrs={'class': 'form-control'})
        }

def get_aircrafts_by_airline(request, airline_id):
    aircrafts = Aircraft.objects.filter(aerolinea_id=airline_id).values('id', 'numero_aeronave')
    return JsonResponse(list(aircrafts), safe=False)
