from django.shortcuts import render, redirect, get_object_or_404
from django.contrib.auth.decorators import login_required
from django.contrib import messages
from main.models.airline import Airline
from main.models.flight import Flight
from main.forms.airline_forms import AirlineForm
from main.utils.audit import log_action

@login_required
def manage_airlines(request):
    if not request.user.is_superuser:
        return redirect('unauthorized')
    airlines = Airline.objects.all().order_by('name')
    return render(request, 'main/manage_airlines.html', {'airlines': airlines})

@login_required
def create_airline(request):
    if not request.user.is_superuser:
        return redirect('unauthorized')
    if request.method == 'POST':
        form = AirlineForm(request.POST)
        if form.is_valid():
            airline = form.save()
            messages.success(request, 'Aerolínea creada correctamente.')
            log_action(request.user, "Creó una aerolínea", f"Aerolínea {airline.name} creada.")
            return redirect('manage_airlines')
    else:
        form = AirlineForm()
    return render(request, 'main/create_airline.html', {'form': form})

@login_required
def edit_airline(request, airline_id):
    if not request.user.is_superuser:
        return redirect('unauthorized')
    airline = get_object_or_404(Airline, pk=airline_id)
    if request.method == 'POST':
        form = AirlineForm(request.POST, instance=airline)
        if form.is_valid():
            form.save()
            messages.success(request, 'Aerolínea actualizada correctamente.')
            log_action(request.user, "Editó una aerolínea", f"Aerolínea {airline.name} actualizada.")
            return redirect('manage_airlines')
    else:
        form = AirlineForm(instance=airline)
    return render(request, 'main/edit_airline.html', {'form': form, 'airline': airline})

@login_required
def delete_airline(request, airline_id):
    if not request.user.is_superuser:
        return redirect('unauthorized')
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
