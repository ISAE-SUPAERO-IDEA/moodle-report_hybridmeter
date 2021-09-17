<?php

namespace report_hybridmeter\classes;

require_once(dirname(__FILE__).'/../../../config.php');
require_once(__DIR__.'/exporter.php');
require_once(__DIR__.'/../indicators.php');
require_once(__DIR__.'/../constants.php');
require_once(__DIR__.'/data.php');
require_once(__DIR__.'/formatter.php');
require_once(__DIR__.'/configurator.php');


// TODO: Transformer en classe (P2)


class traitement{

	protected $data;
	protected $formatter;
	protected $exporter;
	protected $configurator;
	protected $date_debut;
	protected $date_fin;

	function __construct(){
		$this->data=new \report_hybridmeter\classes\data();
		$timestamp = strtotime('NOW');

		$this->data->clear_adhoc_tasks();
		
		$this->formatter=new \report_hybridmeter\classes\formatter($this->data, array(), function($data, $blacklist){return $data->get_whitelisted_courses();});

		$this->exporter=new \report_hybridmeter\classes\exporter(array('id_moodle', 'idnumber', 'fullname', 'url', 'niveau_de_digitalisation', 'niveau_d_utilisation', 'cours_actif', 'nb_utilisateurs_actifs', 'nb_inscrits', 'date_debut_capture', 'date_fin_capture'));

		$this->date_debut = new \DateTime();
		$this->date_debut->setTimestamp($timestamp);

		$this->date_fin = new \DateTime();

		$this->configurator = new \report_hybridmeter\classes\configurator($this->data);
	}

	function launch() {
		global $CFG;
		global $SITE;

		$this->configurator->set_as_running($this->date_debut->getTimestamp());

		//Calcul des indicateurs détaillés

		$this->formatter->calculate_new_indicator(
			function($object, $data, $parameters){
				return $object['id'];
			},
			"id_moodle"
		);


		$this->formatter->calculate_new_indicator(
			function($object, $data, $parameters){
				return $parameters["www_root"]."/course/view.php?id=".$object['id'];
			},
			"url",
			array("www_root" => $CFG->wwwroot)
		);

	    $this->formatter->calculate_new_indicator(
	    	"hybridation_statique",
	    	'niveau_de_digitalisation',
	    	array(
	    		"nb_cours" => $this->formatter->get_length_array(),
	    		"configurator" => $this->configurator
	    	)
	    );

		$this->formatter->calculate_new_indicator(
			"hybridation_dynamique",
			'niveau_d_utilisation',
			array(
				"nb_cours" => $this->formatter->get_length_array(),
				"configurator" => $this->configurator
			)
		);

		$this->formatter->calculate_new_indicator(
			"is_course_active_last_month",
			'cours_actif',
			array (
				"configurator" => $this->configurator
			)
		);

		$this->formatter->calculate_new_indicator(
			function ($object, $data, $parameters) {
				return $data->count_single_users_course_viewed(
					$object['id'],
					$parameters["begin_date"],
					$parameters["end_date"]
				);
			},
			'nb_utilisateurs_actifs',
			array(
				"begin_date" => $this->configurator->get_begin_timestamp(),
				"end_date" => $this->configurator->get_end_timestamp()
			)
		);

		$this->formatter->calculate_new_indicator(
			function ($object, $data, $parameters) {
				return $data->count_registered_users($object['id']);
			},
			'nb_inscrits'
		);

		$date_debut = new \DateTime();
		$date_debut->setTimestamp($this->configurator->get_begin_timestamp());
		$date_debut = $date_debut->format('Y-m-d H:i:s');


		$this->formatter->calculate_new_indicator(
			function ($object, $data, $parameters) {
				return $parameters['date_debut'];
			},
			'date_debut_capture',
			array(
				"date_debut" => $date_debut
			)
		);

		$date_fin = new \DateTime();
		$date_fin->setTimestamp($this->configurator->get_end_timestamp());
		$date_fin = $date_fin->format('Y-m-d H:i:s');

		$this->formatter->calculate_new_indicator(
			function ($object, $data, $parameters) {
				return $parameters['date_fin'];
			},
			'date_fin_capture',
			array(
				"date_fin" => $date_fin
			)
		);

		$data_out = $this->formatter->get_array();

		//Calcul des indicateurs généraux

		$generaldata=array();

		$generaldata['cours_hybrides_statiques']=array_values(
			array_filter($data_out,
				function($cours){
					return $cours["niveau_de_digitalisation"] >= SEUIL_STATIQUE;
				}
			)
		);

		$generaldata['cours_hybrides_dynamiques']=array_values(
			array_filter($data_out,
				function($cours){
					return $cours["niveau_d_utilisation"] >= SEUIL_DYNAMIQUE;
				}
			)
		);

		$generaldata['id_hybrides_statiques']=array_map(function($cours){
				return $cours["id"];
			}
		, $generaldata['cours_hybrides_statiques']);

		$generaldata['id_hybrides_dynamiques']=array_map(function($cours){
				return $cours["id"];
			}
		, $generaldata['cours_hybrides_dynamiques']);

		$generaldata['nb_cours_hybrides_statiques']=count($generaldata['cours_hybrides_statiques']);
		$generaldata['nb_cours_hybrides_dynamiques']=count($generaldata['cours_hybrides_dynamiques']);


		$generaldata['nb_etudiants_concernes_statiques']=$this->data->count_distinct_students(
			$generaldata['id_hybrides_statiques']
		);

		$generaldata['nb_etudiants_concernes_statiques_actifs']=$this->data->count_single_users_course_viewed(
			$generaldata['id_hybrides_statiques'],
			$this->configurator->get_begin_timestamp(),
			$this->configurator->get_end_timestamp()
		);
		
		

		$generaldata['nb_etudiants_concernes_dynamiques']=$this->data->count_distinct_students(
			$generaldata['id_hybrides_dynamiques']
		);
		
		$generaldata['nb_etudiants_concernes_dynamiques_actifs']=$this->data->count_single_users_course_viewed(
			$generaldata['id_hybrides_dynamiques'],
			$this->configurator->get_begin_timestamp(),
			$this->configurator->get_end_timestamp()
		);

		$generaldata["timestamp_debut_capture"] = $this->configurator->get_begin_timestamp();
		$generaldata["timestamp_fin_capture"] = $this->configurator->get_end_timestamp();

		//Export des données
		
		$this->exporter->set_data($data_out);
		$this->exporter->create_csv($SITE->fullname);


		$this->date_fin->setTimestamp(strtotime('NOW'));

		$interval = $this->date_debut->getTimestamp()-$this->date_fin->getTimestamp();

		$time = array(
			"timestamp_debut" => $this->date_debut->getTimestamp(),
			"timestamp_fin" => $this->date_fin->getTimestamp(),
			"diff" => $interval
		);
		
		error_log($this->date_debut->getTimestamp());
		error_log($this->date_fin->getTimestamp());
		error_log($interval);

		$file_exporter = fopen($CFG->dataroot."/hybridmeter/records/serialized_data","w");
		$s = serialize(array(
			"time" => $time,
			"data" => $data_out,
			"generaldata" => $generaldata
		));
		fwrite($file_exporter, $s);
		//error_log(dirname(__FILE__)."/../records/serialized_data");

		$date_format = $this->date_debut->format('Y-m-d H:i:s');

		$filename = $CFG->dataroot."/hybridmeter/records/backup/record_".$date_format.".csv";

		$backup=fopen($filename,"w");
	    fwrite($backup, $this->exporter->print_csv_data(true));

		//Gestion des logs et des tâches

	    $this->configurator->unset_as_running();

	    return $data_out;
	}
}

?>