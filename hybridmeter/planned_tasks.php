<?php

require(dirname(__FILE__).'/../../config.php');
require_once('classes/data_provider.php');

use \report_hybridmeter\classes\data_provider as data_provider;

require_login();
$context = context_system::instance();
$PAGE->set_context($context);
has_capability('report/hybridmeter:all', $context) || die();

$planned_tasks = data_provider::getInstance()->get_adhoc_tasks_list();

echo json_encode($planned_tasks);