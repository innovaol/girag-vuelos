# /home/innovaol/girapp/main/models/custom_user.py

from django.contrib.auth.models import AbstractUser
from django.db import models
from main.models.archivable import ArchivableModel
from main.models.custom_group import CustomGroup
from django.contrib.auth.models import Group

class CustomUser(ArchivableModel, AbstractUser):
    groups = models.ManyToManyField(
        Group,
        related_name="customuser_set",
        blank=True,
        help_text="(No usar) Campo requerido por Django internamente para permisos."
    )

    
    is_flight_supervisor = models.BooleanField(default=False, verbose_name="Supervisor de Vuelos")
    is_billing_supervisor = models.BooleanField(default=False, verbose_name="Facturador de Vuelos")
    is_admin_vuelos = models.BooleanField(default=False, help_text="Permite revertir vuelos a estado pendiente.")

    # En lugar de 'user.groups' (del core de Django), definimos nuestra propia relaci贸n M2M.
    groups_custom = models.ManyToManyField(
        CustomGroup,
        blank=True,
        related_name='users_custom',
        help_text="Grupos personalizados a los que pertenece este usuario."
    )

    def __str__(self):
        return self.username
        
    class Meta:
        default_permissions = ()  # 🔒 Desactiva los permisos automáticos de Django
