<?php

namespace report_hybridmetrics\classes;

require_once(__DIR__.'/exporter.php');
require_once(__DIR__.'/../indicators.php');
require_once(__DIR__.'/../constants.php');
require_once(__DIR__.'/data.php');
require_once(__DIR__.'/formatter.php');


// TODO: Transformer en classe (P2)
function traitement() {
	global $CFG;
	global $SITE;
	$data=new \report_hybridmetrics\classes\data();
	$timestamp = strtotime('NOW');
	$courses = $data->get_whitelisted_courses();
	
	# Compute raw data
	$formatter=new \report_hybridmetrics\classes\formatter($data, $data->get_ids_blacklist(), function($data, $blacklist){return $data->get_whitelisted_courses();});

    $formatter->calculate_new_indicator("hybridation_statique", 'statique', array("nb_cours"=> $formatter->get_length_array()));
	$formatter->calculate_new_indicator("hybridation_dynamique", 'dynamique', array("nb_cours"=> $formatter->get_length_array()));
	$formatter->calculate_new_indicator("is_course_active_last_month", 'cours_actif');
	$formatter->calculate_new_indicator(function ($object, $data) { return $data->count_single_users_course_viewed($object['id'],strtotime("-1 month"),strtotime("now")); }, 'nb_utilisateurs_actifs');
	$formatter->calculate_new_indicator(function ($object, $data) { return $data->count_registered_users($object['id']); }, 'nb_inscrits');

	$data_out = $formatter->get_array();

	
	$serialized_data_file = fopen($CFG->dataroot."/hybridmetrics/records/serialized_data","w");
	$s = serialize(array(
		"timestamp" => strtotime('NOW'),
		"data" => $data_out
	));
	fwrite($serialized_data_file, $s);
	//error_log(dirname(__FILE__)."/../records/serialized_data");

	# Export
	$exporter=new \report_hybridmetrics\classes\exporter(array('id','fullname','dynamique', 'statique','cours_actif', 'nb_utilisateurs_actifs', 'nb_inscrits'));
	
	$exporter->set_data($data_out);
	$exporter->create_csv($SITE->fullname);

	$date = new \DateTime();
	$date->setTimestamp($timestamp);
	$date_format = $date->format('Y-m-d\TH:i:s');

	$filename = $CFG->dataroot."/hybridmetrics/records/backup/record_".$date_format.".csv";

	$backup=fopen($filename,"w");
    fwrite($backup, $exporter->print_csv_data(true));

    return $data_out;
}

?>