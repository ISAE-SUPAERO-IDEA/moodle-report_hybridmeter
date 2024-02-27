<?php
/*
 * Hybrid Meter
 * Copyright (C) 2020 - 2024  ISAE-SUPAERO
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace report_hybridmeter\classes;

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/logger.php');

use Exception;

class formatter {
    
    protected $data;

    public function __construct(array $data){
        $this->precondition_record($data);
        $this->data = $this->objects_array_to_2D_array($data);
    }

    protected function objects_array_to_2D_array(array $data): array {
        return (array) array_map(
            function($element){
                return (array) $element;
            },
            $data
        );
    }

    protected function precondition_record(array $data) { 
        $accumulated_precondition = array_reduce(
            $data,
            function($acc, $record) {
                return ($acc && (is_object($record) || is_array($record)));
            },
            true
        );

        if (!$accumulated_precondition) {
            throw new Exception("The data is not formatted correctly, a record is an array of objects or a two dimensional array");
        }
    }

    public function calculate_new_indicator($lambda, string $indicator_name, array $parameters = array()){
        $i = 1;
        foreach($this->data as $key => $value){
            logger::log("Computing indicator ".$indicator_name." for course id=". $key." (".$i."/".count($this->data).")");
            $this->data[$key][$indicator_name]=$lambda($value,$parameters);
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
