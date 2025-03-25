from django.shortcuts import render, get_object_or_404, redirect
from django.contrib.auth.decorators import permission_required
from main.models.audit_log import AuditLog
from .auth_views import unauthorized

@permission_required('main.view_audit', login_url='unauthorized')
def audit_logs(request):
    """
    Vista para listar los registros de auditoría, usando 'main.view_audit'.
    """
    logs = AuditLog.objects.all().order_by('-timestamp')
    return render(request, 'manage_audit.html', {'logs': logs})

@permission_required('main.view_audit_detail', login_url='unauthorized')
def audit_detail(request, log_id):
    """
    Vista para ver los detalles de un registro de auditoría, usando 'main.view_audit_detail'.
    """
    log = get_object_or_404(AuditLog, pk=log_id)
    return render(request, 'audit_detail.html', {'log': log})
