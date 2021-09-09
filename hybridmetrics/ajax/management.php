<?php

define('AJAX_SCRIPT', true);

require_once('../../../config.php');
require_once('../classes/management_controller.php');
require_once('../output/management/management_renderer.php');
require_once('../classes/data.php');
require_once($CFG->dirroot.'/course/lib.php');

use \report_hybridmetrics\classes\management_controller;

$action = required_param('action', PARAM_ALPHA);
//require_sesskey(); // Gotta have the sesskey.
require_login(); // Gotta be logged in (of course).
$PAGE->set_context(context_system::instance());
$data=new \report_hybridmetrics\classes\data();
$controller=new \report_hybridmetrics\classes\management_controller($data);
$output=array();

$id=required_param('id', PARAM_INT);

$outcome = new stdClass;
$outcome->error = false;
$outcome->outcome = false;


echo $OUTPUT->header();

switch($action){
	case 'blacklistcourse' :
		$outcome->error=!$controller->blacklistcourse($id);
		$outcome->outcome = true;
		break;
	case 'blacklistcategory' :
		$outcome->error=!$controller->blacklistcategory($id);
		$outcome->outcome = true;
		break;
	case 'whitelistcourse' :
		$outcome->error=!$controller->whitelistcourse($id);
		$outcome->outcome = true;
		break;
	case 'whitelistcategory' :
		$outcome->error=!$controller->whitelistcategory($id);
		$outcome->outcome = true;
		break;
	default :
		$outcome->error=true;
		break;
}

echo json_encode($outcome);
echo $OUTPUT->footer();
exit;