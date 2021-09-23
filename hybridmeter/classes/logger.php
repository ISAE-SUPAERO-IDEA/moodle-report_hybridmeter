<?php

namespace report_hybridmeter\classes;
require_once(__DIR__."/configurator.php");

defined('MOODLE_INTERNAL') || die();

// Hybridmeter's logger
class logger {
	public static function log($object) {
		if (configurator::getInstance()->get_debug()) {
			error_log(print_r($object, 1));
		}
    }
}

