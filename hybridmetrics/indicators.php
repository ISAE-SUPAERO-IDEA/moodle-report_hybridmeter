<?php

defined('MOODLE_INTERNAL') || die();

define("SEUIL_ACTIF", 5);
const COEFF_STATIQUES=[
	MODULE_ASSIGN => 1,
	MODULE_ASSIGNMENT => 4,
	MODULE_BOOK => 7,
	MODULE_CHAT => 4,
	MODULE_CHOICE => 4,
	MODULE_DATA => 6,
	MODULE_FEEDBACK => 6,
	MODULE_FOLDER => 0,
	MODULE_FORUM => 5,
	MODULE_GLOSSARY => 4,
	MODULE_H5P => 7,
	MODULE_IMSCP => 7,
	MODULE_LABEL => 1,
	MODULE_LESSON => 7, 
	MODULE_LTI => 7,
	MODULE_PAGE => 2,
	MODULE_QUIZ => 8,
	MODULE_RESOURCE => 2,
	MODULE_SCORM => 7,
	MODULE_SURVEY => 6,
	MODULE_URL => 2,
	MODULE_WIKI => 6,
	MODULE_WORKSHOP => 8,
];

const COEFF_DYNAMIQUES=[
	MODULE_ASSIGN => 1,
	MODULE_ASSIGNMENT => 4,
	MODULE_BOOK => 7,
	MODULE_CHAT => 4,
	MODULE_CHOICE => 4,
	MODULE_DATA => 6,
	MODULE_FEEDBACK => 6,
	MODULE_FOLDER => 0,
	MODULE_FORUM => 5,
	MODULE_GLOSSARY => 4,
	MODULE_H5P => 7,
	MODULE_IMSCP => 7,
	MODULE_LABEL => 1,
	MODULE_LESSON => 7, 
	MODULE_LTI => 7,
	MODULE_PAGE => 2,
	MODULE_QUIZ => 8,
	MODULE_RESOURCE => 2,
	MODULE_SCORM => 7,
	MODULE_SURVEY => 6,
	MODULE_URL => 2,
	MODULE_WIKI => 6,
	MODULE_WORKSHOP => 8,
];

//Fonction lambda utilisée pour calculer les indicateurs statiques
function hybridation_statique($object,$data,$parameters){
	fwrite($file, print_r(array($count, $parameters, $object), true));

	$count=$data->count_modules_types_id($object['id']);
	$indicator=0;
	foreach ($count as $key => $value){
		$indicator+=COEFF_STATIQUES[$key]*$value;
	}

	return ($indicator/$parameters['nb_cours']);
}


//Fonction lambda utilisée pour calculer les indicateurs dynamiques
function hybridation_dynamique($object,$data,$parameters){
	$active=$data->count_active_single_users($object['id']);
	$indicator=0;
	if($active==0) return 0;
	foreach (COEFF_DYNAMIQUES as $key => $value){
		$count=$data->count_hits_by_module_type($object['id'],$key);
		$indicator+=$value*($count/$active);
	}
	return ($indicator/$parameters['nb_cours']);
}

//Fonction lambda utilisée pour définir si le cours est actif
function is_course_active_last_month($object, $data, $parameters){
	$count=$data->count_single_users_course_viewed($object['id'],strtotime("-1 month"),strtotime("now"));
	if ($count >= SEUIL_ACTIF)
		return 1;
	else
		return 0;
}