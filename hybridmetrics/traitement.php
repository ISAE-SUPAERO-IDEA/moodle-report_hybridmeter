<?php

class traitement extends \core\task\adhoc_task {

    public function execute() {
    	mtrace("My task started");
    	$file=fopen("/mnt/data/moodle37/report/hybridmetrics/gacooo.txt", "w");

		fwrite($file, print_r("hohohoooo", true));

        $formatter->calculate_new_indicator("hybridation_statique", 'statique', array("nb_cours"=>$formatter->get_length_array()));
		$formatter->calculate_new_indicator("hybridation_dynamique", 'dynamique', array("nb_cours"=>$formatter->get_length_array()));
		$formatter->calculate_new_indicator("is_course_active_last_month", 'cours_actif');
		$formatter->calculate_new_indicator(function ($object, $data) { return $data->count_single_users_course_viewed($object['id'],strtotime("-1 month"),strtotime("now")); }, 'nb_utilisateurs_actifs');
		$formatter->calculate_new_indicator(function ($object, $data) { return $data->count_registered_users($object['id']); }, 'nb_inscrits');

		$data = $formatter->get_array();

		$exporter=new \report_hybridmetrics\classes\exporter(array('id','fullname','dynamique', 'statique','cours_actif', 'nb_utilisateurs_actifs', 'nb_inscrits'));
		$exporter->set_data($data);
		$exporter->create_csv($SITE->fullname);
		$fs = fopen(dirname(__FILE__)."/mnt/data/moodle37/report/hybridmetrics/records/results.csv","w");
	    $fs->create_file_from_string($fs, $exporter->print_csv_data());
	    mtrace("My task finished");
    }
}