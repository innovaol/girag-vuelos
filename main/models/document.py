# /home/innovaol/girapp/main/models/document.py

import os
from datetime import datetime
import pytz
from django.db import models
from django.db.models.signals import pre_delete
from django.dispatch import receiver
from django.utils.text import slugify
from main.models.flight import Flight

def generate_document_filename(instance, filename):
    """
    Genera un nombre de archivo usando el tipo de documento, el número de vuelo y la fecha/hora actual en la zona horaria de Panamá.
    Formato: "documents/{tipo_documento}-{flight_number}-{YYYYMMDD}_{HHMMSS}{ext}"
    Ejemplo: "documents/oirsa-12B1514-20250417_220156.png"
    """
    flight = instance.flight
    flight_number = slugify(flight.flight_number) if flight.flight_number else "vuelo"
    # Normalizamos el tipo de documento usando slugify
    doc_type_slug = slugify(instance.doc_type) if instance.doc_type else "documento"
    # Obtener la hora actual en la zona horaria de Panamá
    panama_tz = pytz.timezone("America/Panama")
    now = datetime.now(panama_tz)
    date_str = now.strftime("%Y%m%d")
    time_str = now.strftime("%H%M%S")
    _, ext = os.path.splitext(filename)
    return f"documents/{doc_type_slug}-{flight_number}-{date_str}_{time_str}{ext}"

class Document(models.Model):
    doc_type = models.CharField(max_length=50)
    file = models.FileField(upload_to=generate_document_filename)
    flight = models.ForeignKey(Flight, on_delete=models.CASCADE, related_name='documents')

    def __str__(self):
        return f"{self.doc_type} - {self.file.name}"
    
    class Meta:
        pass

@receiver(pre_delete, sender=Document)
def delete_document_file(sender, instance, **kwargs):
    """
    Señal que elimina el archivo físico del servidor cuando se elimina una instancia de Document.
    """
    if instance.file:
        file_path = instance.file.path
        if os.path.exists(file_path):
            os.remove(file_path)
