<?php 
	/*
	AJAX endpoint to manage HybridMeter blacklist configuration

	*/
	require_once("../../../config.php");
	require_once(__DIR__."/../classes/configurator.php");
	use \report_hybridmeter\classes\configurator as configurator;

    header('Content-Type: text/json');

    //Vérification des autorisations (rôle admin obligatoire)

	require_login();
	$context = context_system::instance();
	$PAGE->set_context($context);
	has_capability('report/hybridmeter:all', $context) || die();

	$configurator = configurator::getInstance();
	
    if ($_SERVER['REQUEST_METHOD'] == "POST") {
		$task  = required_param('task', PARAM_ALPHAEXT);
	}
	else {
		$task = "get";
		$output = array(
			"error" => true,
			"message" => "GET method not supported, please retry with a POST request"
		);
	}
	// List category children
	if ($task == "category_children") {
		$id = required_param('id', PARAM_INT);
		$categories = $DB->get_records('course_categories', array("parent" => $id));

		//Dans le cas où l'id de la catégorie est 0, on ne renvoie pas le cours enfant qui correpond au site

		if($id != 0){
			$courses = $DB->get_records('course', array("category" =>$id));
		}
		else{
			$courses = array();
		}
		
		$output = [ 
		  "categories" => $categories,
		  "courses" => $courses
		];
	}
	// manage blacklist of a category or course
	else if ($task == "manage_blacklist") {
		$type = required_param('type', PARAM_ALPHAEXT);
		$value = required_param('value', PARAM_ALPHAEXT) == "true" ? 1 : 0;
		$id = required_param('id', PARAM_INT);
		$configurator->set_blacklisted($type, $id, $value);
		$output = [ "blacklisted" => $value ];
	}
	else{
		$output = array(
			"error" => true,
			"message" => "Tâche inconnue"
		);
	}

	//Renvoie de la réponse

	echo json_encode($output);

?>