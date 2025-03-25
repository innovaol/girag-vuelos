from django.contrib.auth.models import User, Group

def check_username(username):
    """Verifica si un nombre de usuario ya está en uso."""
    return not User.objects.filter(username=username).exists()

def check_email(email):
    """Verifica si un correo electrónico ya está en uso."""
    return not User.objects.filter(email=email).exists()

def check_group_name(group_name):
    """Verifica si un nombre de grupo ya existe."""
    return not Group.objects.filter(name=group_name).exists()
