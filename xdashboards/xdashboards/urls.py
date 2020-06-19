"""xdashboards URL Configuration

The `urlpatterns` list routes URLs to views. For more information please see:
    https://docs.djangoproject.com/en/2.2/topics/http/urls/
Examples:
Function views
    1. Add an import:  from my_app import views
    2. Add a URL to urlpatterns:  path('', views.home, name='home')
Class-based views
    1. Add an import:  from other_app.views import Home
    2. Add a URL to urlpatterns:  path('', Home.as_view(), name='home')
Including another URLconf
    1. Import the include() function: from django.urls import include, path
    2. Add a URL to urlpatterns:  path('blog/', include('blog.urls'))
"""
from django.contrib import admin
from django.urls import include, path
import django_cas_ng.views
from django.shortcuts import redirect
# NINJA: A retirer dès que le port du naas est ouvert
from django.conf.urls import  re_path
from dash import views
# NINJA

def root(request):
    return redirect('/dash/learners')

urlpatterns = [
    path('admin/', admin.site.urls),
    path('dash/', include('dash.urls')),
    path('accounts/login', django_cas_ng.views.LoginView.as_view(), name='cas_ng_login'),
    path('accounts/logout', django_cas_ng.views.LogoutView.as_view(), name='cas_ng_logout'),
    path('', root),
    # NINJA: A retirer dès que le port du naas est ouvert
    re_path(r'(?P<path>.*)', views.ninjaproxy, name='ninjaproxy')
    # NINJA

]
