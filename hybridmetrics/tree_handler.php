<?php 
	require_once("../../config.php");
    require_once(dirname(__FILE__).'/classes/data.php');
	$data = new \report_hybridmetrics\classes\data();

	$task  = $_GET['task'];
	// List category children
	if ($task == "category_children") {
		$id = $_GET['id'];
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
		$res = [ 
		  "categories" => $categories,
		  "courses" => $courses ];
	}
	// manage blacklist of a category or course
	if ($task == "manage_blacklist") {
		$type = $_GET['type'];
		$value = $_GET['value'] == "true" ? 1 : 0;
		$id = $_GET['id'];
		if ($type=="category") {
			$data->set_category_blacklisted($id, $value);
			$res = [ "blacklisted" => $value ];
		}
		if ($type=="course") {
			$data->set_course_blacklisted($id, $value);
			$res = [ "blacklisted" => $value ];
		}	
	}
	echo json_encode($res);

?>