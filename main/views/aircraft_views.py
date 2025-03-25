from django.shortcuts import render, get_object_or_404, redirect
from django.contrib import messages
from django.http import JsonResponse
from main.models.aircraft import Aircraft
from main.forms.aircraft_forms import AircraftForm
import json
import re

# Listar Aeronaves
def manage_aircrafts(request):
    aircrafts = Aircraft.objects.all()
    return render(request, 'manage_aircrafts.html', {'aircrafts': aircrafts})

# Crear/Editar Aeronave
def aircraft_form(request, pk=None):
    if pk:
        aircraft = get_object_or_404(Aircraft, pk=pk)
    else:
        aircraft = None

    if request.method == 'POST':
        form = AircraftForm(request.POST, instance=aircraft)
        if form.is_valid():
            aircraft_name = form.cleaned_data['aeronave'].strip().lower()  # Convertimos a minúsculas

            # Si se está creando una nueva aeronave
            if not pk and Aircraft.objects.filter(aeronave__iexact=aircraft_name).exists():
                messages.error(request, "La aeronave ya existe. Por favor, elige otro nombre.")
                
            # Si se está editando una aeronave, verificamos que el nombre no sea duplicado en otra instancia
            elif pk and Aircraft.objects.filter(aeronave__iexact=aircraft_name).exclude(pk=pk).exists():
                messages.error(request, "Ya existe otra aeronave con este nombre. Por favor, elige otro.")
                
            else:
                form.save()
                messages.success(request, 'Aeronave guardada exitosamente.')
                return redirect('manage_aircrafts')

    else:
        form = AircraftForm(instance=aircraft)

    return render(request, 'aircraft_form.html', {'form': form, 'title': 'Crear/Editar Aeronave'})

# Eliminar Aeronave
def delete_aircraft(request, pk):
    aircraft = get_object_or_404(Aircraft, pk=pk)
    aircraft.delete()
    messages.success(request, 'Aeronave eliminada exitosamente.')
    return redirect('manage_aircrafts')

# ✅ API para obtener aeronaves por aerolínea (usada en FlightForm)
def get_aircrafts_by_airline(request, airline_id):
    """Devuelve aeronaves asociadas a la aerolínea seleccionada"""
    try:
        aircrafts = Aircraft.objects.filter(aerolinea_id=airline_id)
        data = [{'id': ac.id, 'aeronave': ac.aeronave} for ac in aircrafts]
        return JsonResponse({'aircrafts': data})
    except Exception as e:
        # Devuelve el error para poder identificar el problema
        return JsonResponse({'error': str(e)}, status=500)

    
def check_aircraft_name(request):
    """Valida si un nombre de aeronave ya existe y proporciona un mensaje detallado."""
    if request.method == "POST":
        try:
            data = json.loads(request.body.decode("utf-8"))
            aircraft_name = data.get("aircraft_name", "").strip()

            if not aircraft_name:
                return JsonResponse({
                    "valid": False,
                    "error": "El nombre de la aeronave no puede estar vacío."
                })

            # Validar si la aeronave ya existe (usando el campo 'aeronave')
            if Aircraft.objects.filter(aeronave__iexact=aircraft_name).exists():
                return JsonResponse({
                    "valid": False,
                    "error": f"La aeronave '{aircraft_name}' ya existe."
                })

            return JsonResponse({
                "valid": True,
                "success": "✔ Nombre de aeronave disponible."
            })

        except json.JSONDecodeError as e:
            return JsonResponse({
                "valid": False,
                "error": f"⚠️ Error al procesar la solicitud: {str(e)}"
            }, status=400)
    return JsonResponse({
        "valid": False,
        "error": "⚠️ Método no permitido."
    }, status=405)
