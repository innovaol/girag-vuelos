# /home/innovaol/girapp/main/models/document.py

import os
from datetime import datetime
import pytz
from django.db import models
from django.db.models.signals import pre_delete
from django.dispatch import receiver
from django.utils.text import slugify
from main.models.flight import Flight
import uuid
from main.models.document_type import DocumentType


def generate_document_filename(instance, filename):
    """
    Genera un nombre de archivo siguiendo el formato:
    "documents/{flight_number}-{YYYYMMDD}_{HHMMSS}_{unique_id}{ext}"
    Ejemplo: "documents/12B1514-20250417_220156_5a3f2e9c.png"
    """
    # Obtener el número de vuelo y formatearlo para evitar caracteres especiales
    flight_number = slugify(instance.flight.flight_number) if instance.flight.flight_number else "vuelo"
    # Obtener la hora actual en la zona horaria de Panamá
    panama_tz = pytz.timezone("America/Panama")
    now = datetime.now(panama_tz)
    date_str = now.strftime("%Y%m%d")
    time_str = now.strftime("%H%M%S")
    # Generar un UUID corto para asegurar unicidad (8 caracteres)
    unique_id = uuid.uuid4().hex[:8]
    # Obtener la extensión del archivo original
    _, ext = os.path.splitext(filename)
    # Nombre final del archivo
    return f"documents/{flight_number}-{date_str}_{time_str}_{unique_id}{ext}"

class Document(models.Model):
    doc_type = models.ForeignKey(DocumentType, on_delete=models.PROTECT)
    file = models.FileField(upload_to=generate_document_filename)
    flight = models.ForeignKey(Flight, on_delete=models.CASCADE, related_name='documents')

    def __str__(self):
        return f"{self.doc_type} - {self.file.name}"
    
    class Meta:
        default_permissions = ()  # 🔒 Desactiva los permisos automáticos

@receiver(pre_delete, sender=Document)
def delete_document_file(sender, instance, **kwargs):
    """
    Señal que elimina el archivo físico del servidor cuando se elimina una instancia de Document.
    """
    if instance.file:
        file_path = instance.file.path
        if os.path.exists(file_path):
            os.remove(file_path)
