<?php 

/*
AJAX endpoint to manage HybridMeter blacklist configuration

*/
require_once("../../../config.php");
require_once(__DIR__."/../classes/configurator.php");
require_once(__DIR__."/../classes/data_provider.php");
require_once(__DIR__."/../classes/logger.php");

use \report_hybridmeter\classes\configurator as configurator;
use \report_hybridmeter\classes\data_provider as data_provider;
use \report_hybridmeter\classes\logger as logger;

header('Content-Type: text/json');

//Checking authorizations (admin role required)

require_login();
$context = context_system::instance();
$PAGE->set_context($context);
has_capability('report/hybridmeter:all', $context) || die();

$configurator = configurator::get_instance();
$data_provider = data_provider::get_instance();

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $task  = required_param('task', PARAM_ALPHAEXT);
}
else {
    $task = "get";
    $output = array(
        "error" => true,
        "message" => "GET method not supported, please retry with a POST request",
    );
}
// List category children
if ($task == "category_children") {
    $id = required_param('id', PARAM_INT);
    $categories = $data_provider->get_children_categories_ordered($id);

    //In the case where the category id is 0, the child course that corresponds to the site is not returned

    if($id != 0){
        $courses = $data_provider->get_children_courses_ordered($id);
    }
    else{
        $courses = array();
    }
    
    $output = [ 
        "categories" => $categories,
        "courses" => $courses,
    ];
}
// manage blacklist of a category or course
else if ($task == "manage_blacklist") {
    $type = required_param('type', PARAM_ALPHAEXT);
    $value = required_param('value', PARAM_ALPHAEXT) == "true" ? 1 : 0;
    $id = required_param('id', PARAM_INT);
    $configurator->set_blacklisted($type, $id, $value);

    //Debugging feature, set debug value to 1 in configurations to display
    logger::log("New manage_blacklist post request");
    logger::log(array("value" => $value, "type" => $type, "id" => $id));

    $output = [ "blacklisted" => $value ];
}
else{
    $output = array(
        "error" => true,
        "message" => "Tâche inconnue",
    );
}

//Return response as JSON

echo json_encode($output);
