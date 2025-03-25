from django import template

register = template.Library()

@register.simple_tag(takes_context=True)
def user_permissions_in_section(context, prefix):
    """
    Devuelve una lista de permisos (sin el prefijo "main.") que el usuario tenga
    y cuyo codename comience con el prefijo dado.
    Ejemplo: si prefix es "acceso_manage_flights", se devolverá algo como:
      ["acceso_manage_flights"]
    """
    request = context.get('request')
    if not request:
        return []
    user = request.user
    if not user.is_authenticated:
        return []
    user_perms = user.get_all_permissions()  # Ejemplo: {"main.acceso_manage_flights", "main.acceso_create_flight", ...}
    matching = [perm for perm in user_perms if perm.startswith("main." + prefix)]
    # Removemos el prefijo "main." para facilitar la comparación en la plantilla
    return [perm.replace("main.", "") for perm in matching]
