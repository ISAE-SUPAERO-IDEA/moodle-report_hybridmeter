<?php

defined('MOODLE_INTERNAL') || die();

define("HYBRIDATION_COURS_TYPE_NAME", "cours");
define("HYBRIDATION_CATEGORIE_TYPE_NAME", "categorie");
define("MODULE_ASSIGN", "assign");
define("MODULE_ASSIGNMENT","assignment");
define("MODULE_BOOK","book");
define("MODULE_CHAT","chat");
define("MODULE_CHOICE","choice");
define("MODULE_DATA","data");
define("MODULE_FEEDBACK","feedback");
define("MODULE_FOLDER","folder");
define("MODULE_FORUM","forum");
define("MODULE_GLOSSARY","glossary");
define("MODULE_H5P","h5pactivity");
define("MODULE_IMSCP","imscp");
define("MODULE_LABEL","label");
define("MODULE_LESSON","lesson");
define("MODULE_LTI","lti");
define("MODULE_PAGE","page");
define("MODULE_QUIZ","quiz");
define("MODULE_RESOURCE","resource");
define("MODULE_SCORM","scorm");
define("MODULE_SURVEY","survey");
define("MODULE_URL","url");
define("MODULE_WIKI","wiki");
define("MODULE_WORKSHOP","workshop");
define("MODULE_QUESTIONNAIRE","questionnaire");
define("MODULE_NUGGET", "naas");

define("NA", "N/A");

define("NOW",strtotime("now"));

define("SEUIL_ACTIF", 5);

define("SEUIL_STATIQUE", 2);
define("SEUIL_DYNAMIQUE", 2);

define("NON_RUNNING", -1);

define("HYBRIDMETER_ACTIVITY_INSTANCES_DEVIATOR_CONSTANT", 2);
define("HYBRIDMETER_ACTIVITY_VARIETY_DEVIATOR_CONSTANT", 3);
define("HYBRIDMETER_ACTIVITY_TOTAL_INSTANCES_DEVIATOR_CONSTANT", 4);

define("DOUBLE", "double");
define("FLOAT", "float");

const FIELDS_TYPE = [
	'niveau_de_digitalisation' => DOUBLE,
	'niveau_d_utilisation' => DOUBLE
];

const FIELDS = [
	'id_moodle',
	'idnumber',
	'category_name',
	'fullname',
	'url',
	'niveau_de_digitalisation',
	'niveau_d_utilisation',
	'cours_actif',
	'nb_utilisateurs_actifs',
	'nb_inscrits',
	'date_debut_capture',
	'date_fin_capture',
];

const ALIAS = [
	'idnumber' => 'Identifiant du cours',
	'category_name' => "Catégorie",
	'fullname' => 'Nom du cours',
	'url' => 'URL du cours',
	'niveau_de_digitalisation' => 'ND',
	'niveau_d_utilisation' => 'NU',
	'cours_actif' => 'Cours actif durant la période mesurée',
	'nb_utilisateurs_actifs' => 'Nombre d\'apprenants actifs',
	'nb_inscrits' => 'Nombre d\'inscrits actuellement',
	'date_debut_capture' => 'Debut de la période de capture',
	'date_fin_capture' => 'Fin de la période de capture'
];

const COEFF_STATIQUES = [
	MODULE_ASSIGN => 4,
	MODULE_ASSIGNMENT => 4,
	MODULE_BOOK => 2,
	MODULE_CHAT => 5,
	MODULE_CHOICE => 1,
	MODULE_DATA => 5,
	MODULE_FEEDBACK => 3,
	MODULE_FOLDER => 2,
	MODULE_FORUM => 5,
	MODULE_GLOSSARY => 5,
	MODULE_H5P => 4,
	MODULE_IMSCP => 4,
	MODULE_LABEL => 2,
	MODULE_LESSON => 4, 
	MODULE_LTI => 4,
	MODULE_PAGE => 2,
	MODULE_QUIZ => 4,
	MODULE_RESOURCE => 2,
	MODULE_SCORM => 4,
	MODULE_SURVEY => 1,
	MODULE_URL => 2,
	MODULE_WIKI => 5,
	MODULE_WORKSHOP => 5,
	MODULE_QUESTIONNAIRE => 3,
	MODULE_NUGGET => 5
];

const COEFF_DYNAMIQUES = [
	MODULE_ASSIGN => 4,
	MODULE_ASSIGNMENT => 4,
	MODULE_BOOK => 2,
	MODULE_CHAT => 5,
	MODULE_CHOICE => 1,
	MODULE_DATA => 5,
	MODULE_FEEDBACK => 3,
	MODULE_FOLDER => 2,
	MODULE_FORUM => 5,
	MODULE_GLOSSARY => 5,
	MODULE_H5P => 4,
	MODULE_IMSCP => 4,
	MODULE_LABEL => 2,
	MODULE_LESSON => 4, 
	MODULE_LTI => 4,
	MODULE_PAGE => 2,
	MODULE_QUIZ => 4,
	MODULE_RESOURCE => 2,
	MODULE_SCORM => 4,
	MODULE_SURVEY => 1,
	MODULE_URL => 2,
	MODULE_WIKI => 5,
	MODULE_WORKSHOP => 5,
	MODULE_QUESTIONNAIRE => 3,
	MODULE_NUGGET => 5
];
