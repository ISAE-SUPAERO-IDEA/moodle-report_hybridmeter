<?php
// This file is part of Moodle - http://moodle.org
//
//  Moodle is free software: you can redistribute it and/or modify
//  it under the terms of the GNU General Public License as published by
//  the Free Software Foundation, either version 3 of the License, or
//  (at your option) any later version.
//
//  Moodle is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU General Public License for more details.
//
//  You should have received a copy of the GNU General Public License
//  along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @author Nassim Bennouar
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2020  ISAE-SUPAERO (https://www.isae-supaero.fr/)
 */
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
