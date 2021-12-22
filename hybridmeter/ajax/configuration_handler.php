<?php
	/*
	AJAX endpoint to manage HybridMeter configuration data

	*/ 
	require_once("../../../config.php");
    require_once("../classes/configurator.php");
    require_once("../classes/data.php");

	use \report_hybridmeter\classes\configurator as configurator;
	use \report_hybridmeter\classes\data as data;

    header('Content-Type: text/json');

    //Vérification des autorisations (rôle admin obligatoire)

	require_login();
	$context = context_system::instance();
	$PAGE->set_context($context);
	has_capability('report/hybridmeter:all', $context) || die();

	$data = data::getInstance();
	$configurator = configurator::getInstance();

 	// Sauvegarde
	if ($_SERVER['REQUEST_METHOD'] == "POST") {
		$action = optional_param('action', 'nothing', PARAM_ALPHAEXT);
		$debug = optional_param('debug', null, PARAM_BOOL);

		if($action == "periode_mesure"){
			$begin_date = required_param('begin_date', PARAM_INT);
			$end_date = required_param('end_date', PARAM_INT);
			$configurator->update([
				"begin_date" => $begin_date, 
				"end_date" => $end_date,
				"debug" => $debug
			]);
		}
		else if ($action == "schedule"){
			$scheduled_timestamp = required_param('scheduled_timestamp', PARAM_INT);
			$configurator->update([
				"scheduled_date" => $scheduled_timestamp,
				"has_scheduled_calculation" => 1,
				"debug" => $debug
			]);
		}
		else if ($action == "unschedule"){
			$configurator->update([
				"has_scheduled_calculation" => 0,
				"debug" => $debug
			]);
		}
	} else  {
		/*TODO : Un seul echo*/
		
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