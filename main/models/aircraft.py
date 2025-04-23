# /home/innovaol/girapp/main/models/aircraft.py

from django.db import models
from main.models.airline import Airline
from main.models.archivable import ArchivableModel

class Aircraft(ArchivableModel):
    aeronave = models.CharField(max_length=50, unique=True)
    aerolinea = models.ForeignKey(Airline, on_delete=models.RESTRICT, related_name='aeronaves')

    parent_aircraft = models.ForeignKey('self', on_delete=models.RESTRICT, null=True, blank=True, related_name='child_aircrafts')

    def __str__(self):
        return f"{self.aeronave} ({self.aerolinea.name})"
        
    class Meta:
        default_permissions = ()  # 🔒 Desactiva los permisos automáticos de Django
