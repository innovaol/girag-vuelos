# /home/innovaol/girapp/girag_app/settings.py

import os
from pathlib import Path

# -----------------------------------------------------------------------------------
# Variables de entorno
# -----------------------------------------------------------------------------------
import environ

# Define BASE_DIR (si aún no lo tienes definido)
BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))

# Inicializar django-environ
env = environ.Env(
    # Puedes establecer variables por defecto (por ejemplo, para DEBUG)
    DEBUG=(bool, False)
)

# Leer el archivo .env que debe estar en BASE_DIR
environ.Env.read_env(os.path.join(BASE_DIR, '.env'))

# -----------------------------------------------------------------------------------
# Rutas base
# -----------------------------------------------------------------------------------
BASE_DIR = Path(__file__).resolve().parent.parent

# Donde se recolectarán los archivos estáticos al ejecutar collectstatic
STATIC_ROOT = os.path.join(BASE_DIR, 'staticfiles')

# -----------------------------------------------------------------------------------
# Clave secreta (cambia este valor para tu proyecto)
# -----------------------------------------------------------------------------------
SECRET_KEY = 'django-insecure-xxxxxxxxxxxxxxxxxxxxxxx'

# -----------------------------------------------------------------------------------
# Depuración (poner en False en producción)
# -----------------------------------------------------------------------------------
DEBUG = False

# -----------------------------------------------------------------------------------
# Hosts permitidos
# -----------------------------------------------------------------------------------
ALLOWED_HOSTS = ["vuelos.innovaol.com", "www.vuelos.innovaol.com", "127.0.0.1", "localhost"]

# -----------------------------------------------------------------------------------
# Modelo de Usuario Personalizado
# -----------------------------------------------------------------------------------
AUTH_USER_MODEL = 'main.CustomUser'

# -----------------------------------------------------------------------------------
# Aplicaciones instaladas
# -----------------------------------------------------------------------------------
INSTALLED_APPS = [
    'django.contrib.admin',           # ✅ Incluido para usar el panel de admin
    'django.contrib.auth',
    'django.contrib.contenttypes',
    'django.contrib.sessions',
    'django.contrib.messages',
    'django.contrib.staticfiles',
    'widget_tweaks',
    'main',
]

# -----------------------------------------------------------------------------------
# Middlewares esenciales
# -----------------------------------------------------------------------------------
MIDDLEWARE = [
    'django.middleware.security.SecurityMiddleware',
    'django.contrib.sessions.middleware.SessionMiddleware',
    'django.middleware.common.CommonMiddleware',
    'django.middleware.csrf.CsrfViewMiddleware',
    'django.contrib.auth.middleware.AuthenticationMiddleware',
    'django.contrib.messages.middleware.MessageMiddleware',
    'django.middleware.clickjacking.XFrameOptionsMiddleware',

    # ➔ Nuestro middleware de permisos
    'main.middleware.permission_middleware.PermissionMiddleware',
]

# -----------------------------------------------------------------------------------
# URLs raíz y WSGI
# -----------------------------------------------------------------------------------
ROOT_URLCONF = 'girag_app.urls'
WSGI_APPLICATION = 'girag_app.wsgi.application'

# -----------------------------------------------------------------------------------
# Configuración de Base de Datos (PosgreSQL)
# -----------------------------------------------------------------------------------
DATABASES = {
    # 'default': {
    #     'ENGINE': 'django.db.backends.sqlite3',
    #     'NAME': BASE_DIR / 'db.sqlite3',
    # }
    
    'default': {
        'ENGINE': 'django.db.backends.mysql',
        'NAME': env('DB_NAME'),
        'USER': env('DB_USER'),
        'PASSWORD': env('DB_PASSWORD'),
        'HOST': env('DB_HOST'),
        'PORT': env('DB_PORT'),
    }
}

# -----------------------------------------------------------------------------------
# Configuración de plantillas
# -----------------------------------------------------------------------------------
TEMPLATES = [
    {
        'BACKEND': 'django.template.backends.django.DjangoTemplates',
        'DIRS': [BASE_DIR / 'main' / 'templates'],  # ✅ Ruta correcta
        'APP_DIRS': True,
        'OPTIONS': {
            'context_processors': [
                'django.template.context_processors.debug',
                'django.template.context_processors.request',
                'django.contrib.auth.context_processors.auth',
                'django.contrib.messages.context_processors.messages',
                # Agrega el context processor de notificaciones:
                'main.utils.context_processors.notifications_processor',
            ],
        },
    },
]

# -----------------------------------------------------------------------------------
# Configuración de Archivos Estáticos
# -----------------------------------------------------------------------------------
STATIC_URL = '/static/'
STATICFILES_DIRS = [BASE_DIR / 'main' / 'static']
STATIC_ROOT = BASE_DIR / 'staticfiles'

# -----------------------------------------------------------------------------------
# Configuración de Archivos Media
# -----------------------------------------------------------------------------------
MEDIA_URL = '/media/'
MEDIA_ROOT = os.path.join(BASE_DIR, 'media')

# -----------------------------------------------------------------------------------
# Internacionalización
# -----------------------------------------------------------------------------------
# Configuración de formato de fecha global
LANGUAGE_CODE = 'es'
TIME_ZONE = 'America/Panama'
USE_I18N = True
USE_L10N = False  # 🔥 IMPORTANTE: Desactiva la localización automática de Django
USE_TZ = True

# Establecer formato de fecha por defecto en toda la app
DATE_FORMAT = 'd/m/Y'
DATE_INPUT_FORMATS = ['%d/%m/%Y']
DATETIME_FORMAT = 'd/m/Y H:i'
DATETIME_INPUT_FORMATS = ['%d/%m/%Y %H:%M']


# -----------------------------------------------------------------------------------
# Redirección al login y logout (✅ Login personalizado)
# -----------------------------------------------------------------------------------
LOGIN_URL = '/login/'               # ✅ Redirige al login personalizado
LOGIN_REDIRECT_URL = 'dashboard'    # ✅ Después del login, redirige al dashboard
LOGOUT_REDIRECT_URL = '/login/'     # ✅ Después del logout, redirige al login

# -----------------------------------------------------------------------------------
# Configuración de URL Base para Notificaciones y Enlaces Completos
# -----------------------------------------------------------------------------------
SITE_URL = "https://vuelos.innovaol.com"

# -----------------------------------------------------------------------------------
# Configuración de Correo (SMTP)
# -----------------------------------------------------------------------------------
EMAIL_BACKEND = 'django.core.mail.backends.smtp.EmailBackend'
EMAIL_HOST = 'localhost'  # Reemplaza con el servidor SMTP de tu proveedor de correo
EMAIL_PORT = 25  # Usa 465 si usas SSL
EMAIL_USE_TLS = False  # Usa seguridad TLS
EMAIL_USE_SSL = False  # No usar SSL si ya tienes TLS activado
EMAIL_HOST_USER = 'test@innovaol.com'  # Reemplaza con tu email
EMAIL_HOST_PASSWORD = os.getenv("EMAIL_CONTRASENA")  # ⚠️ Usa una contraseña de aplicación si usas Gmail
DEFAULT_FROM_EMAIL = 'test@innovaol.com'  # Email predeterminado para enviar correos

# 🔹 Opcional: Configuración para ver emails en consola (solo en pruebas)
# EMAIL_BACKEND = 'django.core.mail.backends.console.EmailBackend'

# -----------------------------------------------------------------------------------
# Configuración de Logging
# -----------------------------------------------------------------------------------
LOGGING = {
    'version': 1,
    'disable_existing_loggers': False,
    'handlers': {
        'file': {
            'level': 'DEBUG',
            'class': 'logging.FileHandler',
            'filename': '/home/innovaol/girapp/logs/debug.log',
        },
        'console': {
            'level': 'DEBUG',
            'class': 'logging.StreamHandler',
        },
    },
    'loggers': {
        'django': {
            'handlers': ['file', 'console'],
            'level': 'DEBUG',
            'propagate': True,
        },
    },
}


# -----------------------------------------------------------------------------------
# Fin de settings
# -----------------------------------------------------------------------------------
