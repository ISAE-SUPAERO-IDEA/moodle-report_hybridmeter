<?php

namespace report_hybridmetrics\task;

require_once(dirname(__FILE__).'/../traitement.php');

class traitement extends \core\task\adhoc_task {
	public function get_name() {
        return get_string('pluginname', 'report_hybridmetrics');
    }
    
    public function execute() {
    	$traitement = new report_hybridmetrics\classes\traitement();
        $traitement->launch();
    }
}