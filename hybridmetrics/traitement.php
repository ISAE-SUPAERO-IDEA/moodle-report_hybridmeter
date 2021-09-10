<?php

require(dirname(__FILE__).'/../../config.php');
require_once('classes/traitement.php');


$traitement = new report_hybridmetrics\classes\traitement();
$data = $traitement->launch();

echo json_encode($data);

?>