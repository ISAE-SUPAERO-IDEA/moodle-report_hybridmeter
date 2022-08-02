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
$string[REPORT_HYBRIDMETER_GENERAL_NB_STUDENTS_CONCERNED_DIGITALISED] = "Étudiants actuellement inscrits dans au moins un cours hybride selon l'indicateur du niveau de digitalisation";
$string[REPORT_HYBRIDMETER_GENERAL_NB_STUDENTS_CONCERNED_DIGITALISED_ACTIVE] = "Etudiants actifs sur la periode de capture dans au moins un cours hybride selon l'indicateur du niveau de digitalisation";
$string[REPORT_HYBRIDMETER_GENERAL_NB_STUDENTS_CONCERNED_USED] = "Étudiants actuellement inscrits dans au moins un cours hybride selon l'indicateur du niveau d'utilisation";
$string[REPORT_HYBRIDMETER_GENERAL_NB_STUDENTS_CONCERNED_USED_ACTIVE] = "Etudiants actifs sur la periode de capture dans au moins un cours hybride selon l'indicateur du niveau d'utilisation";
$string[REPORT_HYBRIDMETER_GENERAL_NB_ANALYSED_COURSES] = "Cours analysés";

$string['template_seconds'] = "%02d secondes";
$string['template_minutes_seconds'] = "%02d minutes %02d secondes";
$string['template_hours_minutes_seconds'] = "%02d heures %02d minutes %02d secondes";

$string['measurement_period'] = "Période de mesure : du %s au %s.";
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

// Dans configurator.php

$string['module_name'] = "Nom du module";
$string['coefficient'] = "Coefficient";

$string['treshold_name'] = "Nom du seuil";
$string['treshold_value'] = "Valeur du seuil";

$string['digitalisation_treshold'] = "Seuil d'hybridation selon le niveau de digitalisation";
$string['usage_treshold'] = "Seuil d'hybridation selon le niveau d'utilisation";
$string['active_treshold'] = "Nombre d'étudiants actifs minimum pour catégoriser un cours comme actif";

// Sur la page de management

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

$string['blacklist'] = "Blacklister";
$string['whitelist'] = "Whitelister";
$string['x_category'] = "%s la catégorie";
$string['x_course'] = "%s le cours";
$string['diagnostic_course'] = "Obtenir un diagnostic pour le cours";

$string['back_to_plugin'] = "Retour au plugin";

// On peut accéder à ces variables avec la fonction get_string('indice', 'report_hybridmeter');