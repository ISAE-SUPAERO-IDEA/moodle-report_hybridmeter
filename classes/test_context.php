<?php
// This file is part of Moodle - http://moodle.org
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @author Nassim Bennouar
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2020  ISAE-SUPAERO (https://www.isae-supaero.fr/)
 * @package report_hybridmeter
 */
namespace report_hybridmeter;

class test_context {
    protected static function error_handler($errno, $errstr, $errfile, $errline) {
        echo "<p><strong>ERROR : ".$errno." ".$errstr." ".$errfile." ".$errline."</strong></p><br/><br/>";
    }

    protected static function fatal_handler() {
        $lasterror = error_get_last();
        if ($lasterror !== null) {
            self::error_handler($lasterror['type'], $lasterror['str']);
        }
    }

    public static function launch(test_scenario $test) {
        self::launch_batch([$test]);
    }

    public static function launch_batch(array $testset) {
        $olderrorreporting = ini_get('error_reporting');
        error_reporting(0);
        foreach ($testset as $test) {
            $test->test();
            echo "<hr/>";
        }
        error_reporting($olderrorreporting);
    }
}
