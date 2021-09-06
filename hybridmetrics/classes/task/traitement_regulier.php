<?php

namespace report_hybridmetrics\task;

require_once(dirname(__FILE__).'/../traitement.php');

class traitement_regulier extends \core\task\scheduled_task {
	public function get_name(){
		return "Hybridmetrics";
	}

    public function execute() {
    	\traitement();
    }
}