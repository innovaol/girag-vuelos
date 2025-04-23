# /home/innovaol/AppVuelos/main/models/flight.py

from django.db import models
from django.conf import settings
from main.models.airline import Airline
from main.models.aircraft import Aircraft

from django.db import models
from django.conf import settings
from main.models.airline import Airline
from main.models.aircraft import Aircraft

class Flight(models.Model):
    flight_number = models.CharField(max_length=50)
    date = models.DateField()
    created_at = models.DateField(auto_now_add=True)
    created_by = models.ForeignKey(
        settings.AUTH_USER_MODEL, 
        on_delete=models.RESTRICT, 
        null=True, 
        blank=True,
        related_name="created_flights"
    )
    airline = models.ForeignKey(Airline, on_delete=models.RESTRICT, related_name='flights')
    aircraft = models.ForeignKey(Aircraft, on_delete=models.RESTRICT, related_name='flight_list', null=True, blank=True)
    billing_user = models.ForeignKey(
        settings.AUTH_USER_MODEL, 
        on_delete=models.RESTRICT, 
        null=True, 
        blank=True, 
        related_name="billing_user", 
        help_text="Usuario encargado de la facturaci贸n"
    )
    supervisors = models.ManyToManyField(
        settings.AUTH_USER_MODEL, 
        related_name="supervised_flights", 
        blank=True
    )
    status = models.CharField(
        max_length=20,
        choices=[('pending', 'Pendiente'), ('approved', 'Aprobado'), ('billed', 'Facturado')],
        default='pending',
    )

    class Meta:
        permissions = [
            ("approve_flight", "Puede aprobar vuelos"),
            ("mark_as_billed", "Puede marcar vuelos como facturados"),
            ("admin_vuelos", "Puede administrar vuelos (revertir estado)"),
        ]
        
        default_permissions = ()  # 🔒 Desactiva los permisos automáticos de Django

    def __str__(self):
        return f"Vuelo {self.flight_number} - {self.airline.name}"
