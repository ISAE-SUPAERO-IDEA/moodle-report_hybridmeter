from django.urls import path

from . import views

urlpatterns = [
    path('learners', views.learner, name='learners'),
    path('resources', views.resource, name='resources'),
]