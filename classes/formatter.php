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
 * @package
 */
namespace report_hybridmeter;

use Exception;

class formatter {

    protected $data;

    public function __construct(array $data) {
        $this->precondition_record($data);
        $this->data = $this->objects_array_to_2D_array($data);
    }

    protected function objects_array_to_2d_array(array $data): array {
        return (array) array_map(
            function($element) {
                return (array) $element;
            },
            $data
        );
    }

    protected function precondition_record(array $data) {
        $accumulatedprecondition = array_reduce(
            $data,
            function($acc, $record) {
                return ($acc && (is_object($record) || is_array($record)));
            },
            true
        );

        if (!$accumulatedprecondition) {
            throw new Exception("The data is not formatted correctly, a record is an array of objects or a two dimensional array");
        }
    }

    public function calculate_new_indicator($lambda, string $indicatorname, array $parameters = []) {
        $i = 1;
        foreach ($this->data as $key => $value) {
            logger::log("Computing indicator ".$indicatorname." for course id=". $key." (".$i."/".count($this->data).")");
            $this->data[$key][$indicatorname] = $lambda($value, $parameters);
            $i++;
        }
    }

    public function get_array(): array {
        return $this->data;
    }

    public function get_length_array(): int {
        return count($this->data);
    }
}
