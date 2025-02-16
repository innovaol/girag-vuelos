# girag_app/settings.py

import os
from pathlib import Path

# -----------------------------------------------------------------------------------
# Rutas base
# -----------------------------------------------------------------------------------
BASE_DIR = Path(__file__).resolve().parent.parent

# -----------------------------------------------------------------------------------
# Clave secreta (cambia este valor para tu proyecto)
# -----------------------------------------------------------------------------------
SECRET_KEY = 'django-insecure-xxxxxxxxxxxxxxxxxxxxxxx'

# -----------------------------------------------------------------------------------
# Depuración (poner en False en producción)
# -----------------------------------------------------------------------------------
DEBUG = True

# -----------------------------------------------------------------------------------
# Hosts permitidos
# -----------------------------------------------------------------------------------
ALLOWED_HOSTS = ["vuelos.innovaol.com", "localhost", "127.0.0.1"]

# -----------------------------------------------------------------------------------
# Aplicaciones instaladas
# -----------------------------------------------------------------------------------
INSTALLED_APPS = [
    'django.contrib.admin',
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
    'django.contrib.sessions.middleware.SessionMiddleware',
    'django.middleware.common.CommonMiddleware',
    'django.middleware.csrf.CsrfViewMiddleware',
    'django.contrib.auth.middleware.AuthenticationMiddleware',
    'django.contrib.messages.middleware.MessageMiddleware',
    'django.middleware.clickjacking.XFrameOptionsMiddleware',
]

# -----------------------------------------------------------------------------------
# URLs raíz y WSGI
# -----------------------------------------------------------------------------------
ROOT_URLCONF = 'girag_app.urls'
WSGI_APPLICATION = 'girag_app.wsgi.application'

# -----------------------------------------------------------------------------------
# Configuración de Base de Datos (SQLite)
# -----------------------------------------------------------------------------------
DATABASES = {
    'default': {
        'ENGINE': 'django.db.backends.sqlite3',
        'NAME': BASE_DIR / 'db.sqlite3',
    }
}

# -----------------------------------------------------------------------------------
# Configuración de plantillas
# -----------------------------------------------------------------------------------
TEMPLATES = [
    {
        'BACKEND': 'django.template.backends.django.DjangoTemplates',
        # Django buscará plantillas en /home/innovaol/AppVuelos/main/templates/main
        'DIRS': [BASE_DIR / 'main' / 'templates' / 'main'],
        'APP_DIRS': True,
        'OPTIONS': {
            'context_processors': [
                'django.template.context_processors.debug',
                'django.template.context_processors.request',
                'django.contrib.auth.context_processors.auth',
                'django.contrib.messages.context_processors.messages',
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
LANGUAGE_CODE = 'es'
TIME_ZONE = 'UTC'
USE_I18N = True
USE_L10N = True
USE_TZ = True

# -----------------------------------------------------------------------------------
# Redirección al login y logout
# -----------------------------------------------------------------------------------
LOGIN_REDIRECT_URL = 'dashboard'
LOGOUT_REDIRECT_URL = '/accounts/login/'

# -----------------------------------------------------------------------------------
# Fin de settings
# -----------------------------------------------------------------------------------
