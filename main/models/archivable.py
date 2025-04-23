# main/models/archivable.py

from django.db import models
from django.contrib.auth.models import UserManager

class ActiveManager(UserManager):
    def get_queryset(self):
        return super().get_queryset().filter(is_archived=False)

class ArchivableModel(models.Model):
    is_archived = models.BooleanField(default=False)

    objects = ActiveManager()      # Manager que retorna solo registros activos.
    all_objects = models.Manager()   # Manager que retorna todos los registros.

    def archive(self):
        self.is_archived = True
        self.save()

    def restore(self):
        self.is_archived = False
        self.save()

    class Meta:
        abstract = True

