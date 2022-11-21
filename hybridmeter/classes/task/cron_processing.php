<?php

namespace report_hybridmeter\task;

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/../processing.php');
require_once(dirname(__FILE__)."/../../../../config.php");

use \report_hybridmeter\classes\processing as processing;

// Scheduled task that produces hybridmeter's serialized data
class cron_processing extends \core\task\scheduled_task {
    public function get_name(){
        return get_string('pluginname', 'report_hybridmeter');
    }

    public function execute() {
        $processing = new processing();
        $processing->launch();
    }
}