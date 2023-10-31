<?php

namespace report_hybridmeter\classes;

defined('MOODLE_INTERNAL') || die();

class test_context {
    protected static function error_handler($errno, $errstr, $errfile, $errline) {
        echo "<p><strong>ERROR : ".$errno." ".$errstr." ".$errfile." ".$errline."</strong></p><br/><br/>";
    }

    protected static function fatal_handler() {
        $last_error = error_get_last();
        if($last_error !== null) {
            self::error_handler($last_error['type'], $last_error['str']);
        }
    }

    public static function launch(test_scenario $test){
        $this->launch_batch(array($test));
    }

    public static function launch_batch(array $test_set){
        $old_error_reporting = ini_get('error_reporting');
        error_reporting(0);
        //set_error_handler("diagnostic_component::error_handler");
        //register_shutdown_function("diagnostic_component::fatal_handler");
        foreach ($test_set as $test) {
            $test->test();
            echo "<hr/>";
        }
        error_reporting($old_error_reporting);
    }
}
