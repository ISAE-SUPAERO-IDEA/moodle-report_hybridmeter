from django.urls import path


from . import views, export_views

urlpatterns = [
    path('learners', views.learner, name='learners'),
    path('resources', views.resource, name='resources'),
    path('lms', views.lms, name='lms'),
    path('lms/path', views.path, name='path'),
    path('adn', views.adn, name='adn'),
    path('api/courses/search/<query>', views.api_search_course, name='api_search_course'),
    path('api/lms/summary', export_views.api_lms_summary, name='api_lms_summary'),
    path('api/lms/traces', export_views.api_lms_traces, name='api_lms_traces'),
    path('api/lms/merged_traces', export_views.api_lms_merged_traces, name='api_lms_merged_traces'),
]