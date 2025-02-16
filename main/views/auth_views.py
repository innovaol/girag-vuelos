from django.shortcuts import render
from django.contrib.auth.decorators import login_required

@login_required
def unauthorized(request):
    return render(request, 'main/unauthorized.html')
