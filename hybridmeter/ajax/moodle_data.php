<?php
//TODO : Standardize APIs response
/*
AJAX endpoint to manage HybridMeter configuration

*/
require_once(dirname(__FILE__)."/../../../config.php");

header('Content-Type: text/json');

//Checking authorizations (admin role required)

require_login();

$context = \context_system::instance();
$PAGE->set_context($context);
has_capability('report/hybridmeter:all', $context) || die();

$task  = required_param('task', PARAM_ALPHAEXT);

if ($task == "roles"){
    $roles = $DB->get_records("role");
    $output = json_encode(array_values($roles));
}
else{
    $output = json_encode([]);
}

echo $output;
