# /home/innovaol/girapp/main/views/permissions_views.py

from django.contrib.auth.models import Permission
from django.contrib.contenttypes.models import ContentType
from django.shortcuts import render
from django.http import JsonResponse

SPECIAL_CODENAMES = ['admin_vuelos', 'approve_flight', 'mark_as_billed']

def get_available_permissions():
    """
    Obtiene los permisos esperados organizados por secciones conceptuales.
    Retorna un diccionario con la estructura:
    {
        "Permisos Especiales": [
            {"id": ..., "codename": "change_status_flight", "name": "Cambiar estado de vuelo"},
            {"id": ..., "codename": "approve_flight", "name": "Aprobar vuelo"},
            {"id": ..., "codename": "mark_as_billed", "name": "Marcar vuelo como facturado"},
        ],
        "Dashboard": [
            {"id": ..., "codename": "access_dashboard", "name": "Acceso al Dashboard"},
        ],
        "Vuelos": [
            {"id": ..., "codename": "view_flight", "name": "Ver vuelo"},
            {"id": ..., "codename": "create_flight", "name": "Crear vuelo"},
            {"id": ..., "codename": "edit_flight", "name": "Editar vuelo"},
            {"id": ..., "codename": "delete_flight", "name": "Eliminar vuelo"},
        ],
        "Notificaciones": [
            {"id": ..., "codename": "view_notifications", "name": "Ver notificaciones"},
        ],
        "Aerolíneas": [
            {"id": ..., "codename": "create_airline", "name": "Crear aerolínea"},
            {"id": ..., "codename": "edit_airline", "name": "Editar aerolínea"},
            {"id": ..., "codename": "delete_airline", "name": "Eliminar aerolínea"},
            {"id": ..., "codename": "view_airline", "name": "Ver aerolínea"},
        ],
        "Aeronaves": [
            {"id": ..., "codename": "create_aircraft", "name": "Crear aeronave"},
            {"id": ..., "codename": "edit_aircraft", "name": "Editar aeronave"},
            {"id": ..., "codename": "delete_aircraft", "name": "Eliminar aeronave"},
            {"id": ..., "codename": "view_aircraft", "name": "Ver aeronave"},
        ],
        "Usuarios": [
            {"id": ..., "codename": "change_password", "name": "Cambiar contraseña de usuario"},
            {"id": ..., "codename": "create_user", "name": "Crear usuario"},
            {"id": ..., "codename": "edit_user", "name": "Editar usuario"},
            {"id": ..., "codename": "delete_user", "name": "Eliminar usuario"},
            {"id": ..., "codename": "view_user", "name": "Ver usuario"},
        ],
        "Grupos": [
            {"id": ..., "codename": "create_group", "name": "Crear grupo"},
            {"id": ..., "codename": "edit_group", "name": "Editar grupo"},
            {"id": ..., "codename": "delete_group", "name": "Eliminar grupo"},
            {"id": ..., "codename": "view_group", "name": "Ver grupo"},
        ],
        "Tipos de Documento": [
            {"id": ..., "codename": "create_document_type", "name": "Crear tipo de documento"},
            {"id": ..., "codename": "edit_document_type", "name": "Editar tipo de documento"},
            {"id": ..., "codename": "delete_document_type", "name": "Eliminar tipo de documento"},
            {"id": ..., "codename": "view_documenttype", "name": "Ver tipo de documento"},
        ],
        "Auditoría": [
            {"id": ..., "codename": "view_audit", "name": "Ver auditoría"},
            {"id": ..., "codename": "view_audit_detail", "name": "Ver detalle de auditoría"},
        ],
        "Configuración": [
            {"id": ..., "codename": "access_settings", "name": "Acceder a configuración"},
        ],
    }
    """
    # Mapa de codename a sección (nombres en español)
    section_map = {
        # Dashboard
        "access_dashboard": "Dashboard",
        # Vuelos
        "create_flight": "Vuelos",
        "edit_flight": "Vuelos",
        "view_flight": "Vuelos",
        "delete_flight": "Vuelos",
        "change_status_flight": "Vuelos",  # Nuevo permiso para cambiar el estado de vuelo
        # Notificaciones
        "approve_flight": "Notificaciones",
        "mark_as_billed": "Notificaciones",
        "view_notifications": "Notificaciones",
        # Aerolíneas
        "create_airline": "Aerolíneas",
        "edit_airline": "Aerolíneas",
        "delete_airline": "Aerolíneas",
        "view_airline": "Aerolíneas",
        "restore_airline": "Aerolíneas",
        # Aeronaves
        "create_aircraft": "Aeronaves",
        "edit_aircraft": "Aeronaves",
        "delete_aircraft": "Aeronaves",
        "view_aircraft": "Aeronaves",
        "restore_aircraft": "Aeronaves",
        # Usuarios
        "change_password": "Usuarios",
        "create_user": "Usuarios",
        "edit_user": "Usuarios",
        "delete_user": "Usuarios",
        "view_user": "Usuarios",
        "restore_user": "Usuarios",
        # Grupos
        "create_group": "Grupos",
        "edit_group": "Grupos",
        "delete_group": "Grupos",
        "view_group": "Grupos",
        "restore_group": "Grupos",
        # Tipos de Documento
        "create_document_type": "Tipos de Documento",
        "edit_document_type": "Tipos de Documento",
        "delete_document_type": "Tipos de Documento",
        "view_documenttype": "Tipos de Documento",
        "restore_document_type": "Tipos de Documento",
        # Auditoría
        "view_audit": "Auditoría",
        "view_audit_detail": "Auditoría",
        # Configuración
        "access_settings": "Configuración",
    }

    # Lista de permisos válidos (según section_map)
    valid_permissions = set(section_map.keys()) - set(SPECIAL_CODENAMES)
    permissions = Permission.objects.filter(codename__in=valid_permissions)\
                    .select_related('content_type')
    
    # Convertir cada permiso a un diccionario
    permissions_list = []
    for perm in permissions:
        permissions_list.append({
            "id": perm.id,
            "codename": perm.codename,
            "name": perm.name,
            "section": section_map.get(perm.codename, "Otros")
        })
    
    # Orden de prioridad: view, create, edit, delete, y el resto
    def sort_key(item):
        codename = item["codename"]
        if codename.startswith("view_"):
            order = 0
        elif codename.startswith("create_"):
            order = 1
        elif codename.startswith("edit_"):
            order = 2
        elif codename.startswith("delete_"):
            order = 3
        elif codename.startswith("restore_"):
            order = 4
        else:
            order = 5
        return (order, item["name"])
    
    # Agrupar permisos por sección y ordenar cada grupo según la prioridad
    grouped_permissions = {}
    for perm in permissions_list:
        section = perm["section"]
        grouped_permissions.setdefault(section, []).append(perm)
    
    # Ordenar cada lista de permisos
    for section in grouped_permissions:
        grouped_permissions[section] = sorted(grouped_permissions[section], key=sort_key)
    
    return grouped_permissions

def permissions_list_view(request):
    """
    Vista que devuelve la lista de permisos organizados por secciones conceptuales.
    Se puede usar en la API o en las vistas de creación/edición de usuarios y grupos.
    """
    permissions = get_available_permissions()
    return JsonResponse({"permissions": permissions})
