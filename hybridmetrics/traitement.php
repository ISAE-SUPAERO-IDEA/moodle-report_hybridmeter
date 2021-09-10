<?php

require(dirname(__FILE__).'/../../config.php');
require_once('classes/traitement.php');


$data = report_hybridmetrics\classes\traitement();

echo json_encode($data);

?>