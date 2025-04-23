# /home/innovaol/girapp/main/models/custom_group.py

from django.db import models
from django.contrib.auth.models import Permission
from main.models.archivable import ArchivableModel

class CustomGroup(ArchivableModel):
    name = models.CharField(max_length=150, unique=True)
    permissions = models.ManyToManyField(
        Permission,
        blank=True,
        help_text="Permisos asignados a este grupo."
    )

    def __str__(self):
        return self.name
        
    class Meta:
        default_permissions = ()  # 🔒 Desactiva los permisos automáticos de Django
