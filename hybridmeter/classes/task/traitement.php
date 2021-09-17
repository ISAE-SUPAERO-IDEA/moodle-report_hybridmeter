<?php

namespace report_hybridmeter\task;

require_once(dirname(__FILE__).'/../traitement.php');

class traitement extends \core\task\adhoc_task {
	public function get_name() {
        return get_string('pluginname', 'report_hybridmeter');
    }
    
    public function execute() {
    	$traitement = new \report_hybridmeter\classes\traitement();
        $traitement->launch();
    }
}