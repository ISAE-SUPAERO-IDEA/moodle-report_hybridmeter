<?php
	/*
	AJAX endpoint to manage HybridMeter configuration

	*/
	require_once(dirname(__FILE__)."/../../../config.php");
	require_once(__DIR__."/../classes/configurator.php");
	use \report_hybridmeter\classes\configurator as configurator;

    header('Content-Type: text/json');

    //Vérification des autorisations (rôle admin obligatoire)

	error_log("mdr");

	require_login();

	error_log("oh");

	$context = \context_system::instance();
	$PAGE->set_context($context);
	has_capability('report/hybridmeter:all', $context) || die();

	error_log("lol");

	$configurator = configurator::getInstance();

	error_log("haha");

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
			$configurator->schedule_calculation($scheduled_timestamp);
		}
		else if ($action == "unschedule"){
			$configurator->update([
				"has_scheduled_calculation" => 0,
				"debug" => $debug
			]);
		}
	} else  {	
		$task  = optional_param('task', 'nothing', PARAM_ALPHAEXT);

	// Lecture
		if ($task == "get_dynamic_coeffs"){
			$output = json_encode($configurator->get_coeffs_grid("dynamic_coeffs"));
		}
		else if ($task == "get_static_coeffs"){
			$output = json_encode($configurator->get_coeffs_grid("static_coeffs"));
		}
		else if ($task == "get_seuils"){
			$output = json_encode($configurator->get_seuils_grid());
		}
		else{
			$output = json_encode($configurator->get_data());
		}

		echo $output;
	}

	error_log("wtf");