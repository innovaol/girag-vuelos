# /home/innovaol/girapp/main/backends/custom_backend.py

from django.contrib.auth.backends import BaseBackend
from django.contrib.auth.models import Permission
from django.db.models import Q

class CustomGroupBackend(BaseBackend):
    def authenticate(self, request, username=None, password=None, **kwargs):
        return None  # Solo manejamos permisos, no autenticación

    def get_user_permissions(self, user_obj, obj=None):
        return set(user_obj.user_permissions.values_list("codename", flat=True))

    def get_group_permissions(self, user_obj, obj=None):
        if not hasattr(user_obj, 'groups_custom'):
            return set()

        perms = Permission.objects.filter(
            Q(customgroup__in=user_obj.groups_custom.all())
        ).values_list('content_type__app_label', 'codename')

        return set(f"{ct}.{name}" for ct, name in perms)

    def get_all_permissions(self, user_obj, obj=None):
        if user_obj.is_anonymous:
            return set()
        return self.get_user_permissions(user_obj, obj) | self.get_group_permissions(user_obj, obj)

    def has_perm(self, user_obj, perm, obj=None):
        if user_obj.is_superuser:
            return True
        return perm in self.get_all_permissions(user_obj, obj)
