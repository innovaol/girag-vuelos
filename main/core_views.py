# main/core_views.py

from django.shortcuts import render, redirect
from django.contrib.auth import authenticate, login, logout
from django.contrib.auth.decorators import login_required
from django.contrib.auth.forms import AuthenticationForm
from django.contrib import messages
from django.utils import timezone
from datetime import timedelta
from django.db.models import Count
from .models import Flight, Airline
from .forms.flight_forms import FlightReportForm  # ✅ Importamos el formulario correcto

# ✅ Login personalizado
def custom_login_view(request):
    if request.method == "POST":
        form = AuthenticationForm(request, data=request.POST)
        if form.is_valid():
            username = form.cleaned_data.get("username")
            password = form.cleaned_data.get("password")
            user = authenticate(request, username=username, password=password)

            if user is not None:
                # ✅ Limpiar mensajes anteriores
                storage = messages.get_messages(request)
                storage.used = True

                login(request, user)
                messages.success(request, f"Bienvenido, {user.username}!")
                return redirect("dashboard")
            else:
                messages.error(request, "Usuario o contraseña incorrectos.")  # ✅ Mostrar este siempre que las credenciales sean inválidas
        else:
            # ✅ Mostrar el mismo mensaje aunque el form no pase la validación
            messages.error(request, "Usuario o contraseña incorrectos.")
    else:
        form = AuthenticationForm()

    return render(request, "login.html", {"form": form})

# ✅ Cerrar sesión
def logout_view(request):
    logout(request)
    messages.success(request, "Sesión cerrada correctamente.")
    return redirect("login")

# ✅ Dashboard principal
@login_required
def dashboard(request):
    today = timezone.now().date()
    last_week = today - timedelta(days=7)

    total_flights = Flight.objects.count()
    flights_today = Flight.objects.filter(date=today).count()
    flights_last_7 = Flight.objects.filter(date__gte=last_week).count()

    form = FlightReportForm(request.GET or None)
    flights = Flight.objects.all()

    if form.is_valid():
        start_date = form.cleaned_data.get('start_date')
        end_date = form.cleaned_data.get('end_date')
        if start_date and end_date:
            flights = flights.filter(date__range=[start_date, end_date])

    last_week_flights = (
        Flight.objects.filter(date__gte=last_week, date__lte=today)
        .values('date')
        .annotate(total=Count('id'))
        .order_by('date')
    )

    chart_labels = [f['date'].strftime("%d/%m") for f in last_week_flights]
    chart_data = [f['total'] for f in last_week_flights]

    context = {
        "total_flights": total_flights,
        "flights_today": flights_today,
        "flights_last_7": flights_last_7,
        "chart_labels": chart_labels,
        "chart_data": chart_data,
        "flights": flights,
        "form": form,
    }

    return render(request, 'dashboard.html', context)
