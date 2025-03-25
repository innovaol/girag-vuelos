# /home/innovaol/girapp/main/views/document_views.py

from django.contrib.auth.decorators import permission_required
# -*- coding: utf-8 -*-
import os
import base64
from django.http import HttpResponse, HttpResponseForbidden
from django.shortcuts import get_object_or_404, redirect
from django.contrib.auth.decorators import login_required
from django.contrib import messages
from django.template.loader import render_to_string
from main.models.document import Document
from main.utils.audit import log_action

@login_required
def view_document(request, doc_id):
    doc = get_object_or_404(Document, id=doc_id)
    file_path = doc.file.path
    print(f"[DEBUG] Usuario: {request.user.username} accediendo a {file_path}")
    if not os.path.exists(file_path):
        return HttpResponse("Archivo no encontrado", status=404)
        return HttpResponse("Archivo no encontrado", status=404)

    ext = os.path.splitext(file_path)[1].lower()
    content_type = 'application/octet-stream'
    if ext == '.pdf':
        content_type = 'application/pdf'
    elif ext == '.doc':
        content_type = 'application/msword'
    elif ext == '.docx':
        content_type = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    elif ext == '.xls':
        content_type = 'application/vnd.ms-excel'
    elif ext == '.xlsx':
        content_type = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    elif ext == '.txt':
        content_type = 'text/plain'
    elif ext in ['.jpg', '.jpeg']:
        content_type = 'image/jpeg'
    elif ext == '.png':
        content_type = 'image/png'
    elif ext == '.gif':
        content_type = 'image/gif'

    # Si es una imagen, renderiza la plantilla de vista previa
    if ext in ['.jpg', '.jpeg', '.png', '.gif']:
        with open(file_path, 'rb') as f:
            image_data = f.read()
        encoded_image = base64.b64encode(image_data).decode('utf-8')
        context = {
            'image_data': encoded_image,
            'content_type': content_type,
            'filename': os.path.basename(file_path),
        }
        html = render_to_string('preview_image.html', context)
        return HttpResponse(html)
    # Para Excel se sirve el archivo en bruto para que SheetJS lo procese
    elif ext in ['.xls', '.xlsx']:
        with open(file_path, 'rb') as f:
            data = f.read()
        response = HttpResponse(data, content_type=content_type)
        response['Content-Disposition'] = f'inline; filename="{os.path.basename(file_path)}"'
        return response
    # Para PDF, Word y otros archivos
    else:
        with open(file_path, 'rb') as f:
            data = f.read()
        response = HttpResponse(data, content_type=content_type)
        response['Content-Disposition'] = f'inline; filename="{os.path.basename(file_path)}"'
        return response

@permission_required('main.delete_', raise_exception=True)
def delete_document(request, doc_id):
    doc = get_object_or_404(Document, id=doc_id)
    if not request.user.has_perm('main.delete_document'):
        messages.error(request, "No tienes permiso para eliminar este documento.")
        return redirect('flight_detail', flight_id=doc.flight.id)
    flight_id = doc.flight.id
    doc.delete()
    messages.success(request, "Documento eliminado correctamente.")
    log_action(
        request.user,
        "Eliminó un documento",
        f"Documento {doc.doc_type} eliminado del vuelo {doc.flight.flight_number}."
    )
    return redirect('flight_detail', flight_id=flight_id)
