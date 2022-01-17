<?php

namespace report_hybridmeter\task;

require_once(dirname(__FILE__).'/../traitement.php');
require_once(dirname(__FILE__).'/../configurator.php');

// Adhoc task that produces hybridmeter's serialized data
class traitement extends \core\task\adhoc_task {
	public function get_name() {
        return get_string('pluginname', 'report_hybridmeter');
    }
    
    public function execute() {
        \report_hybridmeter\classes\configurator::getInstance()->unschedule_calculation();
    	$traitement = new \report_hybridmeter\classes\traitement();
        $traitement->launch();
    }
}