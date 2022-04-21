<?php

require_once(dirname(__FILE__)."/../../../config.php");
require_once(__DIR__."/../classes/configurator.php");
use \report_hybridmeter\classes\configurator as configurator;

header('Content-Type: text/json');

//Vérification des autorisations (rôle admin obligatoire)

require_login();

$context = \context_system::instance();
$PAGE->set_context($context);
has_capability('report/hybridmeter:all', $context) || die();

/*$configurator = configurator::getInstance();

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $action = required_param('action', PARAM_ALPHAEXT);
	switch($action) {
        case 'NU_nul':
            $id = required_param('id', PARAM_INT);
            (new nu_nul_scenario($id))->test();
            break;
        case 'ND_nul':
            $id = required_param('id', PARAM_INT);
            (new nu_nul_scenario($id))->test();
            break;
        default : 
            //TODO : Codes HTTP
            echo "Jeu de tests inconnu";
            break;
    } 
}
*/