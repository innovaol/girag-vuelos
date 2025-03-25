# /home/innovaol/girapp/main/urls.py

# from django.contrib import admin
from django.urls import path, include
from django.contrib.auth import views as auth_views
from main.views.notification_views import mark_as_billed

# Notificaciones
from main.views import notification_views


# Vistas principales
from main import core_views as views

# Vistas de ajustes
from main.views.settings_views import manage_settings

# Vistas de vuelos
from main.views.flight_views import (
    manage_flights, flight_form, delete_flight,
    flight_detail, check_flight_number,
    revert_flight_to_pending # Para habilitar botón de revertir estado de vuelo
)

# Vistas de aerolíneas
from main.views.airline_views import (
    manage_airlines, create_airline, edit_airline, delete_airline, check_airline_name
)

# Vistas de aeronaves
from main.views.aircraft_views import (
    manage_aircrafts, aircraft_form, delete_aircraft, check_aircraft_name, get_aircrafts_by_airline
)

# Vistas de documentos y tipos de documentos
from main.views.document_type_views import (
    manage_document_types, create_document_type, edit_document_type, delete_document_type, check_document_type_name
)
from main.views.document_views import view_document, delete_document

# Vistas de auditoría
from main.views.audit_views import audit_logs, audit_detail

# Vistas de usuarios, grupos y autenticación
from main.views.auth_views import unauthorized
from main.views.user_views import (
    manage_users, create_user, edit_user, delete_user, change_user_password,
    check_username, check_email  # 🔹 Validaciones AJAX aquí
)
from main.views.group_views import (
    manage_groups, create_group, edit_group, delete_group,
    check_group_name  # 🔹 Validación AJAX aquí
)

# Vistas de permisos (Gestión y asignación)
from main.views.permissions_manage_views import manage_permissions
from main.views.permissions_assign_views import assign_permissions

# Vistas de navegación
from main.views.navigation_views import manage_navigation_sections

urlpatterns = [
    # Notificaciones
    path('notifications/', notification_views.notification_view, name='notification_view'),
    path('flights/approve/<int:flight_id>/', notification_views.approve_flight, name='approve_flight'),
    path('flights/mark-as-billed/<int:flight_id>/', mark_as_billed, name='mark_as_billed'),
    
    # Dashboard
    path('', views.dashboard, name='dashboard'),
    path('dashboard/', views.dashboard, name='dashboard'),

    # Gestión de Vuelos
    path('flights/', manage_flights, name='manage_flights'),
    path('flights/<int:flight_id>/', flight_detail, name='view_flight'), 
    path('flights/delete/<int:flight_id>/', delete_flight, name='delete_flight'),
    path('flights/form/', flight_form, name='create_flight'),
    path('flights/form/<int:flight_id>/', flight_form, name='edit_flight'),
    # ✅ API para obtener aeronaves por aerolínea
    path('api/aircrafts/<int:airline_id>/', get_aircrafts_by_airline, name='get_aircrafts_by_airline'),
    # ✅ Validación AJAX del número de vuelo
    path("check/flight_number/", check_flight_number, name="check_flight_number"),
    # Para habilitar botón de revertir estado de vuelo
    path('flights/revert/<int:flight_id>/', revert_flight_to_pending, name='revert_flight_to_pending'),


    # Gestión de Aerolíneas
    path('airlines/', manage_airlines, name='manage_airlines'),
    path('airlines/create/', create_airline, name='create_airline'),
    path('airlines/edit/<int:airline_id>/', edit_airline, name='edit_airline'),
    path('airlines/delete/<int:airline_id>/', delete_airline, name='delete_airline'),
    path("check/airline/", check_airline_name, name="check_airline_name"),

    # Gestión de Aeronaves
    path('aeronaves/', manage_aircrafts, name='manage_aircrafts'),
    path('aeronaves/create/', aircraft_form, name='create_aircraft'),
    path('aeronaves/edit/<int:pk>/', aircraft_form, name='edit_aircraft'),
    path('aeronaves/delete/<int:pk>/', delete_aircraft, name='delete_aircraft'),
    path("check/aircraft/", check_aircraft_name, name="check_aircraft_name"),

    # Gestión de Tipos de Documento
    path('document-types/', manage_document_types, name='manage_document_types'),
    path('document-types/create/', create_document_type, name='create_document_type'),
    path('document-types/edit/<int:doc_type_id>/', edit_document_type, name='edit_document_type'),
    path('document-types/delete/<int:doc_type_id>/', delete_document_type, name='delete_document_type'),
    path("check/document_type/", check_document_type_name, name="check_document_type_name"),

    # Auditoría
    path('audit/', audit_logs, name='audit_logs'),
    path('audit/<int:log_id>/', audit_detail, name='audit_detail'),

    # Acceso Denegado
    path('unauthorized/', unauthorized, name='unauthorized'),

    # Gestión de Usuarios
    path('users/', manage_users, name='manage_users'),
    path('users/create/', create_user, name='create_user'),
    path('users/edit/<int:user_id>/', edit_user, name='edit_user'),
    path('users/delete/<int:user_id>/', delete_user, name='delete_user'),
    path('users/change-password/<int:user_id>/', change_user_password, name='change_user_password'),

    # Validaciones AJAX
    path("check/airline/", check_airline_name, name="check_airline_name"),
    path("aeronaves/check-name/", check_aircraft_name, name="check_aircraft_name"),
    path("check/document_type/", check_document_type_name, name="check_document_type_name"),
    path("check/username/", check_username, name="check_username"),
    path("check/email/", check_email, name="check_email"),
    path("check/group/", check_group_name, name="check_group_name"),  # 🔹 Se deja solo esta

    # Gestión de Grupos
    path('groups/', manage_groups, name='manage_groups'),
    path('groups/create/', create_group, name='create_group'),
    path('groups/edit/<int:group_id>/', edit_group, name='edit_group'),
    path('groups/delete/<int:group_id>/', delete_group, name='delete_group'),

    # Documentos
    path('documents/<int:doc_id>/view/', view_document, name='view_document'),
    path('documents/<int:doc_id>/delete/', delete_document, name='delete_document'),

    # Autenticación
    path('login/', views.custom_login_view, name='login'),
    path('logout/', views.logout_view, name='logout'),

    # Ajustes y Configuraciones
    path('manage_settings/', manage_settings, name='manage_settings'),

    # Gestión de Permisos
    path('manage-permissions/', manage_permissions, name='manage_permissions'),
    path('assign-permissions/', assign_permissions, name='assign_permissions'),

    # Gestión de Secciones (Navegación)
    path('manage_settings/navigation_sections/', manage_navigation_sections, name='manage_navigation_sections'),

    # Admin
    # path('admin/', admin.site.urls),
]
