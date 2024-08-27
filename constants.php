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
 * Plugin constants.
 *
 * @author Nassim Bennouar
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2020  ISAE-SUPAERO (https://www.isae-supaero.fr/)
 * @package report_hybridmeter
 */
define("REPORT_HYBRIDMETER_HYBRIDATION_COURS_TYPE_NAME", "cours");
define("REPORT_HYBRIDMETER_HYBRIDATION_CATEGORIE_TYPE_NAME", "categorie");
define("REPORT_HYBRIDMETER_MODULE_ASSIGN", "assign");
define("REPORT_HYBRIDMETER_MODULE_ASSIGNMENT", "assignment");
define("REPORT_HYBRIDMETER_MODULE_BOOK", "book");
define("REPORT_HYBRIDMETER_MODULE_CHAT", "chat");
define("REPORT_HYBRIDMETER_MODULE_CHOICE", "choice");
define("REPORT_HYBRIDMETER_MODULE_DATA", "data");
define("REPORT_HYBRIDMETER_MODULE_FEEDBACK", "feedback");
define("REPORT_HYBRIDMETER_MODULE_FOLDER", "folder");
define("REPORT_HYBRIDMETER_MODULE_FORUM", "forum");
define("REPORT_HYBRIDMETER_MODULE_GLOSSARY", "glossary");
define("REPORT_HYBRIDMETER_MODULE_H5P", "h5pactivity");
define("REPORT_HYBRIDMETER_MODULE_IMSCP", "imscp");
define("REPORT_HYBRIDMETER_MODULE_LABEL", "label");
define("REPORT_HYBRIDMETER_MODULE_LESSON", "lesson");
define("REPORT_HYBRIDMETER_MODULE_LTI", "lti");
define("REPORT_HYBRIDMETER_MODULE_PAGE", "page");
define("REPORT_HYBRIDMETER_MODULE_QUIZ", "quiz");
define("REPORT_HYBRIDMETER_MODULE_RESOURCE", "resource");
define("REPORT_HYBRIDMETER_MODULE_SCORM", "scorm");
define("REPORT_HYBRIDMETER_MODULE_SURVEY", "survey");
define("REPORT_HYBRIDMETER_MODULE_URL", "url");
define("REPORT_HYBRIDMETER_MODULE_WIKI", "wiki");
define("REPORT_HYBRIDMETER_MODULE_WORKSHOP", "workshop");
define("REPORT_HYBRIDMETER_MODULE_QUESTIONNAIRE", "questionnaire");
define("REPORT_HYBRIDMETER_MODULE_NUGGET", "naas");

define("REPORT_HYBRIDMETER_NA", "N/A");

define("REPORT_HYBRIDMETER_ACTIVE_TRESHOLD", 5);

define("REPORT_HYBRIDMETER_DIGITALISATION_TRESHOLD", 2);
define("REPORT_HYBRIDMETER_USAGE_TRESHOLD", 2);

define("REPORT_HYBRIDMETER_NON_RUNNING", -1);

define("REPORT_HYBRIDMETER_ACTIVITY_INSTANCES_DEVIATOR_CONSTANT", 2);
define("REPORT_HYBRIDMETER_ACTIVITY_VARIETY_DEVIATOR_CONSTANT", 3);
define("REPORT_HYBRIDMETER_ACTIVITY_TOTAL_INSTANCES_DEVIATOR_CONSTANT", 4);

define("REPORT_HYBRIDMETER_DOUBLE", "double");
define("REPORT_HYBRIDMETER_FLOAT", "float");

define("REPORT_HYBRIDMETER_FIELD_FULLNAME", "fullname");
define("REPORT_HYBRIDMETER_FIELD_CATEGORY_NAME", "category_name");
define("REPORT_HYBRIDMETER_FIELD_DIGITALISATION_LEVEL", "digitalisation_level");
define("REPORT_HYBRIDMETER_FIELD_USAGE_LEVEL", "usage_level");
define("REPORT_HYBRIDMETER_FIELD_ACTIVE_COURSE", "active_course");
define("REPORT_HYBRIDMETER_FIELD_NB_ACTIVE_USERS", "nb_active_users");
define("REPORT_HYBRIDMETER_FIELD_ID_MOODLE", "id_moodle");
define("REPORT_HYBRIDMETER_FIELD_CATEGORY_PATH", "category_path");
define("REPORT_HYBRIDMETER_FIELD_ID_NUMBER", "id_number");
define("REPORT_HYBRIDMETER_FIELD_URL", "url");
define("REPORT_HYBRIDMETER_FIELD_NB_REGISTERED_STUDENTS", "nb_registered_students");
define("REPORT_HYBRIDMETER_FIELD_BEGIN_DATE", "begin_date_capture");
define("REPORT_HYBRIDMETER_FIELD_END_DATE", "end_date_capture");

define("REPORT_HYBRIDMETER_GENERAL_DIGITALISED_COURSES", "digitalised_courses");
define("REPORT_HYBRIDMETER_GENERAL_USED_COURSES", "used_courses");
define("REPORT_HYBRIDMETER_GENERAL_IDS_DIGITALISED_COURSES", "ids_digitalised_courses");
define("REPORT_HYBRIDMETER_GENERAL_IDS_USED_COURSES", "ids_used_courses");
define("REPORT_HYBRIDMETER_GENERAL_NB_DIGITALISED_COURSES", "nb_digitalised_courses");
define("REPORT_HYBRIDMETER_GENERAL_NB_USED_COURSES", "nb_used_courses");
define("REPORT_HYBRIDMETER_GENERAL_NB_STUDENTS_CONCERNED_DIGITALISED", "nb_students_concerned_digitalised");
define("REPORT_HYBRIDMETER_GENERAL_NB_STUDENTS_CONCERNED_DIGITALISED_ACTIVE", "nb_students_concerned_digitalised_active");
define("REPORT_HYBRIDMETER_GENERAL_NB_STUDENTS_CONCERNED_USED", "nb_students_concerned_used");
define("REPORT_HYBRIDMETER_GENERAL_NB_STUDENTS_CONCERNED_USED_ACTIVE", "nb_students_concerned_used_active");
define("REPORT_HYBRIDMETER_GENERAL_BEGIN_CAPTURE_TIMESTAMP", "begin_capture_timestamp");
define("REPORT_HYBRIDMETER_GENERAL_END_CAPTURE_DATE", "end_capture_timestamp");
define("REPORT_HYBRIDMETER_GENERAL_NB_ANALYSED_COURSES", "nb_analysed_courses");


/**
 * Associate a field to its type (for rendering format).
 */
const REPORT_HYBRIDMETER_FIELDS_TYPE = [
    REPORT_HYBRIDMETER_FIELD_DIGITALISATION_LEVEL => REPORT_HYBRIDMETER_DOUBLE,
    REPORT_HYBRIDMETER_FIELD_USAGE_LEVEL => REPORT_HYBRIDMETER_DOUBLE,
];

/**
 * Report fields
 */
const REPORT_HYBRIDMETER_FIELDS = [
    REPORT_HYBRIDMETER_FIELD_ID_MOODLE,
    REPORT_HYBRIDMETER_FIELD_ID_NUMBER,
    REPORT_HYBRIDMETER_FIELD_CATEGORY_NAME,
    REPORT_HYBRIDMETER_FIELD_CATEGORY_PATH,
    REPORT_HYBRIDMETER_FIELD_FULLNAME,
    REPORT_HYBRIDMETER_FIELD_URL,
    REPORT_HYBRIDMETER_FIELD_DIGITALISATION_LEVEL,
    REPORT_HYBRIDMETER_FIELD_USAGE_LEVEL,
    REPORT_HYBRIDMETER_FIELD_ACTIVE_COURSE,
    REPORT_HYBRIDMETER_FIELD_NB_ACTIVE_USERS,
    REPORT_HYBRIDMETER_FIELD_NB_REGISTERED_STUDENTS,
    REPORT_HYBRIDMETER_FIELD_BEGIN_DATE,
    REPORT_HYBRIDMETER_FIELD_END_DATE,
];

/**
 * Fields displayed label (note: this should be i18ned).
 */
const REPORT_HYBRIDMETER_ALIAS = [
    REPORT_HYBRIDMETER_FIELD_ID_NUMBER => 'Identifiant du cours',
    REPORT_HYBRIDMETER_FIELD_CATEGORY_NAME => 'Catégorie',
    REPORT_HYBRIDMETER_FIELD_CATEGORY_PATH => 'Chemin de la catégorie',
    REPORT_HYBRIDMETER_FIELD_FULLNAME => 'Nom du cours',
    REPORT_HYBRIDMETER_FIELD_URL => 'URL du cours',
    REPORT_HYBRIDMETER_FIELD_DIGITALISATION_LEVEL => 'ND',
    REPORT_HYBRIDMETER_FIELD_USAGE_LEVEL => 'NU',
    REPORT_HYBRIDMETER_FIELD_ACTIVE_COURSE => 'Cours actif durant la période mesurée',
    REPORT_HYBRIDMETER_FIELD_NB_ACTIVE_USERS => 'Nombre d\'apprenants actifs',
    REPORT_HYBRIDMETER_FIELD_NB_REGISTERED_STUDENTS => 'Nombre d\'inscrits actuellement',
    REPORT_HYBRIDMETER_FIELD_BEGIN_DATE => 'Debut de la période de capture',
    REPORT_HYBRIDMETER_FIELD_END_DATE => 'Fin de la période de capture',
];

/**
 * Coefficient to be applied to each type of module to calculate the "digitalization" indicator
 */
const REPORT_HYBRIDMETER_DIGITALISATION_COEFFS = [
    REPORT_HYBRIDMETER_MODULE_ASSIGN => 4,
    REPORT_HYBRIDMETER_MODULE_ASSIGNMENT => 4,
    REPORT_HYBRIDMETER_MODULE_BOOK => 2,
    REPORT_HYBRIDMETER_MODULE_CHAT => 5,
    REPORT_HYBRIDMETER_MODULE_CHOICE => 1,
    REPORT_HYBRIDMETER_MODULE_DATA => 5,
    REPORT_HYBRIDMETER_MODULE_FEEDBACK => 3,
    REPORT_HYBRIDMETER_MODULE_FOLDER => 2,
    REPORT_HYBRIDMETER_MODULE_FORUM => 5,
    REPORT_HYBRIDMETER_MODULE_GLOSSARY => 5,
    REPORT_HYBRIDMETER_MODULE_H5P => 4,
    REPORT_HYBRIDMETER_MODULE_IMSCP => 4,
    REPORT_HYBRIDMETER_MODULE_LABEL => 2,
    REPORT_HYBRIDMETER_MODULE_LESSON => 4,
    REPORT_HYBRIDMETER_MODULE_LTI => 4,
    REPORT_HYBRIDMETER_MODULE_PAGE => 2,
    REPORT_HYBRIDMETER_MODULE_QUIZ => 4,
    REPORT_HYBRIDMETER_MODULE_RESOURCE => 2,
    REPORT_HYBRIDMETER_MODULE_SCORM => 4,
    REPORT_HYBRIDMETER_MODULE_SURVEY => 1,
    REPORT_HYBRIDMETER_MODULE_URL => 2,
    REPORT_HYBRIDMETER_MODULE_WIKI => 5,
    REPORT_HYBRIDMETER_MODULE_WORKSHOP => 5,
    REPORT_HYBRIDMETER_MODULE_QUESTIONNAIRE => 3,
    REPORT_HYBRIDMETER_MODULE_NUGGET => 5,
];

/**
 * Coefficient to be applied to each type of module to calculate the "usage" indicator
 */
const REPORT_HYBRIDMETER_USAGE_COEFFS = [
    REPORT_HYBRIDMETER_MODULE_ASSIGN => 4,
    REPORT_HYBRIDMETER_MODULE_ASSIGNMENT => 4,
    REPORT_HYBRIDMETER_MODULE_BOOK => 2,
    REPORT_HYBRIDMETER_MODULE_CHAT => 5,
    REPORT_HYBRIDMETER_MODULE_CHOICE => 1,
    REPORT_HYBRIDMETER_MODULE_DATA => 5,
    REPORT_HYBRIDMETER_MODULE_FEEDBACK => 3,
    REPORT_HYBRIDMETER_MODULE_FOLDER => 2,
    REPORT_HYBRIDMETER_MODULE_FORUM => 5,
    REPORT_HYBRIDMETER_MODULE_GLOSSARY => 5,
    REPORT_HYBRIDMETER_MODULE_H5P => 4,
    REPORT_HYBRIDMETER_MODULE_IMSCP => 4,
    REPORT_HYBRIDMETER_MODULE_LABEL => 2,
    REPORT_HYBRIDMETER_MODULE_LESSON => 4,
    REPORT_HYBRIDMETER_MODULE_LTI => 4,
    REPORT_HYBRIDMETER_MODULE_PAGE => 2,
    REPORT_HYBRIDMETER_MODULE_QUIZ => 4,
    REPORT_HYBRIDMETER_MODULE_RESOURCE => 2,
    REPORT_HYBRIDMETER_MODULE_SCORM => 4,
    REPORT_HYBRIDMETER_MODULE_SURVEY => 1,
    REPORT_HYBRIDMETER_MODULE_URL => 2,
    REPORT_HYBRIDMETER_MODULE_WIKI => 5,
    REPORT_HYBRIDMETER_MODULE_WORKSHOP => 5,
    REPORT_HYBRIDMETER_MODULE_QUESTIONNAIRE => 3,
    REPORT_HYBRIDMETER_MODULE_NUGGET => 5,
];
