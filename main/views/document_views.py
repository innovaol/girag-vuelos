from django.shortcuts import get_object_or_404, redirect
from django.contrib.auth.decorators import login_required
from django.contrib import messages
from main.models.document import Document
from main.utils.audit import log_action

@login_required
def delete_document(request, doc_id):
    doc = get_object_or_404(Document, id=doc_id)
    if not request.user.has_perm('main.delete_document'):
        messages.error(request, "No tienes permiso para eliminar este documento.")
        return redirect('flight_detail', flight_id=doc.flight.id)
    flight_id = doc.flight.id
    doc.delete()
    messages.success(request, "Documento eliminado correctamente.")
    log_action(request.user, "Eliminó un documento", f"Documento {doc.doc_type} eliminado del vuelo {doc.flight.flight_number}.")
    return redirect('flight_detail', flight_id=flight_id)
