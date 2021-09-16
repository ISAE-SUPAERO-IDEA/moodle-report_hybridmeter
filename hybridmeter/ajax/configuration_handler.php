<?php 
	require_once("../../../config.php");
    require_once("../classes/configurator.php");
    require_once("../classes/data.php");

	require_login();
	$context = context_system::instance();
	$PAGE->set_context($context);
	has_capability('report/hybridmeter:all', $context) || die();

	$data = new \report_hybridmeter\classes\data();
	$configurator = new \report_hybridmeter\classes\configurator($data);

 	// Sauvegarde
	if ($_SERVER['REQUEST_METHOD'] == "POST") {
		$begin_date = optional_param('begin_date', null, PARAM_INT);
		$end_date = optional_param('end_date', null, PARAM_INT);
		$configurator->update([
			"begin_date" => $begin_date, 
			"end_date" => $end_date
		]);
	} else  {
		$task  = optional_param('task', 'nothing', PARAM_ALPHAEXT);
	// Lecture
		if ($task == "get_dynamic_coeffs"){
			echo json_encode($configurator->get_coeffs_grid("dynamic_coeffs"));
		}
		else if ($task == "get_static_coeffs"){
			echo json_encode($configurator->get_coeffs_grid("static_coeffs"));
		}
		else if ($task == "get_seuils"){
			echo json_encode($configurator->get_seuils_grid());
		}
		else{
			echo json_encode($configurator->get_data());
		}
	}