<?php 
	require_once("../../../config.php");
    require_once("../classes/configurator.php");
    // TODO: Gérer les droits d'accès (P2)
	$configurator = new \report_hybridmetrics\classes\configurator();
 	// Sauvegarde
	if ($_SERVER['REQUEST_METHOD'] == "POST") {
		$begin_date = optional_param('begin_date', null, PARAM_INT);
		$end_date = optional_param('end_date', null, PARAM_INT);
		$configurator->update([
			"begin_date" => $begin_date, 
			"end_date" => $end_date
		]);
	} else  {
	// Lecture
		echo json_encode($configurator->get_data());
	}

?>