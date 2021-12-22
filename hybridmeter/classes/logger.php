<?php

namespace report_hybridmeter\classes;
use \report_hybridmeter\classes\configurator as configurator;

defined('MOODLE_INTERNAL') || die();

// Hybridmeter's logger
class logger {
	public static function log($object) {
		if (configurator::getInstance()->get_debug()) {
			error_log(print_r($object, 1));
		}
    }
}

