# /home/innovaol/girapp/main/views/airline_views.py

from django.contrib.auth.decorators import login_required, permission_required
from django.shortcuts import render, redirect, get_object_or_404
from django.contrib import messages
from django.http import JsonResponse
from django.db import IntegrityError
from main.models.airline import Airline
from main.models.flight import Flight
from main.models.aircraft import Aircraft
from main.forms.airline_forms import AirlineForm
from main.utils.audit import log_action
import json
import re
from django.views.decorators.http import require_POST


@permission_required('main.view_airline', login_url='unauthorized')
def manage_airlines(request):
    """
    Vista para gestionar aerolíneas.
    Exige 'main.view_airline' para listar.
    """
    airlines = Airline.objects.filter(is_archived=False).order_by('name')
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

@require_POST
@login_required
@permission_required('main.delete_airline', login_url='unauthorized')
def delete_airline(request, airline_id):
    """
    Vista para eliminar una aerolínea vía fetch/JSON.
    Si no puede eliminarse, sugiere archivarla.
    """
    airline = get_object_or_404(Airline, pk=airline_id)

    # 1) Verificar si hay vuelos o aeronaves asociadas
    if Flight.objects.filter(airline=airline).exists() or Aircraft.objects.filter(aerolinea=airline).exists():
        return JsonResponse({
            'success': False,
            'archivable': True,
            'error': f'La aerolínea "{airline.name}" no puede eliminarse porque está asociada a vuelos o aeronaves.'
        })

    # 2) Intentar eliminar si no hay restricciones
    try:
        airline.delete()
        log_action(request.user, "Eliminó aerolínea", f"Aerolínea {airline.name} eliminada correctamente.")

        return JsonResponse({
            'success': True,
            'message': f'Aerolínea "{airline.name}" eliminada correctamente.'
        })

    except IntegrityError as e:
        log_action(request.user, "Error al eliminar aerolínea", f"No se pudo eliminar '{airline.name}': {str(e)}")
        return JsonResponse({'success': False, 'error': str(e)})

    except Exception as e:
        log_action(request.user, "Error inesperado al eliminar aerolínea", f"{airline.name}: {str(e)}")
        return JsonResponse({'success': False, 'error': str(e)})

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

@login_required
@permission_required('main.restore_airline', raise_exception=True)
def archived_airlines(request):
    """
    Lista aerolíneas archivadas
    """
    airlines = Airline.objects.filter(is_archived=True).order_by('name')
    return render(request, 'archived_airlines.html', {'airlines': airlines})
    
@require_POST
@login_required
@permission_required('main.delete_airline', raise_exception=True)
def archive_airline(request, airline_id):
    airline = get_object_or_404(Airline, pk=airline_id)

    # ❗ Ya no bloqueamos por vuelos o aeronaves asociadas — se permite archivar
    airline.is_archived = True
    airline.save()

    log_action(request.user, "Archivó aerolínea", f"Aerolínea {airline.name} archivada correctamente.")

    return JsonResponse({
        'success': True,
        'message': f'Aerolínea "{airline.name}" archivada correctamente.'
    })

@login_required
@permission_required('main.restore_airline', raise_exception=True)
def restore_airline(request, airline_id):
    airline = get_object_or_404(Airline, pk=airline_id, is_archived=True)

    # Chequeo de conflicto (por nombre, etc.):
    if Airline.objects.filter(name=airline.name, is_archived=False).exists():
        messages.error(
            request,
            f'No se puede restaurar. Ya existe otra aerolínea activa con el nombre "{airline.name}".'
        )
        return redirect('archived_airlines')

    airline.is_archived = False
    airline.save()
    log_action(request.user, 'Restauró aerolínea', airline)
    messages.success(request, f'La aerolínea "{airline.name}" ha sido restaurada.')
    return redirect('manage_airlines')

