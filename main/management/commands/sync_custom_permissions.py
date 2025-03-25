# /home/innovaol/AppVuelos/main/management/commands/sync_custom_permissions.py
from django.core.management.base import BaseCommand
from django.contrib.auth.models import Permission
from django.contrib.contenttypes.models import ContentType
from django.contrib.auth.models import User, Group
from main.models import Flight, Airline, Aircraft, DocumentType, AuditLog

class Command(BaseCommand):
    help = "Sincroniza permisos personalizados en la base de datos"

    def handle(self, *args, **kwargs):
        permissions = [
            # Permisos especiales
            {"codename": "admin_vuelos", "name": "Permiso especial admin de vuelos", "model": Flight}, # Para revertir el estado de los vuelos de aprovado o facturado, a pendiente
            {"codename": "approve_flight", "name": "Permiso especial Aprobar vuelo", "model": Flight},
            {"codename": "mark_as_billed", "name": "Permiso especial Marcar vuelo como facturado", "model": Flight},
            
            # Dashboard
            {"codename": "access_dashboard", "name": "Acceso al Dashboard", "model": User},

            # Flights
            {"codename": "create_flight", "name": "Crear vuelo", "model": Flight},
            {"codename": "edit_flight", "name": "Editar vuelo", "model": Flight},
            {"codename": "view_flight", "name": "Ver vuelo", "model": Flight},
            {"codename": "delete_flight", "name": "Eliminar vuelo", "model": Flight},
            # {"codename": "change_status_flight", "name": "Cambiar estado de vuelo", "model": Flight},

            # Notificaciones
            {"codename": "view_notifications", "name": "Ver notificaciones", "model": Flight},

            # Airlines
            {"codename": "create_airline", "name": "Crear aerolínea", "model": Airline},
            {"codename": "edit_airline", "name": "Editar aerolínea", "model": Airline},
            {"codename": "delete_airline", "name": "Eliminar aerolínea", "model": Airline},
            {"codename": "view_airline", "name": "Ver aerolínea", "model": Airline},

            # Aircraft
            {"codename": "create_aircraft", "name": "Crear aeronave", "model": Aircraft},
            {"codename": "edit_aircraft", "name": "Editar aeronave", "model": Aircraft},
            {"codename": "delete_aircraft", "name": "Eliminar aeronave", "model": Aircraft},
            {"codename": "view_aircraft", "name": "Ver aeronave", "model": Aircraft},

            # Users
            {"codename": "change_password", "name": "Cambiar contraseña de usuario", "model": User},
            {"codename": "create_user", "name": "Crear usuario", "model": User},
            {"codename": "edit_user", "name": "Editar usuario", "model": User},
            {"codename": "delete_user", "name": "Eliminar usuario", "model": User},
            {"codename": "view_user", "name": "Ver usuario", "model": User},

            # Groups
            {"codename": "create_group", "name": "Crear grupo", "model": Group},
            {"codename": "edit_group", "name": "Editar grupo", "model": Group},
            {"codename": "delete_group", "name": "Eliminar grupo", "model": Group},
            {"codename": "view_group", "name": "Ver grupo", "model": Group},

            # Document Types
            {"codename": "create_document_type", "name": "Crear tipo de documento", "model": DocumentType},
            {"codename": "edit_document_type", "name": "Editar tipo de documento", "model": DocumentType},
            {"codename": "delete_document_type", "name": "Eliminar tipo de documento", "model": DocumentType},
            {"codename": "view_documenttype", "name": "Ver tipo de documento", "model": DocumentType},

            # Audit
            {"codename": "view_audit", "name": "Ver auditoría", "model": AuditLog},
            {"codename": "view_audit_detail", "name": "Ver detalle de auditoría", "model": AuditLog},

            # Settings
            {"codename": "access_settings", "name": "Acceder a configuración", "model": User},
        ]

        for perm in permissions:
            content_type = ContentType.objects.get_for_model(perm["model"])
            # Buscamos el permiso, si existe lo actualizamos, de lo contrario lo creamos
            permission, created = Permission.objects.get_or_create(
                codename=perm["codename"],
                content_type=content_type,
                defaults={"name": perm["name"]}
            )
            if created:
                self.stdout.write(self.style.SUCCESS(f'Permiso "{perm["name"]}" creado'))
            else:
                # Si el permiso existe pero su nombre es diferente, lo actualizamos
                if permission.name != perm["name"]:
                    permission.name = perm["name"]
                    permission.save()
                    self.stdout.write(self.style.SUCCESS(f'Permiso "{perm["codename"]}" actualizado a "{perm["name"]}"'))
                else:
                    self.stdout.write(self.style.WARNING(f'Permiso "{perm["name"]}" ya existe'))
