<?php 
	require_once("../../../config.php");
    require_once("../classes/configurator.php");

	require_login();
	$context = context_system::instance();
	$PAGE->set_context($context);
	has_capability('report/hybridmeter:all', $context) || die();

	$configurator = new \report_hybridmeter\classes\configurator();
	
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

		if($id != 0){
			$courses = $DB->get_records('course', array("category" =>$id));
		}
		
		$output = [ 
		  "categories" => $categories,
		  "courses" => $courses ];
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
	echo json_encode($output);

?>