<?php

namespace report_hybridmetrics\task;

require_once(dirname(__FILE__).'/../traitement.php');

class traitement extends \core\task\adhoc_task {
	public function get_name() {
		// TODO: use strings
        return "Hybrid metrics";
    }
    public function execute() {
    	\report_hybridmetrics\classes\traitement();
    }
}