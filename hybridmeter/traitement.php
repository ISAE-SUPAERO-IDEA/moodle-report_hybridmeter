<?php

require(dirname(__FILE__).'/../../config.php');
require_once('classes/traitement.php');

require_login();
$context = context_system::instance();
$PAGE->set_context($context);
has_capability('report/hybridmeter:all', $context) || die();

$traitement = new report_hybridmeter\classes\traitement();
$data = $traitement->launch();

echo json_encode($data);

?>