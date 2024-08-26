<?php
// This file is part of Moodle - http://moodle.org
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * English messages.
 * @author Nassim Bennouar, Bruno Ilponse
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2020  ISAE-SUPAERO (https://www.isae-supaero.fr/)
 * @package report_hybridmeter
 */

// Settings.
$string['pluginname'] = "HybridMeter";
$string['report/hybridmeter:all'] = "Access to HybridMeter";

$string["hybridmeter_settings"] = "Hybridmeter settings";
$string["hybridmeter_settings_help"] = "There are no settings for the hybridmeter plugin";

// In index.js or renderer.js.
$string['download_csv'] = "Download report";
$string['config'] = "Configuration";
$string['blacklistmanagement'] = "Configuration";

$string['recalculate'] = "Re-calculate";
$string['task_pending'] = "A task is pending";
$string['no_task_pending'] = "No task is pending";
$string['task_running'] = "A task is running";
$string['last_updated'] = "Last calculation : %s en %s";

$string['last_processing_results'] = "Results of the last processing";
$string['indicator_name'] = "Name of the indicator";
$string['number'] = "Number";
$string['nb_digitalised_courses'] =
    "Hybrid courses according to their level of digitalisation";
$string['nb_used_courses'] = "Hybrid courses according to their level of use";
$string['nb_students_concerned_digitalised'] =
    "Students enrolled in at least one hybrid course according to its level of digitalisation the ";
$string['nb_students_concerned_digitalised_active'] =
    "Active students in at least one hybrid course according to its level of digitalisation";
$string['nb_students_concerned_used'] =
    "Students enrolled in at least one hybrid course according to its level of use the";
$string['nb_students_concerned_used_active'] =
    "Active Students in at least one hybrid course according to its level of use";
$string['nb_analysed_courses'] = "Analysed courses";

$string['template_seconds'] = "%02d seconds";
$string['template_minutes_seconds'] = "%02d minutes %02d seconds";
$string['template_hours_minutes_seconds'] = "%02d hours %02d minutes %02d secondes";

$string['measurement_period_intro'] = "Measurement period:";
$string['measurement_period'] = "from %s to %s.";
$string['measurement_disclaimer'] = "Measures taken on older timestamps can vary depending of changes done since then (modification of courses content, registration, unregistration of students from courses and deletion of students from the platform)";
$string['end_processing'] = "Processing completed on %s.";
$string['processing_duration'] = "The processing lasted %s.";

$string['next_schedule'] = "Next scheduled calculation for %s at %s";
$string['no_schedule'] = "No programmed calculation";
$string['reschedule'] = "Reschedule the launch";
$string['unschedule'] = "Unschedule the launch";
$string['schedule'] = "Schedule the launch";
$string['successfully_unscheduled'] = "Launch successfully deprogrammed";

$string['documentation'] = "Documentation";
$string['changelog'] = "Change log";

// Diagnostics names.
$string['inconsistent_active_students'] = "Number of active students inconsistent";
$string['inconsistent_registered_active_students'] = "Number of active students inconsistent with enrolment";
$string['inconsistent_registered_students'] = "Inconsistent number of students enrolled";
$string['inconsistent_nd'] = "ND inconsistent";
$string['inconsistent_nu'] = "NU inconsistent";
$string['inconsistent_blacklist'] = "Blacklist inconsistent";

// In management page.
$string['module_name'] = "Module name";
$string['coefficient'] = "Coefficient";
$string['usage_coeff'] = "Usage coefficient";
$string['digitalisation_coeff'] = "Digitalisation coefficient";

$string['treshold_name'] = "Treshold name";
$string['treshold'] = "Treshold";
$string['treshold_value'] = "Treshold value";

$string['digitalisation_treshold'] = "Hybridization threshold according to the level of digitalisation";
$string['usage_treshold'] = "Hybridization threshold according to the level of usage";
$string['active_treshold'] = "Minimum number of active students to categorise a course as active";

$string['boxokstring'] = "The capture period has been successfully changed";
$string['boxnotokstring'] = "The period change did not work";

$string['blacklist_title'] = "Selection of courses/categories";
$string['period_title'] = "Capture period configuration";
$string['next_schedule_title'] = "Schedule next calculation";
$string['additional_config_title'] = "Additional configuration";
$string['coeff_value_title'] = "Value of coefficients";
$string['treshold_value_title'] = "Threshold values";
$string['coeff_digitalisation_title'] = "Digitalisation coefficients";
$string['coeff_using_title'] = "Usage coefficients";

$string['error_occured'] = "An error occured, please refresh the page and try again. Error code : %s";

$string['begin_date'] = "Beginning date";
$string['end_date'] = "End date";
$string['success_program'] = "Capture period successfully changed";
$string['error_begin_after_end'] = "Beginning date needs to be inferior to end date";

$string['scheduled_date'] = "Scheduled date";
$string['scheduled_time'] = "Scheduled time";
$string['tonight'] = "Tonight";
$string['this_weekend'] = "This week-end";
$string['schedule_submit'] = "Schedule";
$string['unschedule_submit'] = "Unschedule";
$string['success_schedule'] = "Calculation scheduled with success";
$string['success_unschedule'] = "Calculation unscheduled with success";
$string['error_past_schedule'] = "Scheduled date needs to be in the future";

$string['student_roles'] = "Student roles";
$string['student_roles_updated'] = "Data successfully updated";

$string['debug_mode'] = "Debug mode";

$string['blacklist'] = "Blacklist";
$string['whitelist'] = "Whitelist";
$string['x_category'] = "%s the category";
$string['x_course'] = "%s the course";
$string['diagnostic_course'] = "Get a diagnosis for the course";

$string['back_to_plugin'] = "Back to plugin";

$string['save_modif'] = "Save modifications";
