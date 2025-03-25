from django.db import models

class NavigationSection(models.Model):
    url_name = models.CharField(max_length=255, unique=True)  # Debe coincidir con el nombre de URL en urls.py
    display_name = models.CharField(max_length=255)           # Texto que se muestra en el menú
    is_active = models.BooleanField(default=True)             # Indica si la sección está activa

    def __str__(self):
        return self.display_name
