# /home/innovaol/girapp/main/models/airline.py

from django.db import models

class Airline(models.Model):
    name = models.CharField(max_length=100, unique=True)
    is_archived = models.BooleanField(default=False)

    def __str__(self):
        return self.name
        
    class Meta:
        default_permissions = ()  # 🔒 Desactiva los permisos automáticos de Django
