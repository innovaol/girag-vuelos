from django.contrib.auth.models import AbstractUser
from django.db import models

class CustomUser(AbstractUser):
    is_flight_supervisor = models.BooleanField(default=False, verbose_name="Supervisor de Vuelos")
    is_billing_supervisor = models.BooleanField(default=False, verbose_name="Facturador de Vuelos")
    is_admin_vuelos = models.BooleanField(default=False, help_text="Permite revertir vuelos a estado pendiente.")

    def __str__(self):
        return self.username
