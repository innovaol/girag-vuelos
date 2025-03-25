import os
import sys
from django.core.wsgi import get_wsgi_application

# Añade la ruta base a sys.path para que Python ubique tu paquete "girag_app" y "main"
BASE_DIR = "/home/innovaol/girapp"  # Ajusta según necesites
if BASE_DIR not in sys.path:
    sys.path.append(BASE_DIR)

os.environ.setdefault("DJANGO_SETTINGS_MODULE", "girag_app.settings")

application = get_wsgi_application()
