from django.urls import path
from django.contrib.auth import views as auth_views

from .views.flight_views import (
    dashboard, manage_flights, create_flight, edit_flight, delete_flight, flight_detail
)
from .views.airline_views import (
    manage_airlines, create_airline, edit_airline, delete_airline
)
from .views.document_type_views import (
    manage_document_types, create_document_type, edit_document_type, delete_document_type
)
from .views.audit_views import audit_logs, audit_detail
from .views.auth_views import unauthorized
from .views.user_views import (
    manage_users, create_user, edit_user, delete_user
)
from .views.group_views import (
    manage_groups, create_group, edit_group, delete_group
)
from .views.document_views import delete_document

urlpatterns = [
    # Dashboard
    path('', dashboard, name='dashboard'),
    path('dashboard/', dashboard, name='dashboard'),

    # Gestión de Vuelos
    path('flights/', manage_flights, name='manage_flights'),
    path('flights/create/', create_flight, name='create_flight'),
    path('flights/edit/<int:flight_id>/', edit_flight, name='edit_flight'),
    path('flights/delete/<int:flight_id>/', delete_flight, name='delete_flight'),
    path('flights/detail/<int:flight_id>/', flight_detail, name='flight_detail'),

    # Gestión de Aerolíneas
    path('airlines/', manage_airlines, name='manage_airlines'),
    path('airlines/create/', create_airline, name='create_airline'),
    path('airlines/edit/<int:airline_id>/', edit_airline, name='edit_airline'),
    path('airlines/delete/<int:airline_id>/', delete_airline, name='delete_airline'),

    # Gestión de Tipos de Documentos
    path('document-types/', manage_document_types, name='manage_document_types'),
    path('document-types/create/', create_document_type, name='create_document_type'),
    path('document-types/edit/<int:doc_type_id>/', edit_document_type, name='edit_document_type'),
    path('document-types/delete/<int:doc_type_id>/', delete_document_type, name='delete_document_type'),

    # Auditoría
    path('audit/', audit_logs, name='audit_logs'),
    path('audit/<int:log_id>/', audit_detail, name='audit_detail'),

    # Acceso denegado
    path('unauthorized/', unauthorized, name='unauthorized'),

    # Gestión de Usuarios
    path('users/', manage_users, name='manage_users'),
    path('users/create/', create_user, name='create_user'),
    path('users/edit/<int:user_id>/', edit_user, name='edit_user'),
    path('users/delete/<int:user_id>/', delete_user, name='delete_user'),

    # Gestión de Grupos
    path('groups/', manage_groups, name='manage_groups'),
    path('groups/create/', create_group, name='create_group'),
    path('groups/edit/<int:group_id>/', edit_group, name='edit_group'),
    path('groups/delete/<int:group_id>/', delete_group, name='delete_group'),

    # Eliminación de Documento (para eliminación individual en otras vistas)
    path('document/delete/<int:doc_id>/', delete_document, name='delete_document'),

    # Autenticación
    path('accounts/login/', auth_views.LoginView.as_view(template_name='main/login.html'), name='login'),
    path('accounts/logout/', auth_views.LogoutView.as_view(), name='logout'),
]
