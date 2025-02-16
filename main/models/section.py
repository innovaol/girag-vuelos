from django.db import models
from django.contrib.auth.models import Group

class Section(models.Model):
    name = models.CharField(max_length=100)
    code = models.CharField(max_length=100, unique=True)
    groups = models.ManyToManyField(Group, blank=True, related_name='sections')

    def __str__(self):
        return self.name
