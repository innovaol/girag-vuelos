from main.models.flight import Flight

def notifications_processor(request):
    """
    Inyecta en el contexto la información de notificaciones de vuelos según el rol del usuario:

    1. Supervisores:
       - Pueden ver la tabla de vuelos pendientes de aprobación y aprobar vuelos.
       - El contador global mostrará únicamente la cantidad de vuelos pendientes de aprobación.
       - Además, pueden ver la tabla de vuelos por facturar (en modo solo lectura).

    2. Facturadores:
       - Verán únicamente la tabla de vuelos pendientes de facturar.
       - El contador global mostrará únicamente la cantidad de vuelos pendientes de facturación.

    3. Usuarios con ambos roles:
       - Verán ambas tablas y podrán realizar las acciones correspondientes en cada una.
       - El contador global será la suma de ambos conjuntos.
    """
    if not request.user.is_authenticated:
        return {}

    # Convertir a booleano para estar seguros
    is_supervisor = bool(getattr(request.user, 'is_flight_supervisor', False))
    is_billing = bool(getattr(request.user, 'is_billing_supervisor', False))

    # Siempre obtener ambos conjuntos para mostrarlos en la interfaz:
    pending_approval = Flight.objects.filter(status='pending')
    pending_billing = Flight.objects.filter(status='approved')

    # Calcular los contadores de cada conjunto:
    pending_approval_count = pending_approval.count()
    pending_billing_count = pending_billing.count()

    # El contador global en el menú depende del rol que "actúa":
    # - Supervisores: solo cuentan los vuelos pendientes de aprobación.
    # - Facturadores: solo cuentan los vuelos pendientes de facturación.
    # - Ambos: se suman ambos.
    if is_supervisor and not is_billing:
        notifications_count = pending_approval_count
    elif is_billing and not is_supervisor:
        notifications_count = pending_billing_count
    elif is_supervisor and is_billing:
        notifications_count = pending_approval_count + pending_billing_count
    else:
        notifications_count = 0

    return {
        'pending_approval': pending_approval,      # Para mostrar la tabla de aprobaciones
        'pending_billing': pending_billing,          # Para mostrar la tabla de facturación
        'notifications_count': notifications_count,  # Para el badge del menú
    }
