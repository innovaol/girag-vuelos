# /home/innovaol/AppVuelos/main/views/notification_views.py

from django.shortcuts import render, redirect, get_object_or_404
from django.contrib.auth.decorators import login_required, permission_required
from django.contrib import messages
from django.urls import reverse
from main.models.flight import Flight
from django.core.mail import send_mail  # Asegúrate de tener esta importación
from django.conf import settings       # Para usar settings.SITE_URL, etc.
import logging

logger = logging.getLogger(__name__)

def log_action(user, action, message):
    logger.info(f"{user.username} - {action}: {message}")

@login_required
@permission_required('main.view_notifications', raise_exception=True)
def notification_view(request):
    """
    Muestra las notificaciones de vuelos:
      - Si el usuario es solo supervisor, se muestran ambos conjuntos: 
          vuelos pendientes de aprobación y vuelos pendientes de facturación (en solo lectura),
          pero el badge solo muestra la cantidad de vuelos pendientes de aprobación.
      - Si es solo facturador, se muestran únicamente los vuelos pendientes de facturación.
      - Si tiene ambos roles, se muestran ambos conjuntos y el badge es la suma de ambos.
    """
    if not request.user.is_authenticated:
        pending_approval = Flight.objects.none()
        pending_billing = Flight.objects.none()
        notifications_count = 0
    else:
        is_supervisor = bool(getattr(request.user, 'is_flight_supervisor', False))
        is_billing = bool(getattr(request.user, 'is_billing_supervisor', False))
        
        if is_supervisor and not is_billing:
            # Supervisor único: se muestran ambas tablas, pero el badge solo con pendientes de aprobación.
            pending_approval = Flight.objects.filter(status='pending')
            pending_billing = Flight.objects.filter(status='approved')
            notifications_count = pending_approval.count()
        elif is_billing and not is_supervisor:
            # Facturador único
            pending_approval = Flight.objects.none()
            pending_billing = Flight.objects.filter(status='approved')
            notifications_count = pending_billing.count()
        elif is_supervisor and is_billing:
            # Ambos roles: el badge es la suma de ambos.
            pending_approval = Flight.objects.filter(status='pending')
            pending_billing = Flight.objects.filter(status='approved')
            notifications_count = pending_approval.count() + pending_billing.count()
        else:
            pending_approval = Flight.objects.none()
            pending_billing = Flight.objects.none()
            notifications_count = 0
    
    return render(request, 'notification_list.html', {
        'pending_approval': pending_approval,
        'pending_billing': pending_billing,
        'notifications_count': notifications_count,
    })

def send_approval_email(flight):
    """
    Envía un correo a todos los supervisores notificándoles que se ha creado un nuevo vuelo pendiente.
    """
    from django.contrib.auth import get_user_model
    User = get_user_model()
    supervisors = User.objects.filter(is_flight_supervisor=True)
    subject = f"Nuevo vuelo pendiente de aprobación: {flight.flight_number}"
    approval_url = f"{settings.SITE_URL}/flights/{flight.id}/"
    message = (
        f"Se ha creado un nuevo vuelo pendiente de aprobación.\n\n"
        f"Número de vuelo: {flight.flight_number}\n"
        f"Fecha: {flight.date}\n\n"
        f"Revisa el vuelo aquí: {approval_url}"
    )
    recipient_list = [user.email for user in supervisors if user.email]
    send_mail(subject, message, settings.DEFAULT_FROM_EMAIL, recipient_list)

@login_required
def approve_flight(request, flight_id):
    """
    Aprueba un vuelo (cambia su estado a 'approved') y envía notificación al usuario de facturación.
    Si el usuario aprobador tiene el rol de facturador, se asigna como responsable.
    """
    flight = get_object_or_404(Flight, pk=flight_id)
    flight.status = 'approved'
    if request.user.is_billing_supervisor:
        flight.billing_user = request.user
    flight.save()

    send_billing_notification(flight)

    messages.success(request, f"El vuelo {flight.flight_number} ha sido aprobado correctamente.")
    return redirect('notification_view')

def send_billing_notification(flight):
    subject = f"Nuevo vuelo aprobado: {flight.flight_number}"
    billing_url = f"{settings.SITE_URL}/flights/{flight.id}/"
    message = (
        f"El vuelo con número {flight.flight_number} ha sido aprobado.\n\n"
        f"Revisa el vuelo aquí: {billing_url}\n\n"
        f"Por favor, procede con la facturación."
    )
    from django.contrib.auth import get_user_model
    User = get_user_model()
    # Obtener todos los usuarios que tengan el permiso de facturador de vuelos
    billing_supervisors = User.objects.filter(is_billing_supervisor=True)
    recipient_list = [user.email for user in billing_supervisors if user.email]
    
    if recipient_list:
        send_mail(subject, message, settings.DEFAULT_FROM_EMAIL, recipient_list)
    else:
        print("No hay usuarios con permisos de facturador asignados o sin email.")

@login_required
@permission_required('main.view_flight', raise_exception=True)
def mark_as_billed(request, flight_id):
    """
    Marca un vuelo como facturado y notifica a los supervisores.
    Solo puede ser ejecutado por el usuario de facturación.
    Al finalizar, redirige a la vista de notificaciones con "?next=approved" para que
    el botón "Volver" en el detalle del vuelo regrese a la sección correcta.
    """
    flight = get_object_or_404(Flight, pk=flight_id)
    
    # Validar que solo el usuario facturador pueda ejecutar esta acción.
    if not request.user.is_billing_supervisor:
        messages.error(request, "No tienes permisos para facturar vuelos.")
        return redirect('manage_flights')
    
    # Solo facturar si el vuelo está en estado 'approved'
    if flight.status != 'approved':
        messages.error(request, "El vuelo no se encuentra en estado aprobado para facturación.")
        return redirect('notification_view')
    
    flight.status = 'billed'
    flight.save()
    
    # Enviar notificación de completado
    send_completion_notification(flight)
    
    log_action(request.user, "Marcó vuelo como facturado", f"Vuelo {flight.flight_number} marcado como facturado.")
    messages.success(request, f"El vuelo {flight.flight_number} ha sido marcado como facturado.")
    
    # Redirige a la vista de notificaciones con ?next=approved para que el detalle sepa dónde volver.
    return redirect(reverse('notification_view') + "?next=approved")

def send_completion_notification(flight):
    """
    Envía un correo a los supervisores cuando el vuelo se marca como facturado.
    """
    subject = f"Vuelo facturado: {flight.flight_number}"
    message = f"El vuelo con número {flight.flight_number} ha sido facturado y completado."
    from django.contrib.auth import get_user_model
    User = get_user_model()
    supervisors = User.objects.filter(is_flight_supervisor=True)
    recipient_list = [user.email for user in supervisors if user.email]
    send_mail(subject, message, settings.DEFAULT_FROM_EMAIL, recipient_list)
