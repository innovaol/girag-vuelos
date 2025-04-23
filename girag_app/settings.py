#/home/innovaol/girapp/girag_app/settings.py

import os
from pathlib import Path
import environ

# -----------------------------------------------------------------------------------
# Definir BASE_DIR usando Path (una única vez)
# -----------------------------------------------------------------------------------
BASE_DIR = Path(__file__).resolve().parent.parent

# -----------------------------------------------------------------------------------
# Inicializar django-environ y leer el archivo .env
# -----------------------------------------------------------------------------------
env = environ.Env(
    DEBUG=(bool, False)
)
environ.Env.read_env(os.path.join(BASE_DIR, '.env'))

# -----------------------------------------------------------------------------------
# Crear la carpeta logs si no existe
# -----------------------------------------------------------------------------------
LOG_DIR = os.path.join(str(BASE_DIR), 'logs')
os.makedirs(LOG_DIR, exist_ok=True)

# -----------------------------------------------------------------------------------
# Configuración de clave secreta, debug, hosts, etc.
# -----------------------------------------------------------------------------------
SECRET_KEY = 'django-insecure-xxxxxxxxxxxxxxxxxxxxxxx'
DEBUG = False
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
# Configuración de Base de Datos (MySQL)
# -----------------------------------------------------------------------------------
DATABASES = {
    'default': {
        'ENGINE': 'django.db.backends.mysql',
        'NAME': env('DB_NAME'),
        'USER': env('DB_USER'),
        'PASSWORD': env('DB_PASSWORD'),
        'HOST': env('DB_HOST'),
        'PORT': env('DB_PORT'),
        'OPTIONS': {
            'init_command': "SET sql_mode='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'"
        }
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
MEDIA_ROOT = os.path.join(str(BASE_DIR), 'media')

# -----------------------------------------------------------------------------------
# Internacionalización
# -----------------------------------------------------------------------------------
LANGUAGE_CODE = 'es'
TIME_ZONE = 'America/Panama'
USE_I18N = True
USE_L10N = False  # 🔥 IMPORTANTE: Desactiva la localización automática de Django
USE_TZ = True

DATE_FORMAT = 'd/m/Y'
DATE_INPUT_FORMATS = ['%d/%m/%Y']
DATETIME_FORMAT = 'd/m/Y H:i'
DATETIME_INPUT_FORMATS = ['%d/%m/%Y %H:%M']

# -----------------------------------------------------------------------------------
# Redirección al login y logout (✅ Login personalizado)
# -----------------------------------------------------------------------------------
LOGIN_URL = '/login/'
LOGIN_REDIRECT_URL = 'dashboard'
LOGOUT_REDIRECT_URL = '/login/'

# -----------------------------------------------------------------------------------
# Configuración de URL Base para Notificaciones y Enlaces Completos
# -----------------------------------------------------------------------------------
SITE_URL = "https://vuelos.innovaol.com"

# -----------------------------------------------------------------------------------
# Backend para que se utilicen solamente los permisos personalizados y no los de Django
# -----------------------------------------------------------------------------------
AUTHENTICATION_BACKENDS = [
    "main.backends.custom_backend.CustomGroupBackend",
    "django.contrib.auth.backends.ModelBackend",  # Opcional si aún usas permisos individuales
]

# -----------------------------------------------------------------------------------
# Configuración de Correo (SMTP)
# -----------------------------------------------------------------------------------
EMAIL_BACKEND = 'django.core.mail.backends.smtp.EmailBackend'
EMAIL_HOST = 'localhost'
EMAIL_PORT = 25
EMAIL_USE_TLS = False
EMAIL_USE_SSL = False
EMAIL_HOST_USER = 'test@innovaol.com'
EMAIL_HOST_PASSWORD = os.getenv("EMAIL_CONTRASENA")
DEFAULT_FROM_EMAIL = 'test@innovaol.com'

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
            'filename': os.path.join(LOG_DIR, 'debug.log'),
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
