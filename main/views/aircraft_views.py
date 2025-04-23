# /home/innovaol/girapp/main/views/aircraft_views.py

from django.shortcuts import render, get_object_or_404, redirect
from django.contrib import messages
from django.http import JsonResponse
from django.urls import reverse
from django.db import IntegrityError
from main.models.aircraft import Aircraft
from main.forms.aircraft_forms import AircraftForm
import json
import re
from django.contrib.auth.decorators import login_required, permission_required
from main.models.flight import Flight
from main.utils.audit import log_action
from django.views.decorators.http import require_POST

# Listar Aeronaves
def manage_aircrafts(request):
    # Muestra SOLO las aeronaves NO archivadas
    aircrafts = Aircraft.objects.filter(is_archived=False).order_by('aeronave')
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
@permission_required('main.delete_aircraft', login_url='unauthorized')
def delete_aircraft(request, pk):
    """
    Vista para eliminar una aeronave.
    Si no puede eliminarse, se archiva.
    """
    aircraft = get_object_or_404(Aircraft, pk=pk)

    # Verificar si hay vuelos asociados
    if Flight.objects.filter(aircraft=aircraft).exists():
        return JsonResponse({
            'success': False,
            'archivable': True,
            'error': f'La aeronave "{aircraft.aeronave}" no puede eliminarse porque está asociada a vuelos.'
        })

    try:
        aircraft_name = aircraft.aeronave
        aircraft.delete()
        log_action(request.user, "Eliminó aeronave", f"Aeronave {aircraft_name} eliminada correctamente.")

        messages.success(request, f'Aeronave "{aircraft_name}" eliminada correctamente.')

        return JsonResponse({
            'success': True,
            'message': f'Aeronave "{aircraft_name}" eliminada correctamente.',
            'redirect_url': reverse('manage_aircrafts')
        })

    except IntegrityError as e:
        log_action(request.user, "Error al eliminar aeronave", f"No se pudo eliminar {aircraft.aeronave}: {str(e)}")
        return JsonResponse({'success': False, 'error': str(e)})

    except Exception as e:
        log_action(request.user, "Error inesperado al eliminar aeronave", f"No se pudo eliminar {aircraft.aeronave}: {str(e)}")
        return JsonResponse({'success': False, 'error': f'Error inesperado: {str(e)}'})


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


@login_required
@permission_required('main.restore_aircraft', raise_exception=True)
def archived_aircrafts(request):
    """
    LISTA las aeronaves archivadas (no archiva ninguna)
    """
    aircrafts = Aircraft.all_objects.filter(is_archived=True).order_by('aeronave')
    return render(request, 'archived_aircrafts.html', {
        'aircrafts': aircrafts
    })



@require_POST
@login_required
@permission_required('main.delete_aircraft', raise_exception=True)
def archive_aircraft(request, pk):
    aircraft = get_object_or_404(Aircraft, pk=pk)

    # Eliminamos esta validación para permitir archivado incluso si hay vuelos
    # if Flight.objects.filter(aircraft=aircraft).exists():
    #     return JsonResponse({
    #         'success': False,
    #         'error': f'La aeronave \"{aircraft.aeronave}\" está asociada a vuelos y no puede archivarse.'
    #     })

    aircraft.is_archived = True
    aircraft.save()

    log_action(request.user, "Archivó aeronave", f"Aeronave {aircraft.aeronave} archivada correctamente.")

    return JsonResponse({
        'success': True,
        'message': f'Aeronave \"{aircraft.aeronave}\" archivada correctamente.'
    })



@login_required
@permission_required('main.restore_aircraft', raise_exception=True)
def restore_aircraft(request, pk):
    """
    Restaura UNA aeronave archivada si no hay conflictos.
    """
    # 1) Busca la aeronave con pk=pk y que esté archivada
    aircraft = get_object_or_404(Aircraft.all_objects, pk=pk, is_archived=True)

    # 2) Verifica si ya existe otra aeronave con el mismo nombre que NO esté archivada
    if Aircraft.objects.filter(aeronave=aircraft.aeronave, is_archived=False).exists():
        messages.error(
            request,
            f'No se puede restaurar. Ya existe otra aeronave activa con el nombre "{aircraft.aeronave}".'
        )
        # Redirige a la página donde listes aeronaves archivadas
        return redirect('archived_aircrafts')

    # 3) Marca la aeronave como activa (is_archived=False) y guarda
    aircraft.is_archived = False
    aircraft.save()

    # 4) Auditoría y mensaje
    log_action(request.user, 'Restauró aeronave', aircraft)
    messages.success(
        request,
        f'La aeronave "{aircraft.aeronave}" ha sido restaurada.'
    )

    # 5) Redirige al listado de aeronaves activas
    return redirect('archived_aircrafts')
