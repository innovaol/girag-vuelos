from django import forms
from main.models.flight import Flight
from main.models.airline import Airline

class FlightForm(forms.ModelForm):
    class Meta:
        model = Flight
        fields = ['flight_number', 'date', 'airline']
        labels = {
            'flight_number': 'Número de Vuelo',
            'date': 'Fecha',
            'airline': 'Aerolínea'
        }
        widgets = {
            'flight_number': forms.TextInput(attrs={
                'class': 'form-control', 
                'placeholder': 'Ingrese el número de vuelo'
            }),
            'date': forms.DateInput(format='%Y-%m-%d', attrs={
                'type': 'date', 
                'class': 'form-control'
            }),
            'airline': forms.Select(attrs={
                'class': 'form-select'
            }),
        }

    def __init__(self, *args, **kwargs):
        super(FlightForm, self).__init__(*args, **kwargs)
        # Formatear la fecha en modo edición
        if self.instance and self.instance.pk and self.instance.date:
            self.fields['date'].initial = self.instance.date.strftime('%Y-%m-%d')

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
