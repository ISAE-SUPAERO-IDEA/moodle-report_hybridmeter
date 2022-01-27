<?php

namespace report_hybridmeter\classes;

require_once(dirname(__FILE__).'/../../../config.php');
require_once(__DIR__.'/../indicators.php');
require_once(__DIR__.'/../constants.php');
require_once(__DIR__."/configurator.php");
require_once(__DIR__."/data_provider.php");
require_once(__DIR__."/exporter.php");
require_once(__DIR__."/formatter.php");

use \report_hybridmeter\classes\data_provider as data_provider;
use \report_hybridmeter\classes\configurator as configurator;
use \report_hybridmeter\classes\exporter as exporter;
use \report_hybridmeter\classes\formatter as formatter;

class traitement{

	protected $formatter;
	protected $exporter;
	protected $date_debut;
	protected $date_fin;

	function __construct(){
		$timestamp = strtotime('NOW');

		$data_provider = data_provider::getInstance();
		$configurator = configurator::getInstance();

		$whitelist_ids = $data_provider->get_whitelisted_courses_id();

		$filtered = $data_provider->filter_living_courses_on_period($whitelist_ids, $configurator->get_begin_timestamp(), $configurator->get_end_timestamp());

		$this->formatter=new formatter($filtered);

		$this->exporter=new exporter(FIELDS, ALIAS);
		
		$this->date_debut = new \DateTime();
		$this->date_debut->setTimestamp($timestamp);

		$this->date_fin = new \DateTime();
	}

	function launch() {
		global $CFG;
		global $SITE;

		$configurator = configurator::getInstance();
		$data_provider = data_provider::getInstance();

		$configurator->set_as_running($this->date_debut->getTimestamp());

		//Calcul des indicateurs détaillés

		$this->formatter->calculate_new_indicator(
			function($object, $parameters){
				return $object['id'];
			},
			"id_moodle"
		);

		$this->formatter->calculate_new_indicator(
			function($object, $parameters){
				return $object['category_name'];
			},
			"category_name"
		);


		$this->formatter->calculate_new_indicator(
			function($object, $parameters){
				return $object['idnumber'];
			},
			"id_number"
		);


		$this->formatter->calculate_new_indicator(
			function($object, $parameters){
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
	    		"configurator" => $configurator
	    	)
	    );

		$this->formatter->calculate_new_indicator(
			"hybridation_dynamique",
			'niveau_d_utilisation',
			array(
				"nb_cours" => $this->formatter->get_length_array(),
				"configurator" => $configurator
			)
		);


		$this->formatter->calculate_new_indicator(
			"is_course_active_last_month",
			'cours_actif',
			array (
				"configurator" => $configurator
			)
		);

		$this->formatter->calculate_new_indicator(
			function ($object, $parameters) {
				$configurator = configurator::getInstance();
				return data_provider::getInstance()->count_student_visits_on_course(
					$object['id'],
					$configurator->get_begin_timestamp(),
					$configurator->get_end_timestamp()
				);
			},
			'nb_utilisateurs_actifs'
		);

		$this->formatter->calculate_new_indicator(
			function ($object, $parameters) {
				return data_provider::getInstance()->count_registered_students_of_course($object['id']);
			},
			'nb_inscrits'
		);

		$date_debut = new \DateTime();
		$date_debut->setTimestamp($configurator->get_begin_timestamp());
		$date_debut = $date_debut->format('d/m/Y');


		$this->formatter->calculate_new_indicator(
			function ($object, $parameters) {
				return $parameters['date_debut'];
			},
			'date_debut_capture',
			array(
				"date_debut" => $date_debut
			)
		);

		$date_fin = new \DateTime();
		$date_fin->setTimestamp($configurator->get_end_timestamp());
		$date_fin = $date_fin->format('d/m/Y');

		$this->formatter->calculate_new_indicator(
			function ($object, $parameters) {
				return $parameters['date_fin'];
			},
			'date_fin_capture',
			array(
				"date_fin" => $date_fin
			)
		);

		$this->formatter->calculate_new_indicator(
			'raw_data',
			'raw_data'
		);

		$data_out = $this->formatter->get_array();

		//Calcul des indicateurs généraux

		$generaldata=array();

		$generaldata['cours_hybrides_statiques']=array_values(
			array_filter($data_out,
				function($cours){
					return $cours["niveau_de_digitalisation"] >= configurator::getInstance()->get_data()["seuil_statique"];
				}
			)
		);

		$generaldata['cours_hybrides_dynamiques']=array_values(
			array_filter($data_out,
				function($cours){
					return $cours["niveau_d_utilisation"] >= configurator::getInstance()->get_data()["seuil_dynamique"];
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

		$generaldata['nb_etudiants_concernes_statiques']=$data_provider->count_distinct_registered_students_of_courses(
			$generaldata['id_hybrides_statiques']
		);

		$generaldata['nb_etudiants_concernes_statiques_actifs']=$data_provider->count_student_single_visitors_on_courses(
			$generaldata['id_hybrides_statiques'],
			$configurator->get_begin_timestamp(),
			$configurator->get_end_timestamp()
		);
		
		

		$generaldata['nb_etudiants_concernes_dynamiques']=$data_provider->count_distinct_registered_students_of_courses(
			$generaldata['id_hybrides_dynamiques']
		);
		
		$generaldata['nb_etudiants_concernes_dynamiques_actifs']=$data_provider->count_student_single_visitors_on_courses(
			$generaldata['id_hybrides_dynamiques'],
			$configurator->get_begin_timestamp(),
			$configurator->get_end_timestamp()
		);

		$generaldata["timestamp_debut_capture"] = $configurator->get_begin_timestamp();
		$generaldata["timestamp_fin_capture"] = $configurator->get_end_timestamp();

		$generaldata["nb_cours_analyses"] = $this->formatter->get_length_array();

		//Export des données
		
		/*$this->exporter->set_data($data_out);
		$this->exporter->create_csv($SITE->fullname);*/


		$this->date_fin->setTimestamp(strtotime('NOW'));

		$interval = $this->date_fin->getTimestamp()-$this->date_debut->getTimestamp();

		$time = array(
			"timestamp_debut" => $this->date_debut->getTimestamp(),
			"timestamp_fin" => $this->date_fin->getTimestamp(),
			"diff" => $interval
		);

		if (!file_exists($CFG->dataroot."/hybridmeter/records")) {
			mkdir($CFG->dataroot."/hybridmeter/records", 0700, true);
		}

		$file_exporter = fopen($CFG->dataroot."/hybridmeter/records/serialized_data","w");
		$s = serialize(array(
			"time" => $time,
			"data" => $data_out,
			"generaldata" => $generaldata
		));
		fwrite($file_exporter, $s);

		$date_format = $this->date_debut->format('Y-m-d H:i:s');

		/*Nous avons desactivé l'historisation des CSV pour des raisons de RGPD (il faut renégocier les conditions avec le DPO pour les inclure)
		
		$filename = $CFG->dataroot."/hybridmeter/records/backup/record_".$date_format.".csv";

		$backup=fopen($filename,"w");
	    fwrite($backup, $this->exporter->print_csv_data(true));*/

		//Gestion des logs et des tâches

	    $configurator->unset_as_running();

	    return $data_out;
	}
}

?>