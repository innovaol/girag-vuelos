from django.core.management.base import BaseCommand
from django.urls import URLPattern, URLResolver, get_resolver
from main.models.navigation_section import NavigationSection

def extract_patterns(urlpatterns, collected=None):
    if collected is None:
        collected = []

    for pattern in urlpatterns:
        if isinstance(pattern, URLPattern) and pattern.name:
            collected.append(pattern)
        elif isinstance(pattern, URLResolver):
            extract_patterns(pattern.url_patterns, collected)
    return collected

class Command(BaseCommand):
    help = 'Escanea TODAS las URLs (incluyendo anidadas) y sincroniza con NavigationSection'

    def handle(self, *args, **options):
        resolver = get_resolver()
        all_patterns = extract_patterns(resolver.url_patterns)
        new_count = 0

        for pattern in all_patterns:
            obj, created = NavigationSection.objects.get_or_create(
                url_name=pattern.name,
                defaults={'display_name': pattern.name.replace('_', ' ').capitalize(), 'is_active': False}
            )
            if created:
                new_count += 1

        self.stdout.write(self.style.SUCCESS(f'Se sincronizaron {new_count} nuevas secciones desde las URLs registradas.'))
