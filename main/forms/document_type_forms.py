from django import forms
from main.models.document_type import DocumentType

class DocumentTypeForm(forms.ModelForm):
    class Meta:
        model = DocumentType
        fields = ['name']
        labels = {
            'name': 'Tipo de Documento'
        }
        widgets = {
            'name': forms.TextInput(attrs={
                'class': 'form-control',
                'placeholder': 'Ingrese el tipo de documento'
            }),
        }
