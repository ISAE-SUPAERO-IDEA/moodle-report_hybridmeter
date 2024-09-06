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
 * Facilities functions.
 *
 * @author Nassim Bennouar, Bruno Ilponse
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2020  ISAE-SUPAERO (https://www.isae-supaero.fr/)
 * @package report_hybridmeter
 */
namespace report_hybridmeter;

use Exception;
use DateTime;

/**
 * Facilities functions.
 */
class utils {

    /**
     * Create directory structure for plugin local storage.
     * @return void
     * @package report_hybridmeter
     */
    public static function create_hybridmeter_dirs() {
        global $CFG;
        $path = $CFG->dataroot."/hybridmeter/records/backup";
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
    }

    /**
     * Remove directory structure of the plugin local storage.
     * @return void
     * @package report_hybridmeter
     */
    public static function rm_hybridmeter_dirs() {
        global $CFG;
        $path = $CFG->dataroot."/hybridmeter";

        if (is_dir($path)) {
            self::rm_dir_rec($path);
        }
    }

    /**
     * Remove a directory and all its content.
     * @param string $path
     * @return bool
     * @package report_hybridmeter
     */
    protected static function rm_dir_rec($path): bool {
        $files = array_diff(scandir($path), ['.', '..']);
        foreach ($files as $file) {
            (is_dir("$path/$file")) ? self::rm_dir_rec("$path/$file") : unlink("$path/$file");
        }
        return rmdir($path);
    }

    /**
     * Checks that an array contains only int values.
     *
     * @param array $idscourses
     * @return void
     * @throws Exception
     */
    public static function precondition_ids(array $idscourses) {

        $accumulatedprecondition = array_reduce(
            $idscourses,
            function($acc, $id) {
                return ($acc && is_int($id));
            },
            true
        );
        if (!$accumulatedprecondition) {
            throw new Exception("IDs must be integers");
        }
    }

    /**
     * Returns the timestamp for tomorrow at midnight.
     *
     * @return false|int
     */
    public static function tomorrow_midnight() {
        $tomorrowmidnight = strtotime("tomorrow 00:00");
        return $tomorrowmidnight;
    }

    /**
     * Convert a timestamp to a datetime.
     *
     * @param int $timestamp
     * @param string $format
     * @return string
     */
    public static function timestamp_to_datetime(int $timestamp, string $format = 'd/m/Y H:i:s e'): string {
        $datetime = new DateTime();
        $datetime->setTimestamp($timestamp);

        return $datetime->format($format);
    }

    /**
     * Modulo operation ensuring a positive result.
     *
     * @param int $x
     * @param int $n
     * @return int
     */
    public static function modulo_fixed(int $x, int $n): int {
        $r = $x % $n;
        if ($r < 0) {
            $r += abs($n);
        }
        return $r;
    }

    /**
     * Get the release version of HybridMeter.
     * @return string
     */
    public static function get_release_from_plugin() {
        $pluginpath = \core_component::get_plugin_directory("report", "hybridmeter");

        $plugin = new \stdClass();
        require $pluginpath.'/version.php';

        return $plugin->release;
    }
}
