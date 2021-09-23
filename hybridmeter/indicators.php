<?php
require_once(__DIR__.'/constants.php');
require_once(__DIR__.'/classes/configurator.php');
defined('MOODLE_INTERNAL') || die();


//Fonction lambda utilisée pour calculer les indicateurs statiques
function hybridation_statique($object,$data,$parameters){
	$configurator = $parameters["configurator"];
	$count=$data->count_modules_types_id($object['id']);
	$total=0;
	$indicator=0;
	foreach ($count as $key => $value){
		$total+=$value;
		$indicator += $configurator->get_static_coeff($key)*$value;
	}
	if($total === 0){
		$total=1;
	}
	return round(($indicator/$total),2);
}


//Fonction lambda utilisée pour calculer les indicateurs dynamiques
function hybridation_dynamique($object,$data,$parameters){
	$configurator = $parameters["configurator"];
	$coeffs = $configurator->get_data()["dynamic_coeffs"];
	$indicator=0;
	$total=0;
	$info=$data->count_hits_by_module_type($object['id'], 
		$configurator->get_begin_timestamp(),
		$configurator->get_end_timestamp());
	
		
	foreach ($info as $key => $value){
		if (array_key_exists($key, $coeffs)) {
			$indicator += $value * $coeffs[$key];
			$total += $value;	
		}
	}
	if($total === 0){
		$total=1;
	}
	return round(($indicator/$total),2);
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