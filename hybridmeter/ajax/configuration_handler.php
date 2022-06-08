<?php
    
/*
AJAX endpoint to manage HybridMeter configuration

*/
require_once(dirname(__FILE__)."/../../../config.php");
require_once(__DIR__."/../classes/configurator.php");

use \report_hybridmeter\classes\configurator as configurator;

header('Content-Type: text/json');

//Checking authorizations (admin role required)

require_login();

$context = \context_system::instance();
$PAGE->set_context($context);
has_capability('report/hybridmeter:all', $context) || die();

$configurator = configurator::get_instance();

// Writing
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $action = optional_param('action', 'nothing', PARAM_ALPHAEXT);
    $debug = optional_param('debug', null, PARAM_BOOL);

    if($action == "measurement_period") {
        $begin_date = required_param('begin_date', PARAM_INT);
        $end_date = required_param('end_date', PARAM_INT);
        $configurator->update([
            "begin_date" => $begin_date, 
            "end_date" => $end_date,
            "debug" => $debug,
        ]);
    }
    else if ($action == "schedule") {
        $scheduled_timestamp = required_param('scheduled_timestamp', PARAM_INT);
        $configurator->schedule_calculation($scheduled_timestamp);
    }
    else if ($action == "unschedule") {
        $configurator->unschedule_calculation();
        $configurator->update_key("debug", $debug);
    }
    else if ($action == "additional_config") {
        $student_archetype = required_param('student_archetype', PARAM_ALPHAEXT);
        $configurator->update([
            "student_archetype" => $student_archetype,
            "debug" => $debug,
        ]);
    }
} else  {
    $task  = optional_param('task', 'nothing', PARAM_ALPHAEXT);

// Reading
    if ($task == "get_usage_coeffs"){
        $output = json_encode($configurator->get_coeffs_grid("usage_coeffs"));
    }
    else if ($task == "get_digitalisation_coeffs"){
        $output = json_encode($configurator->get_coeffs_grid("digitalisation_coeffs"));
    }
    else if ($task == "get_seuils"){
        $output = json_encode($configurator->get_treshold_grid());
    }
    else{
        $output = json_encode($configurator->get_data());
    }

    echo $output;
}
