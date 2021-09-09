<?php

namespace report_hybridmetrics\classes;

require_once(__DIR__.'/exporter.php');
require_once(__DIR__.'/../indicators.php');
require_once(__DIR__.'/../constants.php');
require_once(__DIR__.'/data.php');
require_once(__DIR__.'/formatter.php');


// TODO: Transformer en classe (P2)

class traitement{

	protected $data;
	protected $formatter;
	protected $exporter;
	protected $date;

	function __construct__(){
		$this->data=new \report_hybridmetrics\classes\data();
		$timestamp = strtotime('NOW');
		$this->data->set_as_running($timestamp);
		
		$this->formatter=new \report_hybridmetrics\classes\formatter($data, $this->data->get_ids_blacklist(), function($data, $blacklist){return $this->data->get_whitelisted_courses();});

		$this->exporter=new \report_hybridmetrics\classes\exporter(array('id','fullname','dynamique', 'statique','cours_actif', 'nb_utilisateurs_actifs', 'nb_inscrits'));

		$this->date = new \DateTime();
		$this->date->setTimestamp($timestamp);
	}

	function launch() {
		global $CFG;
		global $SITE;

	    $this->formatter->calculate_new_indicator("hybridation_statique", 'statique', array("nb_cours"=> $this->formatter->get_length_array()));
		$this->formatter->calculate_new_indicator("hybridation_dynamique", 'dynamique', array("nb_cours"=> $this->formatter->get_length_array()));
		$this->formatter->calculate_new_indicator("is_course_active_last_month", 'cours_actif');
		$this->formatter->calculate_new_indicator(function ($object, $data) { return $this->data->count_single_users_course_viewed($object['id'],strtotime("-1 month"),strtotime("now")); }, 'nb_utilisateurs_actifs');
		$this->formatter->calculate_new_indicator(function ($object, $data) { return $this->data->count_registered_users($object['id']); }, 'nb_inscrits');

		$data_out = $this->formatter->get_array();

		
		$this->exporter->set_data($data_out);
		$this->exporter->create_csv($SITE->fullname);

		
		$file_exporter = fopen($CFG->dataroot."/hybridmetrics/records/serialized_data","w");
		$s = serialize(array(
			"timestamp" => strtotime('NOW'),
			"data" => $data_out
		));
		fwrite($file_exporter, $s);
		//error_log(dirname(__FILE__)."/../records/serialized_data");

		$date_format = $this->date->format('Y-m-d\TH:i:s');

		$filename = $CFG->dataroot."/hybridmetrics/records/backup/record_".$date_format.".csv";

		$backup=fopen($filename,"w");
	    fwrite($backup, $this->exporter->print_csv_data(true));

	    $this->data->add_log_entry($timestamp, $filename);
	    $this->data->clear_running_tasks();
	}
}

?>