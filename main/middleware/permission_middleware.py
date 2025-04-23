import logging
from django.shortcuts import redirect
from django.urls import resolve, reverse
from django.utils.deprecation import MiddlewareMixin
from django.contrib.auth.models import Permission
from django.core.exceptions import PermissionDenied
import os
from django.conf import settings
from pathlib import Path

logger = logging.getLogger("permission_debug")

log_dir = Path(settings.BASE_DIR) / "logs"
log_dir.mkdir(parents=True, exist_ok=True)

handler = logging.FileHandler(log_dir / "permission_middleware.log", mode="a")
handler.setFormatter(logging.Formatter("%(asctime)s [%(levelname)s] %(message)s"))

logger.setLevel(logging.INFO)
logger.addHandler(handler)


# Diccionario de RUTAS → SET DE PERMISOS
ROUTE_PERMISSIONS = {
    # Dashboard
    "dashboard": {
        "main.access_dashboard",
    },

    # Vuelos (Flight)
    "manage_flights": {
        "main.view_flight",
        "main.create_flight",
        "main.edit_flight",
        "main.delete_flight",
    },
    "create_flight": {
        "main.create_flight",
    },
    "edit_flight": {
        "main.edit_flight",
    },
    "delete_flight": {
        "main.delete_flight",
    },
    "view_flight": {
        "main.view_flight",
    },

    # Aerolíneas
    "manage_airlines": {
        "main.view_airline",
        "main.create_airline",
        "main.edit_airline",
        "main.delete_airline",
    },
    "create_airline": {
        "main.create_airline",
    },
    "edit_airline": {
        "main.edit_airline",
    },
    "delete_airline": {
        "main.delete_airline",
    },

    # Aeronaves
    "manage_aircrafts": {
        "main.view_aircraft",
        "main.create_aircraft",
        "main.edit_aircraft",
        "main.delete_aircraft",
    },
    "create_aircraft": {
        "main.create_aircraft",
    },
    "edit_aircraft": {
        "main.edit_aircraft",
    },
    "delete_aircraft": {
        "main.delete_aircraft",
    },

    # Usuarios (User) => main.*
    "manage_users": {
        "main.view_user",
        "main.create_user",
        "main.edit_user",
        "main.delete_user",
    },
    "create_user": {
        "main.create_user",
    },
    "edit_user": {
        "main.edit_user",
    },
    "delete_user": {
        "main.delete_user",
    },
    "change_user_password": {
        "main.change_password",
    },

    # Grupos (Group) => main.*
    "manage_groups": {
        "auth.view_group",
        "auth.create_group",
        "auth.edit_group",
        "auth.delete_group",
    },
    "create_group": {
        "auth.create_group",
    },
    "edit_group": {
        "auth.edit_group",
    },
    "delete_group": {
        "auth.delete_group",
    },

    # Tipos de Documento
    "manage_document_types": {
        "main.view_documenttype",
        "main.create_document_type",
        "main.edit_document_type",
        "main.delete_document_type",
    },
    "create_document_type": {
        "main.create_document_type",
    },
    "edit_document_type": {
        "main.edit_document_type",
    },
    "delete_document_type": {
        "main.delete_documenttype",
    },

    # Auditoría
    "audit_logs": {
        "main.view_auditlog",
        "main.view_audit",
    },
    "audit_detail": {
        "main.view_audit_detail",  # Añadido este permiso
        "main.view_auditlog",
        "main.view_audit",  # Puedes mantener estos si también los necesitas
    },

    # Ajustes
    "manage_settings": {
        "main.access_settings",
    },
}

def get_user_permissions(user):
    """
    Retorna TODOS los permisos del usuario (directos + heredados de grupos).
    """
    if user.is_superuser:
        # El superusuario tiene todos los permisos que existan
        return {"ALL_PERMISSIONS"}  # 🔥 SUPERADMIN TIENE ACCESO TOTAL

    # Permisos directos
    user_perms_qs = Permission.objects.filter(user=user)
    user_permissions = {f"{p.content_type.app_label}.{p.codename}" for p in user_perms_qs}

    # Permisos de grupo
    # Usar groups_custom en lugar del campo 'groups' de Django
    for group in user.groups_custom.all():
        perms_qs = group.permissions.all()
        group_perms = {f"{p.content_type.app_label}.{p.codename}" for p in perms_qs}
        user_permissions |= group_perms

    return user_permissions

class PermissionMiddleware(MiddlewareMixin):
    def process_request(self, request):
        # Exenciones
        exempt_urls = {
            reverse('login'),
            reverse('logout'),
            reverse('unauthorized'),
        }
        if request.path.startswith('/static/') or request.path in exempt_urls:
            return None

        if not request.user.is_authenticated:
            return redirect('login')

        resolved_url = resolve(request.path)
        url_name = resolved_url.url_name
        if not url_name:
            return None  # no se protege

        if url_name not in ROUTE_PERMISSIONS:
            return None  # no se protege

        needed_permissions = ROUTE_PERMISSIONS[url_name]
        user_permissions = get_user_permissions(request.user)

        # Logs de depuración
        logger.info("==== DEBUG PERMISSIONS ====")
        logger.info(f"Usuario: {request.user.username}")
        logger.info(f"URL Name: {url_name}")
        logger.info(f"Permisos requeridos: {needed_permissions}")
        logger.info(f"Permisos del usuario: {user_permissions}")

        # Validar
        if "ALL_PERMISSIONS" in user_permissions or user_permissions & needed_permissions:
            logger.info("ACCESO PERMITIDO\n")
            return None

        logger.info("ACCESO DENEGADO => redirect('unauthorized')\n")
        return redirect('unauthorized')

    def process_exception(self, request, exception):
        if isinstance(exception, PermissionDenied):
            # Convertir 403 en unauthorized
            return redirect('unauthorized')
