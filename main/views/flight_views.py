from django.shortcuts import render, redirect, get_object_or_404
from django.contrib.auth.decorators import login_required
from django.contrib import messages
from datetime import date, timedelta
from django.db.models import Count
from django.db.models.functions import TruncDay
from django.utils.dateformat import format  # Import para formatear fechas

from main.models.flight import Flight
from main.models.document import Document
from main.models.document_type import DocumentType
from main.forms.flight_forms import FlightForm, FlightReportForm
from main.utils.audit import log_action  # Función de auditoría

@login_required
def dashboard(request):
    """
    Vista del dashboard: muestra vuelos filtrados por fechas y estadísticas basadas en la fecha del vuelo.
    El gráfico muestra la cantidad de vuelos ocurridos en los últimos 7 días.
    """
    form = FlightReportForm(request.GET or None)
    flights = Flight.objects.all()
    if form.is_valid():
        start = form.cleaned_data.get('start_date')
        end = form.cleaned_data.get('end_date')
        if start:
            flights = flights.filter(date__gte=start)
        if end:
            flights = flights.filter(date__lte=end)

    total_flights = Flight.objects.count()
    today = date.today()
    flights_today = Flight.objects.filter(date=today).count()
    last_7_days = today - timedelta(days=7)
    flights_last_7 = Flight.objects.filter(date__gte=last_7_days).count()

    flights_by_day = (
        Flight.objects.filter(date__gte=last_7_days)
        .annotate(day=TruncDay('date'))
        .values('day')
        .annotate(count=Count('id'))
        .order_by('day')
    )

    chart_labels = [format(entry['day'], "d/m/Y") for entry in flights_by_day]  # Formateo de fecha
    chart_data = [entry['count'] for entry in flights_by_day]

    context = {
        'form': form,
        'flights': flights,
        'total_flights': total_flights,
        'flights_today': flights_today,
        'flights_last_7': flights_last_7,
        'chart_labels': chart_labels,
        'chart_data': chart_data,
    }
    return render(request, 'main/dashboard.html', context)

@login_required
def manage_flights(request):
    """
    Vista para listar y gestionar vuelos.
    """
    flights = Flight.objects.all().order_by('date')
    
    # Formatear las fechas antes de enviarlas a la plantilla
    for flight in flights:
        flight.date = format(flight.date, "d/m/Y")

    return render(request, 'main/manage_flights.html', {'flights': flights})

@login_required
def create_flight(request):
    """
    Vista para crear un vuelo y procesar la subida de documentos.
    """
    if request.method == 'POST':
        form = FlightForm(request.POST)
        if form.is_valid():
            flight = form.save(commit=False)
            flight.created_by = request.user
            flight.save()

            # Procesar archivos subidos
            for key in request.FILES:
                if key.startswith('file_'):
                    file_uploaded = request.FILES[key]
                    doc_type_id = request.POST.get(f"{key}_type")
                    if not doc_type_id:
                        messages.error(request, "Debe seleccionar un tipo de documento para cada archivo subido.")
                        flight.delete()  # Borrar vuelo en caso de error
                        return render(request, 'main/flight_create.html', {
                            'form': form,
                            'document_types': DocumentType.objects.all()
                        })
                    try:
                        doc_type = DocumentType.objects.get(pk=doc_type_id)
                    except DocumentType.DoesNotExist:
                        messages.error(request, "Tipo de documento inválido.")
                        flight.delete()
                        return render(request, 'main/flight_create.html', {
                            'form': form,
                            'document_types': DocumentType.objects.all()
                        })
                    Document.objects.create(
                        flight=flight,
                        file=file_uploaded,
                        doc_type=doc_type.name
                    )

            messages.success(request, '¡Vuelo creado y documentos subidos!')
            log_action(request.user, "Creó un vuelo", f"Vuelo {flight.flight_number} creado.")
            return redirect('manage_flights')

    else:
        form = FlightForm()

    return render(request, 'main/flight_create.html', {
        'form': form,
        'document_types': DocumentType.objects.all()
    })

@login_required
def edit_flight(request, flight_id):
    """
    Vista para editar un vuelo existente.
    """
    flight = get_object_or_404(Flight, pk=flight_id)
    documents = flight.documents.all()
    
    if request.method == 'POST':
        form = FlightForm(request.POST, instance=flight)
        if form.is_valid():
            form.save()

            # Procesar documentos marcados para eliminación
            removed_docs = request.POST.get('removed_documents', '')
            if removed_docs:
                doc_ids = [int(doc_id) for doc_id in removed_docs.split(',') if doc_id.isdigit()]
                for doc_id in doc_ids:
                    try:
                        doc = flight.documents.get(pk=doc_id)
                        doc.delete()
                    except Document.DoesNotExist:
                        pass

            # Procesar nuevos archivos subidos
            for key in request.FILES:
                if key.startswith('file_'):
                    file_uploaded = request.FILES[key]
                    doc_type_id = request.POST.get(f"{key}_type")
                    if not doc_type_id:
                        messages.error(request, "Debe seleccionar un tipo de documento para cada archivo subido.")
                        return render(request, 'main/flight_edit.html', {
                            'form': form,
                            'flight': flight,
                            'documents': flight.documents.all(),
                            'document_types': DocumentType.objects.all()
                        })
                    try:
                        doc_type = DocumentType.objects.get(pk=doc_type_id)
                    except DocumentType.DoesNotExist:
                        messages.error(request, "Tipo de documento inválido.")
                        return render(request, 'main/flight_edit.html', {
                            'form': form,
                            'flight': flight,
                            'documents': flight.documents.all(),
                            'document_types': DocumentType.objects.all()
                        })
                    Document.objects.create(
                        flight=flight,
                        file=file_uploaded,
                        doc_type=doc_type.name
                    )

            messages.success(request, "Vuelo actualizado correctamente.")
            log_action(request.user, "Editó un vuelo", f"Vuelo {flight.flight_number} editado.")
            return redirect('manage_flights')

    else:
        form = FlightForm(instance=flight)

    return render(request, 'main/flight_edit.html', {
        'form': form,
        'flight': flight,
        'documents': documents,
        'document_types': DocumentType.objects.all()
    })

@login_required
def delete_flight(request, flight_id):
    """
    Vista para eliminar un vuelo.
    """
    flight = get_object_or_404(Flight, pk=flight_id)
    flight_number = flight.flight_number
    flight.delete()
    messages.success(request, "Vuelo eliminado correctamente.")
    log_action(request.user, "Eliminó un vuelo", f"Vuelo {flight_number} eliminado.")
    return redirect('manage_flights')

@login_required
def flight_detail(request, flight_id):
    """
    Vista de detalle de un vuelo.
    """
    flight = get_object_or_404(Flight, pk=flight_id)
    documents = flight.documents.all()
    
    # Formatear la fecha antes de enviarla a la plantilla
    flight.date = format(flight.date, "d/m/Y")

    return render(request, 'main/flight_detail.html', {'flight': flight, 'documents': documents})
