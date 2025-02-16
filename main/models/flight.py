from django.db import models
from django.contrib.auth.models import User
from main.models.airline import Airline

class Flight(models.Model):
    flight_number = models.CharField(max_length=50)
    date = models.DateField()
    airline = models.ForeignKey(Airline, on_delete=models.PROTECT)  # o SET_NULL, según prefieras
    created_by = models.ForeignKey(User, on_delete=models.SET_NULL, null=True)

    def __str__(self):
        return f"{self.flight_number} - {self.airline}"
