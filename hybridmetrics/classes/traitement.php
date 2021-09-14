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
	protected $configurator;
	protected $date;

	function __construct(){
		$this->data=new \report_hybridmetrics\classes\data();
		$timestamp = strtotime('NOW');
		$this->data->set_as_running($timestamp);
		
		$this->formatter=new \report_hybridmetrics\classes\formatter($this->data, $this->data->get_ids_blacklist(), function($data, $blacklist){return $data->get_whitelisted_courses();});

		$this->exporter=new \report_hybridmetrics\classes\exporter(array('id','fullname','dynamique', 'statique','cours_actif', 'nb_utilisateurs_actifs', 'nb_inscrits'));

		$this->date = new \DateTime();
		$this->date->setTimestamp($timestamp);

		$this->configurator = new \report_hybridmetrics\classes\configurator();
	}

	function launch() {
		global $CFG;
		global $SITE;

		//Calcul des indicateurs détaillés

	    $this->formatter->calculate_new_indicator(
	    	"hybridation_statique",
	    	'statique',
	    	array(
	    		"nb_cours" => $this->formatter->get_length_array()
	    	)
	    );

		$this->formatter->calculate_new_indicator(
			"hybridation_dynamique",
			'dynamique',
			array(
				"nb_cours" => $this->formatter->get_length_array(),
				"begin_date" => $this->configurator->get_begin_timestamp(),
				"end_date" => $this->configurator->get_end_timestamp()
			)
		);

		$this->formatter->calculate_new_indicator(
			"is_course_active_last_month",
			'cours_actif',
			array (
				"begin_date" => $this->configurator->get_begin_timestamp(),
				"end_date" => $this->configurator->get_end_timestamp()
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

		$data_out = $this->formatter->get_array();

		//Calcul des indicateurs généraux

		$generaldata=array();

		$generaldata['cours_hybrides_statiques']=array_values(
			array_filter($data_out,
				function($cours){
					return $cours["statique"] > SEUIL_STATIQUE;
				}
			)
		);

		$generaldata['cours_hybrides_dynamiques']=array_values(
			array_filter($data_out,
				function($cours){
					return $cours["dynamique"] > SEUIL_DYNAMIQUE;
				}
			)
		);

		$generaldata['id_hybrides_statiques']=array_map(function($cours){
				return $cours["id"];
			}
		, $generaldata['cours_hybrides_dynamiques']);

		$generaldata['id_hybrides_dynamiques']=array_map(function($cours){
				return $cours["id"];
			}
		, $generaldata['cours_hybrides_dynamiques']);

		$generaldata['nb_cours_hybrides_statiques']=count($generaldata['cours_hybrides_statiques']);
		$generaldata['nb_cours_hybrides_dynamiques']=count($generaldata['cours_hybrides_dynamiques']);

		$generaldata['nb_etudiants_concernes_statiques']=$this->data->count_distinct_students($generaldata['id_hybrides_statiques']);

		$generaldata['nb_etudiants_concernes_statiques_actifs']=$this->data->count_single_users_course_viewed($generaldata['id_hybrides_statiques'],$this->configurator->get_begin_timestamp(),$this->configurator->get_end_timestamp());
		
		$generaldata['nb_etudiants_concernes_dynamiques']=$this->data->count_distinct_students($generaldata['id_hybrides_dynamiques']);
		
		$generaldata['nb_etudiants_concernes_dynamiques_actifs']=$this->data->count_single_users_course_viewed($generaldata['cours_hybrides_dynamiques'],$this->configurator->get_begin_timestamp(),$this->configurator->get_end_timestamp());

		//Export des données
		
		$this->exporter->set_data($data_out);
		$this->exporter->create_csv($SITE->fullname);

		
		$file_exporter = fopen($CFG->dataroot."/hybridmetrics/records/serialized_data","w");
		$s = serialize(array(
			"timestamp" => strtotime('NOW'),
			"data" => $data_out,
			"generaldata" => $generaldata
		));
		fwrite($file_exporter, $s);
		//error_log(dirname(__FILE__)."/../records/serialized_data");

		$date_format = $this->date->format('Y-m-d H:i:s');

		$filename = $CFG->dataroot."/hybridmetrics/records/backup/record_".$date_format.".csv";

		$backup=fopen($filename,"w");
	    fwrite($backup, $this->exporter->print_csv_data(true));

		//Gestion des logs et des tâches

	    $this->data->add_log_entry($this->date->getTimestamp(), $filename);
	    $this->data->clear_running_tasks();

	    return $data_out;
	}
}

?>