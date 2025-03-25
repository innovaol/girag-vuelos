from django import template
from django.urls import reverse, NoReverseMatch
from main.models.navigation_section import NavigationSection
import logging

logger = logging.getLogger(__name__)

register = template.Library()

@register.simple_tag(takes_context=True)
def get_navigation_sections(context):
    user = context['user']
    # Obtener todas las secciones activas
    sections = NavigationSection.objects.filter(is_active=True)
    logger.debug("Todas las secciones activas: %s", [s.url_name for s in sections])
    
    # Si el usuario no es superusuario, filtrar por permisos
    if not user.is_superuser:
        user_perms = user.get_all_permissions()  # Ej.: {'main.view_manage_flights', ...}
        logger.debug("Permisos del usuario: %s", user_perms)
        allowed = []
        for section in sections:
            # Se espera que el permiso se llame "main.view_<url_name>"
            perm = "main.view_" + section.url_name
            logger.debug("Revisando sección '%s'. Permiso requerido: '%s'", section.url_name, perm)
            if perm in user_perms:
                allowed.append(section)
        sections = allowed

    # Filtrar aquellas secciones cuya URL se pueda invertir sin argumentos.
    filtered = []
    for section in sections:
        try:
            reverse(section.url_name)
            filtered.append(section)
        except NoReverseMatch:
            logger.debug("Se omite la sección '%s' por que no se puede invertir la URL", section.url_name)
            continue

    logger.debug("Secciones filtradas para mostrar en el menú: %s", [s.url_name for s in filtered])
    return filtered
