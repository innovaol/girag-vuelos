from django.shortcuts import render, redirect, get_object_or_404
from django.contrib.auth.decorators import login_required
from django.contrib import messages
from main.models.document_type import DocumentType
from main.forms.document_type_forms import DocumentTypeForm

@login_required
def manage_document_types(request):
    if not request.user.is_superuser:
        return redirect('unauthorized')
    doc_types = DocumentType.objects.all().order_by('name')
    return render(request, 'main/manage_document_types.html', {'doc_types': doc_types})

@login_required
def create_document_type(request):
    if not request.user.is_superuser:
        return redirect('unauthorized')
    if request.method == 'POST':
        form = DocumentTypeForm(request.POST)
        if form.is_valid():
            form.save()
            messages.success(request, 'Tipo de documento creado correctamente.')
            return redirect('manage_document_types')
    else:
        form = DocumentTypeForm()
    return render(request, 'main/create_document_type.html', {'form': form})

@login_required
def edit_document_type(request, doc_type_id):
    if not request.user.is_superuser:
        return redirect('unauthorized')
    doc_type = get_object_or_404(DocumentType, pk=doc_type_id)
    if request.method == 'POST':
        form = DocumentTypeForm(request.POST, instance=doc_type)
        if form.is_valid():
            form.save()
            messages.success(request, 'Tipo de documento actualizado correctamente.')
            return redirect('manage_document_types')
    else:
        form = DocumentTypeForm(instance=doc_type)
    return render(request, 'main/edit_document_type.html', {'form': form, 'doc_type': doc_type})

@login_required
def delete_document_type(request, doc_type_id):
    if not request.user.is_superuser:
        return redirect('unauthorized')
    doc_type = get_object_or_404(DocumentType, pk=doc_type_id)
    try:
        doc_type.delete()
        messages.success(request, 'Tipo de documento eliminado correctamente.')
    except Exception as e:
        messages.error(request, f"No se pudo eliminar el tipo de documento: {str(e)}")
    return redirect('manage_document_types')
