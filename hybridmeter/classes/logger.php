<?php

namespace report_hybridmeter\classes;

require_once(__DIR__."/configurator.php");

use \report_hybridmeter\classes\configurator as configurator;

defined('MOODLE_INTERNAL') || die();

// Hybridmeter's logger
class logger {
	public static function log($object) {
		if (configurator::getInstance()->get_debug()) {
			error_log(print_r($object, 1));
		}
    }

	public static function file_log($object, $filename) {
		if (configurator::getInstance()->get_debug()) {
			$file = fopen(dirname(__FILE__).'/../'.$filename, 'a');
			fwrite($file, print_r($object,1));
		}
	}
}

