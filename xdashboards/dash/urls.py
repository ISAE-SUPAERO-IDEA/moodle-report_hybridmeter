from django.urls import path

from . import views

urlpatterns = [
    path('learners', views.learner, name='learners'),
    path('resources', views.resource, name='resources'),
    path('lms', views.lms, name='lms'),
    path('adn', views.adn, name='adn'),
    path('api/courses/search/<query>', views.api_search_course, name='api_search_course'),
    path('api/lms/summary', views.api_lms_summary, name='api_lms_summary')
]