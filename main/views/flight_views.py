# /home/innovaol/AppVuelos/main/views/flight_views.py

import os
import json
import re
from datetime import date, timedelta

from django.shortcuts import render, redirect, get_object_or_404
from django.contrib.auth.decorators import login_required, permission_required
from django.contrib import messages
from django.http import JsonResponse
from django.db.models import Count
from django.db.models.functions import TruncDay
from django.utils.dateformat import format
from django.urls import reverse
from django.core.exceptions import PermissionDenied

from main.models.flight import Flight
from main.models.document import Document
from main.models.document_type import DocumentType
from main.forms.flight_forms import FlightForm, FlightReportForm
from main.utils.audit import log_action


@login_required
def dashboard(request):
    """
    Vista del dashboard: muestra vuelos filtrados por fechas y estadísticas basadas en la fecha del vuelo.
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

    chart_labels = [format(entry['day'], "d/m/Y") for entry in flights_by_day]
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
    return render(request, 'dashboard.html', context)


@permission_required('main.view_flight', raise_exception=True)
def manage_flights(request):
    """
    Vista para listar y gestionar vuelos.
    """
    flights = Flight.objects.all().order_by('-date')
    if not flights.exists():
        messages.info(request, "No hay vuelos registrados.")
    return render(request, 'manage_flights.html', {'flights': flights})

@login_required
def flight_form(request, flight_id=None):
    """
    Vista unificada para crear y editar vuelos.
    - Si flight_id es None, se crea un nuevo vuelo.
    - Si tiene valor, se edita el vuelo correspondiente.
    """
    # Validación interna de permisos:
    if flight_id:
        if not request.user.has_perm('main.edit_flight'):
            raise PermissionDenied("No tienes permiso para editar vuelos.")
    else:
        if not request.user.has_perm('main.create_flight'):
            raise PermissionDenied("No tienes permiso para crear vuelos.")

    if flight_id:
        flight = get_object_or_404(Flight, pk=flight_id)
        if flight.status != 'pending':  # ← ADDED
            raise PermissionDenied("Solo los vuelos en estado Pendiente pueden editarse.")  # ← ADDED
        title = "Editar Vuelo"
        edit_mode = True
        documents = flight.documents.all()
    else:
        flight = None
        title = "Crear Vuelo"
        edit_mode = False
        documents = None

    if request.method == 'POST':
        form = FlightForm(request.POST, instance=flight)
        if form.is_valid():
            flight = form.save(commit=False)
            if not flight_id:
                flight.created_by = request.user
                flight.created_at = date.today()
            flight.save()

            if not flight_id:
                from main.views.notification_views import send_approval_email
                send_approval_email(flight)

            allowed_extensions = ['.txt', '.pdf', '.doc', '.docx', '.xls', '.xlsx', '.jpg', '.jpeg', '.png', '.gif']
            removed_docs = request.POST.get('removed_documents', '')
            if removed_docs:
                doc_ids = [int(doc_id) for doc_id in removed_docs.split(',') if doc_id.isdigit()]
                for doc_id in doc_ids:
                    try:
                        flight.documents.get(pk=doc_id).delete()
                    except Document.DoesNotExist:
                        pass

            for key in request.FILES:
                if key.startswith('file_'):
                    file_uploaded = request.FILES[key]
                    ext = os.path.splitext(file_uploaded.name)[1].lower()
                    if ext not in allowed_extensions:
                        messages.error(request, "Solo se permiten archivos de tipo: txt, excel, word y pdf.")
                        if not flight_id:
                            flight.delete()
                        return render(request, 'flight_form.html', {
                            'form': form,
                            'document_types': DocumentType.objects.all(),
                            'edit_mode': edit_mode,
                            'flight': flight,
                            'documents': documents
                        })
                    doc_type_id = request.POST.get(f"{key}_type")
                    if not doc_type_id:
                        messages.error(request, "Debe seleccionar un tipo de documento para cada archivo subido.")
                        if not flight_id:
                            flight.delete()
                        return render(request, 'flight_form.html', {
                            'form': form,
                            'document_types': DocumentType.objects.all(),
                            'edit_mode': edit_mode,
                            'flight': flight,
                            'documents': documents
                        })
                    try:
                        doc_type = DocumentType.objects.get(pk=doc_type_id)
                    except DocumentType.DoesNotExist:
                        messages.error(request, "Tipo de documento inválido.")
                        if not flight_id:
                            flight.delete()
                        return render(request, 'flight_form.html', {
                            'form': form,
                            'document_types': DocumentType.objects.all(),
                            'edit_mode': edit_mode,
                            'flight': flight,
                            'documents': documents
                        })
                    Document.objects.create(
                        flight=flight,
                        file=file_uploaded,
                        doc_type=doc_type
                    )

            if flight_id:
                messages.success(request, "Vuelo actualizado correctamente.")
                log_action(request.user, "Editó un vuelo", f"Vuelo {flight.flight_number} editado.")
            else:
                messages.success(request, "¡Vuelo creado y documentos subidos!")
                log_action(request.user, "Creó un vuelo", f"Vuelo {flight.flight_number} creado.")

            return redirect('manage_flights')
    else:
        form = FlightForm(instance=flight)

    return render(request, 'flight_form.html', {
        'form': form,
        'document_types': DocumentType.objects.all(),
        'edit_mode': edit_mode,
        'flight': flight,
        'documents': documents
    })

@permission_required('main.delete_flight', raise_exception=True)
def delete_flight(request, flight_id):
    """
    Vista para eliminar un vuelo.
    Solo permite eliminar vuelos en estado 'pending'.
    """
    flight = get_object_or_404(Flight, pk=flight_id)

    if flight.status != 'pending':
        messages.error(request, "Solo se pueden eliminar vuelos en estado pendiente.")
        return redirect('manage_flights')

    flight_number = flight.flight_number
    flight.delete()
    messages.success(request, f"Vuelo {flight_number} eliminado correctamente.")
    log_action(request.user, "Eliminó un vuelo", f"Vuelo {flight_number} eliminado.")

    return redirect('manage_flights')

@permission_required('main.view_flight', raise_exception=True)
def flight_detail(request, flight_id):
    print(f"🟢 Entrando a flight_detail con id={flight_id}")
    flight = get_object_or_404(Flight, pk=flight_id)
    documents = flight.documents.all()

    # ✅ No sobrescribimos flight.date, solo formateamos para mostrar
    formatted_date = format(flight.date, "d/m/Y")

    prev_url = request.META.get('HTTP_REFERER', '')
    next_url = prev_url if 'notification' in prev_url else reverse('manage_flights')

    context = {
        'flight': flight,
        'documents': documents,
        'formatted_date': formatted_date,
        'next_url': next_url,
    }
    return render(request, 'flight_detail.html', context)


def check_flight_number(request):
    """Valida si el número de vuelo ya existe en la base de datos."""
    if request.method == "POST":
        try:
            data = json.loads(request.body.decode("utf-8"))
            flight_number = data.get("flight_number", "").strip()
            if not flight_number:
                return JsonResponse({"valid": False, "error": "El número de vuelo no puede estar vacío."})
            if Flight.objects.filter(flight_number__iexact=flight_number).exists():
                return JsonResponse({"valid": False, "error": "El número de vuelo ya existe"})
            return JsonResponse({"valid": True, "success": "✔ Número de vuelo disponible."})
        except json.JSONDecodeError as e:
            return JsonResponse({"valid": False, "error": f"⚠️ Error: {str(e)}"}, status=400)
    return JsonResponse({"valid": False, "error": "⚠️ Método no permitido."}, status=405)


@permission_required('main.admin_vuelos', raise_exception=True)
def revert_flight_to_pending(request, flight_id):
    flight = get_object_or_404(Flight, pk=flight_id)
    if flight.status == 'pending':
        messages.info(request, "El vuelo ya se encuentra en estado pendiente.")
    else:
        flight.status = 'pending'
        flight.save()
        log_action(request.user, "Revirtió vuelo a pendiente", f"Vuelo {flight.flight_number} revertido a pendiente.")
        messages.success(request, f"El vuelo {flight.flight_number} ha sido revertido a estado pendiente.")
    
    # Redirigir a la URL desde la que vino, si existe, o a 'manage_flights'
    next_url = request.GET.get('next') or request.META.get('HTTP_REFERER')
    if next_url:
        return redirect(next_url)
    return redirect('manage_flights')
