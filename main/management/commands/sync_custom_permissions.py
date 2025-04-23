# /home/innovaol/girapp/main/management/commands/sync_custom_permissions.py

from django.core.management.base import BaseCommand
from django.contrib.auth.models import Permission
from main.models.custom_group import CustomGroup as Group
from django.contrib.contenttypes.models import ContentType
from django.contrib.auth import get_user_model
from main.models import Flight, Airline, Aircraft, DocumentType, AuditLog

User = get_user_model()


class Command(BaseCommand):
    help = "Sincroniza permisos personalizados en la base de datos"

    def handle(self, *args, **kwargs):
        permissions = [
            # Permisos especiales
            {"codename": "admin_vuelos", "name": "Permiso especial admin de vuelos", "model": Flight},
            {"codename": "approve_flight", "name": "Permiso especial Aprobar vuelo", "model": Flight},
            {"codename": "mark_as_billed", "name": "Permiso especial Marcar vuelo como facturado", "model": Flight},

            # Dashboard
            {"codename": "access_dashboard", "name": "Acceso al Dashboard", "model": User},

            # Flights (No archivables)
            {"codename": "create_flight", "name": "Crear vuelo", "model": Flight},
            {"codename": "edit_flight", "name": "Editar vuelo", "model": Flight},
            {"codename": "view_flight", "name": "Ver vuelo", "model": Flight},
            {"codename": "delete_flight", "name": "Eliminar vuelo", "model": Flight},
            {"codename": "view_notifications", "name": "Ver notificaciones", "model": Flight},

            # Airlines
            {"codename": "create_airline", "name": "Crear aerolínea", "model": Airline},
            {"codename": "edit_airline", "name": "Editar aerolínea", "model": Airline},
            {"codename": "view_airline", "name": "Ver aerolínea", "model": Airline},
            {"codename": "delete_airline", "name": "Eliminar aerolínea", "model": Airline},
            {"codename": "restore_airline", "name": "Restaurar aerolínea", "model": Airline},

            # Aircraft
            {"codename": "create_aircraft", "name": "Crear aeronave", "model": Aircraft},
            {"codename": "edit_aircraft", "name": "Editar aeronave", "model": Aircraft},
            {"codename": "view_aircraft", "name": "Ver aeronave", "model": Aircraft},
            {"codename": "delete_aircraft", "name": "Eliminar aeronave", "model": Aircraft},
            {"codename": "restore_aircraft", "name": "Restaurar aeronave", "model": Aircraft},

            # Users
            {"codename": "change_password", "name": "Cambiar contraseña de usuario", "model": User},
            {"codename": "create_user", "name": "Crear usuario", "model": User},
            {"codename": "edit_user", "name": "Editar usuario", "model": User},
            {"codename": "view_user", "name": "Ver usuario", "model": User},
            {"codename": "delete_user", "name": "Eliminar usuario", "model": User},
            {"codename": "restore_user", "name": "Restaurar usuario", "model": User},

            # Groups
            {"codename": "create_group", "name": "Crear grupo", "model": Group},
            {"codename": "edit_group", "name": "Editar grupo", "model": Group},
            {"codename": "view_group", "name": "Ver grupo", "model": Group},
            {"codename": "delete_group", "name": "Eliminar grupo", "model": Group},
            {"codename": "restore_group", "name": "Restaurar grupo", "model": Group},

            # Document Types
            {"codename": "create_document_type", "name": "Crear tipo de documento", "model": DocumentType},
            {"codename": "edit_document_type", "name": "Editar tipo de documento", "model": DocumentType},
            {"codename": "view_documenttype", "name": "Ver tipo de documento", "model": DocumentType},
            {"codename": "delete_document_type", "name": "Eliminar tipo de documento", "model": DocumentType},
            {"codename": "restore_document_type", "name": "Restaurar tipo de documento", "model": DocumentType},

            # Audit
            {"codename": "view_audit", "name": "Ver auditoría", "model": AuditLog},
            {"codename": "view_audit_detail", "name": "Ver detalle de auditoría", "model": AuditLog},

            # Settings
            {"codename": "access_settings", "name": "Acceder a configuración", "model": User},
        ]

        # Obtener permisos existentes para comparar
        existing_permissions = Permission.objects.all().values_list("codename", "content_type_id", "id")
        existing_perms_dict = {f"{codename}_{content_type_id}": perm_id for codename, content_type_id, perm_id in existing_permissions}

        # Crear o actualizar permisos
        for perm in permissions:
            content_type = ContentType.objects.get_for_model(perm["model"])
            perm_key = f"{perm['codename']}_{content_type.id}"

            # Verificar si el permiso ya existe pero con un content_type diferente
            possible_duplicates = Permission.objects.filter(
                codename=perm["codename"]
            ).exclude(content_type=content_type)

            if possible_duplicates.exists():
                for duplicate in possible_duplicates:
                    # Si el content_type cambió, actualiza el existente para mantener permisos
                    if duplicate.name == perm["name"]:
                        duplicate.content_type = content_type
                        duplicate.save()
                        self.stdout.write(self.style.WARNING(
                            f"⚠️ Permiso '{perm['name']}' actualizado para cambiar de content_type."
                        ))
                    else:
                        duplicate.delete()
                        self.stdout.write(self.style.WARNING(
                            f"❌ Permiso '{perm['name']}' duplicado eliminado."
                        ))

            # Crear o actualizar permisos (mantener permisos si el content_type cambió)
            permission, created = Permission.objects.update_or_create(
                codename=perm["codename"],
                content_type=content_type,
                defaults={"name": perm["name"]},
            )

            if created:
                self.stdout.write(self.style.SUCCESS(f"✅ Permiso '{perm['name']}' creado."))
            else:
                self.stdout.write(self.style.WARNING(f"⚠️ Permiso '{perm['name']}' ya existe y está actualizado."))

            # Eliminar de existing_perms_dict si fue actualizado
            if perm_key in existing_perms_dict:
                del existing_perms_dict[perm_key]

        # Eliminar permisos obsoletos si no están en la lista actual
        if existing_perms_dict:
            Permission.objects.filter(id__in=existing_perms_dict.values()).delete()
            self.stdout.write(self.style.WARNING(f"❌ {len(existing_perms_dict)} permisos obsoletos eliminados."))

        self.stdout.write(self.style.SUCCESS("✅ Permisos sincronizados correctamente."))
