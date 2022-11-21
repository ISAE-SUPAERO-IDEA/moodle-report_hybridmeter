<?php

namespace report_hybridmeter\classes;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__."/configurator.php");

use \report_hybridmeter\classes\configurator as configurator;

// Hybridmeter's logger
class logger {
    private static function var_dump_ret($object) {
        ob_start();
        var_dump($object);
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }

    public static function log_var_dump($object) {
        if (configurator::get_instance()->get_debug()) {
            error_log(self::var_dump_ret($object));
        }
    }
      
    public static function log($object) {
        if (configurator::get_instance()->get_debug()) {
            error_log(print_r($object, 1));
        }
    }

    public static function file_log($object, $filename) {
        if (configurator::get_instance()->get_debug()) {
            $file = fopen(dirname(__FILE__).'/../'.$filename, 'a');
            fwrite($file, print_r($object,1));
        }
    }
}
