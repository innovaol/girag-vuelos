# models.py

from django.db import models
from main.models.airline import Airline

class Aircraft(models.Model):
    aeronave = models.CharField(max_length=50, unique=True)  # Cambiado de numero_aeronave a aeronave
    aerolinea = models.ForeignKey(Airline, on_delete=models.CASCADE, related_name='aeronaves')

    # ✅ Mantener si la relación recursiva es necesaria
    parent_aircraft = models.ForeignKey('self', on_delete=models.CASCADE, null=True, blank=True, related_name='child_aircrafts')

    def __str__(self):
        return f"{self.aeronave} ({self.aerolinea.name})"  # Usamos aeronave
