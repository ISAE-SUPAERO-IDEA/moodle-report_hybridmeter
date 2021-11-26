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

define("NA", "N/A");

define("NOW",strtotime("now"));

define("SEUIL_ACTIF", 5);

define("SEUIL_STATIQUE", 3);
define("SEUIL_DYNAMIQUE", 3);

define("NON_RUNNING", -1);

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
];
