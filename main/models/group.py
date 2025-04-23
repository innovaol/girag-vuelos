# /home/innovaol/girapp/main/models/group.py

from django.db import models
from main.models.custom_group import CustomGroup

class GroupExtension(models.Model):
    group = models.OneToOneField(CustomGroup, on_delete=models.CASCADE, primary_key=True, related_name='extension')

    def __str__(self):
        return self.group.name
        
    class Meta:
        default_permissions = ()  # 🔒 Desactiva los permisos automáticos

