from django.shortcuts import render, get_object_or_404, redirect
from django.contrib.auth.decorators import login_required
from main.models.audit_log import AuditLog
from .auth_views import unauthorized

@login_required
def audit_logs(request):
    # Solo superusuarios (ajusta según tus necesidades)
    if not request.user.is_superuser:
        return redirect('unauthorized')
    logs = AuditLog.objects.all().order_by('-timestamp')
    return render(request, 'main/manage_audit.html', {'logs': logs})

@login_required
def audit_detail(request, log_id):
    if not request.user.is_superuser:
        return redirect('unauthorized')
    log = get_object_or_404(AuditLog, pk=log_id)
    return render(request, 'main/audit_detail.html', {'log': log})
