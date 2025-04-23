# /home/innovaol/girapp/main/views/document_type_views.py

from django.contrib.auth.decorators import permission_required
from django.shortcuts import render, redirect, get_object_or_404
from django.contrib import messages
from django.http import JsonResponse
from django.db import IntegrityError  # ✅ Importación para manejar claves foráneas
from main.models.document_type import DocumentType  # ✅ Modelo de Tipo de Documento
from main.models.document import Document  # ✅ Modelo de Documentos para verificar relaciones
from main.forms.document_type_forms import DocumentTypeForm
from main.utils.audit import log_action  # ✅ Registro de acciones para auditoría
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

@permission_required('main.delete_documenttype', login_url='unauthorized')
def delete_document_type(request, doc_type_id):
    """
    Vista para eliminar un tipo de documento vía JSON/fetch.
    Si tiene documentos asociados, retorna 'archivable': True.
    """
    doc_type = get_object_or_404(DocumentType, pk=doc_type_id)

    # Verificar si hay documentos asociados
    if Document.objects.filter(doc_type=doc_type).exists():
        log_action(
            request.user,
            "Intentó eliminar un tipo de documento",
            f"No se pudo eliminar el tipo de documento '{doc_type.name}' porque tiene documentos asociados."
        )
        return JsonResponse({
            'archivable': True,
            'error': f'El tipo de documento "{doc_type.name}" no se puede eliminar porque está asociado a documentos.'
        })

    try:
        doc_type.delete()
        log_action(request.user, "Eliminó un tipo de documento", f"Tipo de documento {doc_type.name} eliminado.")
        return JsonResponse({'success': True})
    except IntegrityError as e:
        if "main_document" in str(e):
            log_action(
                request.user,
                "Intentó eliminar un tipo de documento",
                f"No se pudo eliminar el tipo de documento '{doc_type.name}' debido a documentos asociados."
            )
            return JsonResponse({
                'archivable': True,
                'error': f'El tipo de documento "{doc_type.name}" no se puede eliminar porque tiene documentos asociados.'
            })
        else:
            log_action(
                request.user,
                "Error al eliminar un tipo de documento",
                f"Tipo de documento {doc_type.name}: {str(e)}"
            )
            return JsonResponse({'success': False, 'error': str(e)})
    except Exception as e:
        log_action(request.user, "Error al eliminar un tipo de documento", f"Tipo de documento {doc_type.name}: {str(e)}")
        return JsonResponse({'success': False, 'error': str(e)})

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


@permission_required('main.delete_documenttype', login_url='unauthorized')
def archive_document_type(request, doc_type_id):
    """
    Vista para archivar un Tipo de Documento.
    Se marca como archivado en lugar de eliminarlo, incluso si está en uso.
    """
    doc_type = get_object_or_404(DocumentType, pk=doc_type_id)

    doc_type.is_archived = True
    doc_type.save()

    log_action(request.user, "Archivó tipo de documento", f"Tipo de documento '{doc_type.name}' archivado.")
    return JsonResponse({'success': True, 'message': f"Tipo de documento '{doc_type.name}' archivado correctamente."})

    
@permission_required('main.restore_documenttype', login_url='unauthorized')
def archived_document_types(request):
    """
    Vista para listar los Tipos de Documentos archivados.
    Utiliza el manager 'all_objects' para acceder a todos los registros.
    """
    doc_types = DocumentType.all_objects.filter(is_archived=True).order_by('name')
    return render(request, 'archived_document_types.html', {'doc_types': doc_types})


@permission_required('main.restore_documenttype', login_url='unauthorized')
def restore_document_type(request, doc_type_id):
    """
    Vista para restaurar un Tipo de Documento archivado.
    """
    doc_type = get_object_or_404(DocumentType.all_objects, pk=doc_type_id)
    if not doc_type.is_archived:
        messages.error(request, f"El tipo de documento '{doc_type.name}' no se encuentra archivado.")
    else:
        doc_type.is_archived = False
        doc_type.save()
        log_action(request.user, "Restauró un tipo de documento", f"Tipo de documento '{doc_type.name}' restaurado.")
        messages.success(request, f"Tipo de documento '{doc_type.name}' restaurado correctamente.")
    return redirect('archived_document_types')

