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

	function __construct(){
		$this->data=new \report_hybridmetrics\classes\data();
		$timestamp = strtotime('NOW');
		$this->data->set_as_running($timestamp);
		
		$this->formatter=new \report_hybridmetrics\classes\formatter($this->data, $this->data->get_ids_blacklist(), function($data, $blacklist){return $data->get_whitelisted_courses();});

		$this->exporter=new \report_hybridmetrics\classes\exporter(array('id','fullname','dynamique', 'statique','cours_actif', 'nb_utilisateurs_actifs', 'nb_inscrits'));

		$this->date = new \DateTime();
		$this->date->setTimestamp($timestamp);
	}

	function launch() {
		global $CFG;
		global $SITE;

		//Calcul des indicateurs détaillés

	    $this->formatter->calculate_new_indicator("hybridation_statique", 'statique', array("nb_cours"=> $this->formatter->get_length_array()));
		$this->formatter->calculate_new_indicator("hybridation_dynamique", 'dynamique', array("nb_cours"=> $this->formatter->get_length_array()));
		$this->formatter->calculate_new_indicator("is_course_active_last_month", 'cours_actif');
		$this->formatter->calculate_new_indicator(function ($object, $data) { return $data->count_single_users_course_viewed($object['id'],strtotime("-1 month"),strtotime("now")); }, 'nb_utilisateurs_actifs');
		$this->formatter->calculate_new_indicator(function ($object, $data) { return $data->count_registered_users($object['id']); }, 'nb_inscrits');

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
		, $cours_hybrides_statiques);

		$generaldata['id_hybrides_dynamiques']=array_map(function($cours){
				return $cours["id"];
			}
		, $cours_hybrides_dynamiques);

		$generaldata['nb_cours_hybrides_statiques']=count($generaldata['cours_hybrides_statiques']);
		$generaldata['nb_cours_hybrides_dynamiques']=count($generaldata['cours_hybrides_dynamiques']);

		/*$etudiants_concernes_statiques=$this->data->count_distinct_student($id_hybrides_statiques);
		$nb_etudiants_concernes_statiques=count($etudiants_concernes_statiques);

		$etudiants_concernes_actifs=$this->data->liste_etudiants_cours($id_hybrides,strtotime("-1 month"),strtotime("now"));
		$nb_etudiants_concernes_actifs=count($etudiants_concernes_actifs);
		

		$recap=array(array(),array());
		$recap[0]["nb_cours_hybrides"]="Nombre de cours hybrides";
		$recap[1]["nb_cours_hybrides"]=count($cours_hybrides);
		$recap[0]["nb_concernes_alltime"]="Nombre d'étudiants inscrits dans au moins un cours hybride";
		$recap[1]["nb_concernes_alltime"]=$nb_etudiants_concernes;
		$recap[0]["nb_concernes_actifs"]="Nombre d'étudiants ayant été actifs dans au moins un cours hybride durant la période de mesure";
		$recap[1]["nb_concernes_actifs"]=$nb_etudiants_concernes_actifs;*/

		//Export des données détaillées
		
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

	    $this->data->add_log_entry($timestamp, $filename);
	    $this->data->clear_running_tasks();
	}
}

?>