<?php

namespace report_hybridmeter\task;

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__)."/../../../../config.php");
require_once(dirname(__FILE__).'/../processing.php');
require_once(dirname(__FILE__).'/../configurator.php');

use \report_hybridmeter\classes\processing as class_processing;

// Adhoc task that produces hybridmeter's serialized data
class processing extends \core\task\adhoc_task {
    public function get_name() {
        return get_string('pluginname', 'report_hybridmeter');
    }
    
    public function execute() {
        \report_hybridmeter\classes\configurator::get_instance()->unschedule_calculation();
        $processing = new class_processing();
        $processing->launch();
    }
}