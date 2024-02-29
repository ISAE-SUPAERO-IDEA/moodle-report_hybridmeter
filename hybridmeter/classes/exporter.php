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

namespace report_hybridmeter\classes;

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__)."/../../../config.php");
require_once(dirname(__FILE__).'/configurator.php');
global $CFG;
require_once($CFG->libdir . '/csvlib.class.php');

use Exception;
use csv_export_writer;

/**
 * This class allows you to export an array of tuples as a CSV string or as a
 * CSV file downloaded from the browser, with the possibility to customise
 * the visible fields
 * 
 * 
 * @package    report_hybridmeter
 * @since      Moodle 3.7
 * @copyright  2021 IDEA ISAE-Supaero
 * @author     Nassim Bennouar
 */
class exporter {
    // Delimitation character
    protected $delimiter;

    // The strings in this array correspond to the attributes of $data whose values will be exported
    protected $fields;

    /* As PHP is weak typed, this attribute is a array which explicitly associate 
     * a field with a type to force a certain behaviour.
     * 
     * Associate a type to a field is not required to work correctly, but is useful
     * to deal with double variable processed as integer.
     * 
     */
    protected $fields_type;

    /* Human-readable strings associated with fields (and displayed in CSV)
     * If an alias exists then the alias is displayed, otherwise this is the raw field name
     */
    protected $alias;

    /* Attribute containing data to be exported, array of arrays required,
     * please use formatter class to convert from array of objects
     */ 
    protected $data;

    // Instance of csv_export_writer of moodle core
    protected $csv;

    public function __construct(array $fields=array(), array $alias = array(), array $fields_type = array(), array $data = array(), $delimiter = 'comma'){
        $this->set_data($data);
        if(empty($fields) && !empty($this->data))
            $this->auto_fields();
        else
            $this->set_fields($fields);
        $this->set_alias($alias);
        $this->set_fields_type($fields_type);
        $this->set_delimiter($delimiter);
    }

    // Gets the keys of the first tuple and sets them as fields of the outgoing file
    public function auto_fields(){
        $this->fields=array();

        if(!is_array($this->data) || sizeof($this->data)==0)
            throw new Exception("Fields cannot be calculated automatically if there is no data");

        foreach ($this->data[array_keys($this->data)[0]] as $key => $value){
            array_push($this->fields, $key);
        }
    }

    // Manually set fields of the CSV file
    public function set_fields (array $fields){
        $precondition_array = array_map('is_string', $fields);
        if(in_array(false, $precondition_array)){
            throw new Exception("\$fields must be an array of strings");
        }

        $this->fields = $fields;
    }

    // Add a tuple to the $data array
    public function add_data(array $data){
        array_push($this->data, $data);
    }

    // Set $data array
    public function set_data(array $data){
        $precondition_array = array_map('is_array', $data);
        if(in_array(false, $precondition_array)){
            throw new Exception("The data must be passed to the exporter in the form of a table of tables");
        }

        $this->data = $data;
    }

    public function set_alias(array $alias){
        $this->alias = $alias;
    }

    public function set_fields_type(array $fields_type) {
        $this->fields_type = $fields_type;
    }

    public function set_delimiter(string $delimiter){
        $this->delimiter=$delimiter;
        $this->csv=new csv_export_writer($this->delimiter);
    }

    private function construct_fields_name(): array {
        $output=array();

        foreach ($this->fields as $field){
            if(array_key_exists($field,$this->alias))
                $value = $this->alias[$field];
            else
                $value = $field;

            array_push($output, $value);
        }

        return $output;
    }

    /* TODO : improve workflow */

    public function create_csv(string $filename) {
        $this->csv->set_filename($filename);
        $this->csv->add_data($this->construct_fields_name());

        foreach ($this->data as $key => $record) {
            $row = array();
            foreach ($this->fields as $key => $field) {
                $value = $this->format_value($record,$key,$field);
                array_push($row, $value);
            }
            $this->csv->add_data($row);
        }
    }

    protected function format_value(array $record, $key, $field) {
        if (
            in_array($field, array_keys($this->fields_type))
            && ($this->fields_type[$field] == REPORT_HYBRIDMETER_DOUBLE || $this->fields_type[$field] == REPORT_HYBRIDMETER_FLOAT)
        ) {
            return sprintf("%.2f", $record[$field]);
        }

        return $record[$field];
    }
    
    public function print_csv_data_standard(){
        return $this->csv->print_csv_data(false);
    }

    public function csv_data_to_string(): string {
        return $this->csv->print_csv_data(true);
    }

    public function download_file(){
        $this->csv->download_file();
    }
}
