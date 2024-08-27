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
 * Format config data to be exposed as JSON.
 *
 * @author Nassim Bennouar, Bruno Ilponse, John Tranier
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2020  ISAE-SUPAERO (https://www.isae-supaero.fr/)
 * @package report_hybridmeter
 */
namespace report_hybridmeter\output;

use report_hybridmeter\config;

/**
 * Format config data to be exposed as JSON.
 */
class config_output {

    /**
     * The config
     * @var config
     */
    private $config;

    /**
     * Constructor.
     * @param config $config
     */
    public function __construct(config $config) {
        $this->config = $config;
    }

    /**
     * Get the list of module name rows for the coefficients.
     * @return array
     */
    public function get_all_modulenames_rows(): array {
        $modules = $this->config->get_modules();
        $rows = [];

        foreach ($modules as $modulename) {
            $row = [
                "name" => $modulename,
                "usage_coeff" => $this->config->get_usage_coeff($modulename),
                "digitalisation_coeff" => $this->config->get_digitalisation_coeffs($modulename),
            ];
            array_push($rows, $row);
        }

        $output = [
            "rows" => $rows,
            "count" => count($rows),
        ];

        return $output;
    }

    /**
     * Get the list of rows for the "Threhold" table.
     * @return array
     */
    public function get_tresholds_rows(): array {
        $columns = ["name", "value"];
        $rows = [
            [
                $columns[0] => get_string('digitalisation_treshold', 'report_hybridmeter'),
                $columns[1] => $this->config->get_digitalisation_treshold(),
            ],
            [
                $columns[0] => get_string('usage_treshold', 'report_hybridmeter'),
                $columns[1] => $this->config->get_usage_treshold(),
            ],
            [
                $columns[0] => get_string('active_treshold', 'report_hybridmeter'),
                $columns[1] => $this->config->get_active_treshold(),
            ],
        ];

        return ["rows" => $rows, "count" => count($rows)];
    }

    /**
     * Get the grid of coefficients.
     * @param string $key
     * @return array|false|string
     */
    public function get_coeffs_grid(string $key) {
        $columns = [
            get_string('module_name', 'report_hybridmeter'),
            get_string('coefficient', 'report_hybridmeter'),
        ];

        $coeffs = $this->config->get_coeffs($key);
        if (!isset($coeffs)) {
            return json_encode(
                [
                    "columns" => $columns,
                    "rows" => [],
                ]
            );
        }

        $rows = [];
        $i = 0;
        foreach ($coeffs as $coeff) {
            $rows[$i][$columns[0]] = $coeff["name"];
            $rows[$i][$columns[1]] = $coeff["value"];
            $i++;
        }

        return [
            "columns" => $columns,
            "rows" => $rows,
        ];
    }

    /**
     * Get the grid of threahold values.
     * @return array
     */
    public function get_treshold_grid() {
        $columns = [
            get_string('treshold_name', 'report_hybridmeter'),
            get_string('treshold_value', 'report_hybridmeter'),
        ];
        $rows = [
            [
                $columns[0] => get_string('digitalisation_treshold', 'report_hybridmeter'),
                $columns[1] => $this->config->get_digitalisation_treshold(),
            ],
            [
                $columns[0] => get_string('usage_treshold', 'report_hybridmeter'),
                $columns[1] => $this->config->get_usage_treshold(),
            ],
            [
                $columns[0] => get_string('active_treshold', 'report_hybridmeter'),
                $columns[1] => $this->config->get_active_treshold(),
            ],
        ];

        return [
            "columns" => $columns,
            "rows" => $rows,
        ];
    }
}
