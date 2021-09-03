<?php

namespace report_hybridmetrics\task;

require_once(dirname(__FILE__).'/../exporter.php');
require_once(dirname(__FILE__).'/../../indicators.php');
require_once(dirname(__FILE__).'/../../constants.php');
require_once(dirname(__FILE__).'/../data.php');
require_once(dirname(__FILE__).'/../formatter.php');

// TODO: utiliser la future classe ../traitement.php
class traitement extends \core\task\adhoc_task {
    public function execute() {
    	$data=new \report_hybridmetrics\classes\data();

    	$timestamp = strtotime('NOW');
    	$data->set_as_running($timestamp);

		$formatter=new \report_hybridmetrics\classes\formatter($data, $data->get_ids_blacklist(), function($data, $blacklist){return $data->get_courses_sanitized($blacklist);});
		$exporter=new \report_hybridmetrics\classes\exporter(array('id','fullname','dynamique', 'statique','cours_actif', 'nb_utilisateurs_actifs', 'nb_inscrits'));

    	//$file=fopen("/var/www/html/moodle/report/hybridmetrics/gacooo.txt", "w");

		//fwrite($file, print_r(array("hohohoooohehihohoho"), true));
        $formatter->calculate_new_indicator("hybridation_statique", 'statique', array("nb_cours"=> $formatter->get_length_array()));
		$formatter->calculate_new_indicator("hybridation_dynamique", 'dynamique', array("nb_cours"=> $formatter->get_length_array()));
		$formatter->calculate_new_indicator("is_course_active_last_month", 'cours_actif');
		$formatter->calculate_new_indicator(function ($object, $data) { return $data->count_single_users_course_viewed($object['id'],strtotime("-1 month"),strtotime("now")); }, 'nb_utilisateurs_actifs');
		$formatter->calculate_new_indicator(function ($object, $data) { return $data->count_registered_users($object['id']); }, 'nb_inscrits');

		$data_out = $formatter->get_array();

		
		$exporter->set_data($data_out);
		$exporter->create_csv($SITE->fullname);
		
		$file_exporter = fopen(dirname(__FILE__)."/../../records/serialized_data","w");
		$s = serialize(array(
			"timestamp" => strtotime('NOW'),
			"data" => $data_out
		));
		fwrite($file_exporter, $s);

		$date = new \DateTime();
		$date->setTimestamp($timestamp);
		$date_format = $date->format('Y-m-d\TH:i:s');

		$filename = dirname(__FILE__)."/../../records/backup/record_".$date_format.".csv";

		$backup=fopen($filename,"w");
	    fwrite($backup, $exporter->print_csv_data(true));

	    $data->add_log_entry($timestamp, $filename);
	    $data->clear_running_tasks();
    }
}