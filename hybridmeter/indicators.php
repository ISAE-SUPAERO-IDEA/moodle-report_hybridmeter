<?php
require_once(__DIR__.'/constants.php');
require_once(__DIR__.'/classes/configurator.php');
require_once(__DIR__.'/classes/logger.php');
require_once(__DIR__.'/classes/data_provider.php');
require_once(__DIR__.'/classes/cache_manager.php');

defined('MOODLE_INTERNAL') || die();

use \report_hybridmeter\classes\configurator as configurator;
use \report_hybridmeter\classes\data_provider as data_provider;
use \report_hybridmeter\classes\cache_manager as cache_manager;
use \report_hybridmeter\classes\logger as logger;

# https://app.clickup.com/t/1h2ad7h
function hybridation_calculus($type, $activity_data){
	$H = 0; // Hybridation value
	$C = 0; // Number of activity types
	$N = 0; // Nombre total d'activités
	$sigmaPk = 0; // Sum of activity weights
	$sigmaPkVk = 0; // Sum of activity weight multiplicated by their hybridation value
	$sigmaPkVk = 0; // Sum of activity weight multiplicated by their hybridation value
	$M = 1; // Malus
	foreach ($activity_data as $k => $Nk) {
		//Possibilité d'accéder à des valeurs hardcodées pour le diagnostic
		$Vk = configurator::getInstance()->get_coeff($type, $k); // Activity hybridation value
	
		if ($Nk > 0 && $Vk > 0) {
			$C ++; 
			$N += $Nk;
			$Pk = $Nk / ($Nk + HYBRIDMETER_ACTIVITY_INSTANCES_DEVIATOR_CONSTANT); // Activity weight
			$sigmaPk += $Pk;
			$sigmaPkVk += $Pk * $Vk;
		}
	}
	if ($N <= 2) $M = 0.25;
	if($sigmaPk != 0){
		$P = $C / ($C + HYBRIDMETER_ACTIVITY_VARIETY_DEVIATOR_CONSTANT); // Course weight
		$H = $M * $P * $sigmaPkVk / $sigmaPk;
	}
	return round($H, 2);
}

function hybridation_statique($object, $parameters){
	$activity_data = data_provider::getInstance()->count_activities_per_type_of_course($object['id']);
	return hybridation_calculus("static_coeffs", $activity_data);
}

function raw_data($object, $parameters){
	return data_provider::getInstance()->count_activities_per_type_of_course($object['id']);
}


//Fonction lambda utilisée pour calculer les indicateurs dynamiques
function hybridation_dynamique($object, $parameters){
	$data_provider = data_provider::getInstance();
	$configurator = configurator::getInstance();
	$indicator=0;
	$total=0;
	$activity_data=$data_provider->count_hits_on_activities_per_type($object['id'], 
		$configurator->get_begin_timestamp(),
		$configurator->get_end_timestamp()
	);
	return hybridation_calculus("dynamic_coeffs", $activity_data);
}

function get_category_path($object, $parameters){
	$cache_manager = cache_manager::getInstance();

	if($cache_manager->is_category_path_calculated($object['category_id']))
		return $cache_manager->get_category_path($object['category_id']);

	$category_path = data_provider::getInstance()->get_category_path($object['category_id']);

	$cache_manager->update_category_path($object['category_id'], $category_path);

	//logger::file_log($category_path, "lol.txt");

	return $category_path;
}

//Fonction lambda utilisée pour définir si le cours est actif
function is_course_active_last_month($object, $parameters){
	$configurator = configurator::getInstance();
	$data_provider = data_provider::getinstance();

	$count=$data_provider->count_student_visits_on_course(
		$object['id'], 
		$configurator->get_begin_timestamp(),
		$configurator->get_end_timestamp()
	);
	if ($count >= $configurator->get_data()["seuil_actif"])
		return 1;
	else
		return 0;
}

function active_students ($object, $parameters) {
	$configurator = configurator::getInstance();
	return data_provider::getInstance()->count_student_visits_on_course(
		$object['id'],
		$configurator->get_begin_timestamp(),
		$configurator->get_end_timestamp()
	);
}

function nb_inscrits ($object, $parameters) {
	return data_provider::getInstance()->count_registered_students_of_course($object['id']);
}