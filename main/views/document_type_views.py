from django.contrib.auth.decorators import permission_required
from django.shortcuts import render, redirect, get_object_or_404
from django.contrib import messages
from django.http import JsonResponse
from main.models.document_type import DocumentType
from main.forms.document_type_forms import DocumentTypeForm
import json
import logging

logger = logging.getLogger(__name__)

@permission_required('main.view_documenttype', login_url='unauthorized')
def manage_document_types(request):
    doc_types = DocumentType.objects.all().order_by('name')
    return render(request, 'manage_document_types.html', {'doc_types': doc_types})

@permission_required('main.create_document_type', login_url='unauthorized')
def create_document_type(request):
    if request.method == 'POST':
        form = DocumentTypeForm(request.POST)
        if form.is_valid():
            doc_type_name = form.cleaned_data['name'].strip().lower()
            if DocumentType.objects.filter(name__iexact=doc_type_name).exists():
                logger.warning(f"Intento de crear documento duplicado por {request.user.username}")
            else:
                form.save()
                messages.success(request, 'Tipo de documento creado correctamente.')
                logger.info(f"✅ Tipo de documento creado por {request.user.username}")
                return redirect('manage_document_types')
        else:
            logger.warning(f"⚠️ Error al crear el tipo de documento por {request.user.username}: {form.errors}")
    else:
        form = DocumentTypeForm()
    return render(request, 'document_type_form.html', {'form': form, 'title': 'Crear Tipo de Documento'})

@permission_required('main.edit_document_type', login_url='unauthorized')
def edit_document_type(request, doc_type_id):
    doc_type = get_object_or_404(DocumentType, pk=doc_type_id)
    if request.method == 'POST':
        form = DocumentTypeForm(request.POST, instance=doc_type)
        if form.is_valid():
            doc_type_name = form.cleaned_data['name'].strip().lower()
            if DocumentType.objects.filter(name__iexact=doc_type_name).exclude(pk=doc_type_id).exists():
                logger.warning(f"Intento de editar documento duplicado por {request.user.username}")
            else:
                form.save()
                messages.success(request, 'Tipo de documento actualizado correctamente.')
                logger.info(f"✅ Tipo de documento editado por {request.user.username}")
                return redirect('manage_document_types')
        else:
            logger.warning(f"⚠️ Error al editar el tipo de documento '{doc_type.name}' por {request.user.username}: {form.errors}")
    else:
        form = DocumentTypeForm(instance=doc_type)
    return render(request, 'document_type_form.html', {'form': form, 'title': 'Editar Tipo de Documento'})

@permission_required('main.delete_document_type', login_url='unauthorized')
def delete_document_type(request, doc_type_id):
    doc_type = get_object_or_404(DocumentType, pk=doc_type_id)
    logger.warning(f"⚠️ Usuario {request.user.username} está eliminando el tipo de documento '{doc_type.name}'")
    try:
        doc_type.delete()
        messages.success(request, 'Tipo de documento eliminado correctamente.')
    except Exception as e:
        messages.error(request, f"No se pudo eliminar el tipo de documento: {str(e)}")
    return redirect('manage_document_types')

@permission_required('main.create_document_type', login_url='unauthorized')
def check_document_type_name(request):
    """
    Valida si un nombre de tipo de documento ya existe (sin distinguir mayúsculas y minúsculas).
    """
    if request.method == "POST":
        try:
            data = json.loads(request.body)
            doc_type_name = data.get("document_type_name", "").strip()
            if not doc_type_name:
                return JsonResponse({
                    "valid": False,
                    "error": "El nombre no puede estar vacío."
                })
            if DocumentType.objects.filter(name__iexact=doc_type_name).exists():
                return JsonResponse({
                    "valid": False,
                    "error": f"El tipo de documento ya existe."
                })
            return JsonResponse({
                "valid": True,
                "success": "✔ Nombre de tipo de documento disponible."
            })
        except json.JSONDecodeError:
            return JsonResponse({
                "valid": False,
                "error": "Error en el formato de la solicitud."
            }, status=400)
    return JsonResponse({
        "valid": False,
        "error": "⚠️ Método no permitido."
    }, status=405)
