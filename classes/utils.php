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

use report_hybridmeter\formatter as formatter;
use Exception;
use DateTime;

/**
 * Facilities functions.
 */
class utils {

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
     * Render an array of objects as HTML.
     *
     * @param array $array
     * @return string
     */
    public static function objects_array_to_html(array $array): string {
        if (empty($array)) {
            return "No data";
        }
        $array = (new formatter($array))->get_array();

        $output = "<table>";
        $output .= "<thead><tr>";
        $keys = array_keys(current($array));
        foreach ($keys as $key) {
            $output .= "<th>".$key."</th>";
        }
        $output .= "</tr></thead>";
        $output .= "<tbody>";
        foreach ($array as $elem) {
            $output .= "<tr>";
            foreach ($keys as $key) {
                $output .= "<td>".$elem[$key]."</td>";
            }
            $output .= "</tr>";
        }
        $output .= "</tbody>";
        $output .= "</table>";

        return $output;
    }

    /**
     * Display an associative array as an HTML table.
     *
     * @param array $array
     * @return string
     */
    public static function data_grouped_by_to_html(array $array): string {
        $output = "<table>";
        $output .= "<tbody>";
        foreach ($array as $key => $elem) {
            $output .= "<tr>";
            $output .= "<th>";
            $output .= $key;
            $output .= "</th>";
            $output .= "<td>";
            $output .= $elem;
            $output .= "</td>";
            $output .= "</tr>";
        }
        $output .= "</tbody>";
        $output .= "</table>";

        return $output;
    }

    /**
     * Display an array as an HTML table with headers.
     *
     * @param array $array
     * @return string
     */
    public static function columns_rows_array_to_html(array $array): string {
        $output = "<table>";
        $output .= "<thead><tr>";
        foreach ($array['columns'] as $key) {
            $output .= "<th>".$key."</th>";
        }
        $output .= "</tr></thead>";
        $output .= "<tbody>";
        foreach ($array['rows'] as $elem) {
            $output .= "<tr>";
            foreach ($array['columns'] as $key) {
                $output .= "<td>".$elem[$key]."</td>";
            }
            $output .= "</tr>";
        }
        $output .= "</tbody>";
        $output .= "</table>";

        return $output;
    }

    /**
     * Render an array in HTML using n_uplets style.
     *
     * @param array $array
     * @param int $n
     * @return string
     */
    public static function array_to_n_uplets_table_html(array $array, int $n = 10): string {
        $output = "<table>";
        $output .= "<tbody>";
        $length = count($array);
        $i = 0;
        $output .= "<tr class=\"n_uplets\">";
        while ($i < $length) {
            if ($i != 0 && ($i % $n) === 0) {
                $output .= "</tr><tr>";
            }
            $output .= "<td>".$array[$i]."</td>";
            $i++;
        }
        $output .= "</tr>";
        $output .= "</tbody>";
        $output .= "</table>";

        return $output;
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
     * @param $x
     * @param int $n
     * @return int
     */
    public static function modulo_fixed($x, int $n): int {
        $r = $x % $n;
        if ($r < 0) {
            $r += abs($n);
        }
        return $r;
    }
}
