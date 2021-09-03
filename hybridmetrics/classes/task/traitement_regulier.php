<?php

namespace report_hybridmetrics\task;

require_once(dirname(__FILE__).'/../classes/exporter.php');
require_once(dirname(__FILE__).'/../classes/data.php');
require_once(dirname(__FILE__).'/../classes/formatter.php');

// TODO: utiliser la future classe ../traitement.php
class traitement extends \core\task\scheduled_task {
	protected $data;
	protected $formatter;
	protected $exporter;

	public function get_name(){
		return "ça marche!!!!";
	}

    public function execute() {
    	mtrace("My task started");
    	$this->data=new \report_hybridmetrics\classes\data();
		$this->formatter=new \report_hybridmetrics\classes\formatter(function ($data, $external_data){
			$blacklist=$data->get_ids_blacklist();
			return $data->get_courses_sanitized($blacklist);
		}, $this->data);
		$this->exporter=new \report_hybridmetrics\classes\exporter(array('id','fullname','dynamique', 'statique','cours_actif', 'nb_utilisateurs_actifs', 'nb_inscrits'));
    	$file=fopen("/mnt/data/moodle37/report/hybridmetrics/gacooo.txt", "w");

		fwrite($file, print_r("hohohoooo", true));

        $this->formatter->calculate_new_indicator("hybridation_statique", 'statique', array("nb_cours"=>$this->formatter->get_length_array()));
		$this->formatter->calculate_new_indicator("hybridation_dynamique", 'dynamique', array("nb_cours"=>$this->formatter->get_length_array()));
		$this->formatter->calculate_new_indicator("is_course_active_last_month", 'cours_actif');
		$this->formatter->calculate_new_indicator(function ($object, $data) { return $data->count_single_users_course_viewed($object['id'],strtotime("-1 month"),strtotime("now")); }, 'nb_utilisateurs_actifs');
		$this->formatter->calculate_new_indicator(function ($object, $data) { return $data->count_registered_users($object['id']); }, 'nb_inscrits');

		$data = $this->formatter->get_array();

		
		$this->exporter->set_data($data);
		$this->exporter->create_csv($SITE->fullname);
		$fs = fopen(dirname(__FILE__)."/mnt/data/moodle37/report/hybridmetrics/records/results.csv","w");
	    $fs->create_file_from_string($fs, $this->exporter->print_csv_data());
	    mtrace("My task finished");
    }
}