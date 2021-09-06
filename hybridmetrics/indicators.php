<?php
require_once(__DIR__.'/constants.php');
defined('MOODLE_INTERNAL') || die();

//define("SEUIL_ACTIF", 5);


//Fonction lambda utilisée pour calculer les indicateurs statiques
function hybridation_statique($object,$data,$parameters){
	$count=$data->count_modules_types_id($object['id']);
	$indicator=0;
	foreach ($count as $key => $value){
		$coeff = 1;
		if (array_key_exists($key, COEFF_STATIQUES)) {
			$coeff = COEFF_STATIQUES[$key]*$value;
		}
		$indicator+= $coeff;
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