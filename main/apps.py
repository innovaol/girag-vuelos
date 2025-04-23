# /home/innovaol/girapp/main/apps.py

from django.apps import AppConfig
from django.db.models.signals import post_migrate
from django.contrib.auth.management import create_permissions

class MainConfig(AppConfig):
    default_auto_field = 'django.db.models.BigAutoField'
    name = 'main'

    def ready(self):
        # 🔕 Desconecta la creación automática de permisos por defecto
        post_migrate.disconnect(
            receiver=create_permissions,
            dispatch_uid='django.contrib.auth.management.create_permissions'
        )
