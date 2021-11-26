<?php
require_once(__DIR__.'/constants.php');
require_once(__DIR__.'/classes/configurator.php');
defined('MOODLE_INTERNAL') || die();


const HYBRIDMETER_COURSE_DEVIATOR = 2;
const HYBRIDMETER_ACTIVITY_DEVIATOR = 2;

# https://app.clickup.com/t/1h2ad7h
function hybridation_calculus($type, $activity_data){
	$H = 0; // Hybridation value
	$C = 0; // Number of activity types
	$sigmaPk = 0; // Sum of activity weights
	$sigmaPkVk = 0; // Sum of activity weight multiplicated by their hybridation value
	foreach ($activity_data as $k => $Nk) {
		if ($Nk > 0) {
			$Vk = \report_hybridmeter\classes\configurator::getInstance()->get_coeff($type, $k); // Activity hybridation value
			$C ++; 
			$Pk = $Nk / ($Nk + HYBRIDMETER_ACTIVITY_DEVIATOR); // Activity weight
			$sigmaPk += $Pk;
			$sigmaPkVk += $Pk * $Vk;
		}
	}
	if($sigmaPk != 0){
		$P = $C / ($C + HYBRIDMETER_COURSE_DEVIATOR); // Course weight
		$H = $P * $sigmaPkVk / $sigmaPk;
	}
	return round($H, 2);
}

function hybridation_statique($object,$data,$parameters){
	$configurator = $parameters["configurator"];
	$activity_data = $data->count_modules_types_id($object['id']);
	return hybridation_calculus("static_coeffs", $activity_data);
}
function raw_data($object,$data,$parameters) {
	return $data->count_modules_types_id($object['id']);
}


//Fonction lambda utilisée pour calculer les indicateurs dynamiques
function hybridation_dynamique($object,$data,$parameters){
	$configurator = $parameters["configurator"];
	$coeffs = $configurator->get_data()["dynamic_coeffs"];
	$indicator=0;
	$total=0;
	$activity_data=$data->count_hits_by_module_type($object['id'], 
		$configurator->get_begin_timestamp(),
		$configurator->get_end_timestamp());
	return hybridation_calculus("static_coeffs", $activity_data);
}

//Fonction lambda utilisée pour définir si le cours est actif
function is_course_active_last_month($object, $data, $parameters){
	$configurator=$parameters["configurator"];

	$count=$data->count_single_users_course_viewed(
		$object['id'], 
		$configurator->get_begin_timestamp(),
		$configurator->get_end_timestamp()
	);

	if ($count >= SEUIL_ACTIF)
		return 1;
	else
		return 0;
}