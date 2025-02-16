import os
import sys

# Agrega la ruta completa del proyecto al sys.path
sys.path.insert(0, '/home/innovaol/AppVuelos')

# Establece el modulo de settings de Django
os.environ.setdefault('DJANGO_SETTINGS_MODULE', 'girag_app.settings')

from django.core.wsgi import get_wsgi_application
application = get_wsgi_application()
