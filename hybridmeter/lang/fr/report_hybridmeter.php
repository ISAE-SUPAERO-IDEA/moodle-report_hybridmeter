<?php

require_once(__DIR__.'/../../constants.php');

// Configuration

$string['pluginname'] = "HybridMeter";

$string["hybridmeter_settings"] = "Paramètres hybridmeter";
$string["hybridmeter_settings_help"] = "Il n'y a pas de paramètres pour le plugin hybridmeter";

// Dans index.php ou renderer.php

$string['download_csv'] = "Télécharger le compte rendu";
$string['config'] = "Configuration";
$string['blacklistmanagement'] = "Configuration";

$string['recalculate'] = "Re-calculer";
$string['task_pending'] = "Une tâche est prête à être lancée";
$string['no_task_pending'] = "Il n'y a pas de tâche prête à être lancée";
$string['task_running'] = "Un traitement est en cours d'exécution";
$string['last_updated'] = "Dernier calcul : %s en %s";

$string['last_processing_results'] = "Résultats du dernier traitement";
$string['indicator_name'] = "Nom de l'indicateur";
$string['number'] = "Nombre";
$string[REPORT_HYBRIDMETER_GENERAL_NB_DIGITALISED_COURSES] = "Cours hybrides selon l'indicateur du niveau de digitalisation";
$string[REPORT_HYBRIDMETER_GENERAL_NB_USED_COURSES] = "Cours hybrides selon l'indicateur du niveau d'utilisation";
$string[REPORT_HYBRIDMETER_GENERAL_NB_STUDENTS_CONCERNED_DIGITALISED] = "Étudiants inscrits dans au moins un cours hybride selon l'indicateur du niveau de digitalisation en date du";
$string[REPORT_HYBRIDMETER_GENERAL_NB_STUDENTS_CONCERNED_DIGITALISED_ACTIVE] = "Etudiants actifs dans au moins un cours hybride selon l'indicateur du niveau de digitalisation";
$string[REPORT_HYBRIDMETER_GENERAL_NB_STUDENTS_CONCERNED_USED] = "Étudiants inscrits dans au moins un cours hybride selon l'indicateur du niveau d'utilisation en date du";
$string[REPORT_HYBRIDMETER_GENERAL_NB_STUDENTS_CONCERNED_USED_ACTIVE] = "Etudiants actifs dans au moins un cours hybride selon l'indicateur du niveau d'utilisation";
$string[REPORT_HYBRIDMETER_GENERAL_NB_ANALYSED_COURSES] = "Cours analysés";

$string['template_seconds'] = "%02d secondes";
$string['template_minutes_seconds'] = "%02d minutes %02d secondes";
$string['template_hours_minutes_seconds'] = "%02d heures %02d minutes %02d secondes";

$string['measurement_period_intro'] = "Période de mesure : ";
$string['measurement_period'] = "du %s au %s.";
$string['measurement_disclaimer'] = "Les mesures portant sur les périodes passées peuvent varier en fonction des changements effectués depuis (modification du contenu des cours, inscription/désinscription des étudiants aux cours et suppression des étudiants de la plateforme)";
$string['end_processing'] = "Traitement terminé le %s.";
$string['processing_duration'] = "Le traitement a duré %s."; 

$string['next_schedule'] = "Prochain calcul programmé pour le %s à %s";
$string['no_schedule'] = "Pas de calcul programmé";
$string['reschedule'] = "Reprogrammer le lancement";
$string['unschedule'] = "Déprogrammer le lancement";
$string['schedule'] = "Programmer le lancement";
$string['successfully_unscheduled'] = "Lancement déprogrammé avec succès";

$string['documentation'] = "Documentation";
$string['changelog'] = "Change log";

// Noms des diagnostics

$string['inconsistent_active_students'] = "Nombre d'étudiants actifs incohérent";
$string['inconsistent_registered_active_students'] = "Nombre d'étudiants actifs incohérent par rapport au nombre d'inscrits";
$string['inconsistent_registered_students'] = "Nombre d'étudiants inscrits incohérent";
$string['inconsistent_nd'] = "ND incohérent";
$string['inconsistent_nu'] = "NU incohérent";
$string['inconsistent_blacklist'] = "Blacklist incohérent";

// Sur la page de management

$string['module_name'] = "Nom du module";
$string['coefficient'] = "Coefficient";
$string['usage_coeff'] = "Coefficient d'usage";
$string['digitalisation_coeff'] = "Coefficient de digitalisation";

$string['treshold_name'] = "Nom du seuil";
$string['treshold'] = "Seuil";
$string['treshold_value'] = "Valeur du seuil";

$string['digitalisation_treshold'] = "Seuil d'hybridation selon le niveau de digitalisation";
$string['usage_treshold'] = "Seuil d'hybridation selon le niveau d'utilisation";
$string['active_treshold'] = "Nombre d'étudiants actifs minimum pour catégoriser un cours comme actif";

$string['boxokstring'] = "La période de capture a été changée avec succès";
$string['boxnotokstring'] = "Le changement de période n'a pas fonctionné";

$string['blacklist_title'] = "Sélection des cours/catégories";
$string['period_title'] = "Paramétrage de la période de la capture";
$string['next_schedule_title'] = "Prochain lancement";
$string['additional_config_title'] = "Configuration additionnelle";
$string['coeff_value_title'] = "Valeur des coefficients";
$string['treshold_value_title'] = "Valeur des seuils";
$string['coeff_digitalisation_title'] = "Coefficients de digitalisation";
$string['coeff_using_title'] = "Coefficients d'utilisation";

$string['error_occured'] = "Une erreur s'est produite, veuillez rafraîchir la page et réessayer. Code erreur : %s";

$string['begin_date'] = "Date de début";
$string['end_date'] = "Date de fin";
$string['success_program'] = "La période de capture a bien été mise à jour";
$string['error_begin_after_end'] = "La date de début de période doit être inférieure à la date de fin de période";

$string['scheduled_date'] = "Date de lancement";
$string['scheduled_time'] = "Heure de lancement";
$string['tonight'] = "Cette nuit";
$string['this_weekend'] = "Ce week-end";
$string['schedule_submit'] = "Programmer le lancement";
$string['unschedule_submit'] = "Déprogrammer le lancement";
$string['success_schedule'] = "Lancement programmé avec succès";
$string['success_unschedule'] = "Lancement déprogrammé avec succès";
$string['error_past_schedule'] = "La date de lancement soumise est dans le passé";

$string['student_archetype'] = "Archetype du rôle étudiant";
$string['student_archetype_updated'] = "Les données ont bien été mises à jour";

$string['debug_mode'] = "Mode debug";

$string['blacklist'] = "Blacklister";
$string['whitelist'] = "Whitelister";
$string['x_category'] = "%s la catégorie";
$string['x_course'] = "%s le cours";
$string['diagnostic_course'] = "Obtenir un diagnostic pour le cours";

$string['back_to_plugin'] = "Retour au plugin";

$string['save_modif'] = "Enregistrer les modifications";

// On peut accéder à ces variables avec la fonction get_string('indice', 'report_hybridmeter');