<?php

require(dirname(__FILE__).'/../../config.php');
require_once('classes/processing.php');

require_login();
$context = context_system::instance();
$PAGE->set_context($context);
has_capability('report/hybridmeter:all', $context) || die();

$processing = new report_hybridmeter\classes\processing();
$data = $processing->launch();

//echo "<a href='index.php?debug=1'>index.php?debug=1</a>";
echo json_encode($data);
