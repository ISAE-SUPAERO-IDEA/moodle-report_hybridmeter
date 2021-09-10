<?php 
	require_once("../../../config.php");
    require_once("../classes/configurator.php");
	$configurator = new \report_hybridmetrics\classes\configurator();
    // TODO: Gérer les droits d'accès (P2)
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
		$bl_categories = $DB->get_records("report_hybridmetrics_blcat", array('blacklisted'=>1));
		foreach($categories as $category) {
			$category->blacklisted = false;
			foreach($bl_categories as $bl_category) {
				if ($bl_category->id_category == $category->id) {
					$category->blacklisted = true;
				}
			}
		}


		$courses = $DB->get_records('course', array("category" =>$id));
		$bl_courses = $DB->get_records("report_hybridmetrics_blcours", array('blacklisted'=>1));
		foreach($courses as $course) {
			$course->blacklisted = false;
			foreach($bl_courses as $bl_course) {
				if ($bl_course->id_course == $course->id) {
					$course->blacklisted = true;
				}
			}
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