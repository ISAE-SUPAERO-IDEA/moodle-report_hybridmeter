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
 * @author Nassim Bennouar, Bruno Ilponse
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2020  ISAE-SUPAERO (https://www.isae-supaero.fr/)
 * @package
 */
namespace report_hybridmeter\classes;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__."/formatter.php");

use report_hybridmeter\classes\formatter as formatter;
use Exception;
use DateTime;

class utils {
    public static function object_to_array(object $object) {
        $array = [];
        foreach ($object as $key => $value) {
            $array[$key] = $value;
        }
        return $array;
    }

    public static function id_objects_array_to_array(array $array) {
        return array_values(array_map(function($obj) {
            return $obj->id;
        }, $array));
    }

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

    public static function tomorrow_midnight() {
        $tomorrowmidnight = strtotime("tomorrow 00:00");
        return $tomorrowmidnight;
    }

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

    public static function timestamp_to_datetime(int $timestamp, string $format = 'd/m/Y H:i:s e'): string {
        $datetime = new DateTime();
        $datetime->setTimestamp($timestamp);

        return $datetime->format($format);
    }

    public static function modulo_fixed($x, int $n): int {
        $r = $x % $n;
        if ($r < 0) {
            $r += abs($n);
        }
        return $r;
    }
}
