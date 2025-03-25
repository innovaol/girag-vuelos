# models/audit_log.py
from django.db import models
from django.conf import settings
from django.utils.translation import gettext_lazy as _

class AuditLog(models.Model):
    user = models.ForeignKey(settings.AUTH_USER_MODEL, on_delete=models.SET_NULL, null=True, blank=True)
    action = models.CharField(max_length=255)
    details = models.TextField(blank=True, null=True)
    timestamp = models.DateTimeField(auto_now_add=True)

    class Meta:
        permissions = [
            ("view_audit_detail", _("Puede ver los detalles de la auditoría")),
        ]

    def __str__(self):
        user_display = self.user.username if self.user else "Desconocido"
        return f"{self.timestamp} - {user_display}: {self.action}"
