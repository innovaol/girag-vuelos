from django.contrib.auth.decorators import permission_required
from django.shortcuts import render, redirect, get_object_or_404
from django.contrib import messages
from django.http import JsonResponse
from main.models.airline import Airline
from main.models.flight import Flight
from main.forms.airline_forms import AirlineForm
from main.utils.audit import log_action
import json
import re


@permission_required('main.view_airline', login_url='unauthorized')
def manage_airlines(request):
    """
    Vista para gestionar aerolíneas.
    Exige 'main.view_airline' para listar.
    """
    airlines = Airline.objects.all().order_by('name')
    return render(request, 'manage_airlines.html', {'airlines': airlines})


@permission_required('main.create_airline', login_url='unauthorized')
def create_airline(request):
    """
    Vista para crear una aerolínea.
    Exige 'main.add_airline'.
    """
    if request.method == 'POST':
        form = AirlineForm(request.POST)
        if form.is_valid():
            airline_name = form.cleaned_data['name'].strip().lower()

            if Airline.objects.filter(name__iexact=airline_name).exists():
                messages.error(request, "La aerolínea ya existe. Por favor, elige otro nombre.")
            else:
                form.save()
                messages.success(request, 'Aerolínea creada correctamente.')
                return redirect('manage_airlines')
    else:
        form = AirlineForm()
    
    return render(request, 'airline_form.html', {'form': form, 'title': 'Crear Aerolínea'})


@permission_required('main.edit_airline', login_url='unauthorized')
def edit_airline(request, airline_id):
    """
    Vista para editar una aerolínea existente.
    Exige 'main.edit_airline'.
    """
    airline = get_object_or_404(Airline, pk=airline_id)

    if request.method == 'POST':
        form = AirlineForm(request.POST, instance=airline)
        if form.is_valid():
            airline_name = form.cleaned_data['name'].strip().lower()

            if Airline.objects.filter(name__iexact=airline_name).exclude(pk=airline_id).exists():
                messages.error(request, "Ya existe otra aerolínea con este nombre. Por favor, elige otro.")
            else:
                form.save()
                messages.success(request, 'Aerolínea actualizada correctamente.')
                return redirect('manage_airlines')
    else:
        form = AirlineForm(instance=airline)

    return render(request, 'airline_form.html', {'form': form, 'title': 'Editar Aerolínea'})


@permission_required('main.delete_airline', login_url='unauthorized')
def delete_airline(request, airline_id):
    """
    Vista para eliminar una aerolínea.
    Exige 'main.delete_airline'.
    """
    airline = get_object_or_404(Airline, pk=airline_id)
    if Flight.objects.filter(airline=airline).exists():
        messages.error(request, "No se puede eliminar la aerolínea porque existen vuelos asociados a ella.")
        log_action(request.user, "Intentó eliminar una aerolínea", f"Aerolínea {airline.name} no eliminada por tener vuelos asociados.")
        return redirect('manage_airlines')

    try:
        airline.delete()
        messages.success(request, 'Aerolínea eliminada correctamente.')
        log_action(request.user, "Eliminó una aerolínea", f"Aerolínea {airline.name} eliminada.")
    except Exception as e:
        messages.error(request, f"No se pudo eliminar la aerolínea: {str(e)}")
        log_action(request.user, "Error al eliminar una aerolínea", f"Aerolínea {airline.name}: {str(e)}")

    return redirect('manage_airlines')
    
def check_airline_name(request):
    """Valida si un nombre de aerolínea ya existe en la base de datos y proporciona un mensaje detallado."""
    if request.method == "POST":
        try:
            data = json.loads(request.body.decode("utf-8"))
            airline_name = data.get("airline_name", "").strip()

            # Verificar si el nombre está vacío
            if not airline_name:
                return JsonResponse({"valid": False, "error": "⚠️ El nombre de la aerolínea no puede estar vacío."})

            # Validar caracteres permitidos (solo letras, números y espacios)
            if not re.match(r"^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s]+$", airline_name):
                return JsonResponse({"valid": False, "error": "⚠️ El nombre de la aerolínea solo puede contener letras, números y espacios."})

            # Verificar si la aerolínea ya existe (sin distinguir mayúsculas/minúsculas)
            if Airline.objects.filter(name__iexact=airline_name).exists():
                return JsonResponse({
                    "valid": False,
                    "error": f"La aerolínea ya existe."
                })

            # Si todo está bien, el nombre es válido
            return JsonResponse({"valid": True, "success": "✔ Nombre de aerolínea disponible."})

        except json.JSONDecodeError as e:
            return JsonResponse({"valid": False, "error": f"⚠️ Error al procesar la solicitud: {str(e)}"}, status=400)

    return JsonResponse({"valid": False, "error": "⚠️ Método no permitido."}, status=405)
