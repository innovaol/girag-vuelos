# /home/innovaol/girapp/main/models/document_type.py

from django.db import models
from main.models.archivable import ArchivableModel

class DocumentType(ArchivableModel):
    name = models.CharField(max_length=255)
    
    def __str__(self):
        return self.name
        
    class Meta:
        default_permissions = ()  # 🔒 Desactiva los permisos automáticos de Django