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
 * CSV report exporter.
 * @author Nassim Bennouar
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2020  ISAE-SUPAERO (https://www.isae-supaero.fr/)
 * @package report_hybridmeter
 */
namespace report_hybridmeter;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/csvlib.class.php');

use Exception;
use csv_export_writer;

/**
 * CSV report exporter.
 * @package report_hybridmeter
 * @since      Moodle 3.7
 * @copyright  2021 IDEA ISAE-Supaero
 * @author     Nassim Bennouar
 */
class exporter {
    /**
     * Delimitation character.
     * @var string
     */
    protected $delimiter;

    /**
     * The strings in this array correspond to the attributes of $data whose values will be exported.
     * @var array
     */
    protected $fields;

    /**
     * As PHP is weak typed, this attribute is a array which explicitly associate
     * a field with a type to force a certain behaviour.
     *
     * Associate a type to a field is not required to work correctly, but is useful
     * to deal with double variable processed as integer.
     * @var array
     */
    protected $fieldstype;

    /**
     * Human-readable strings associated with fields (and displayed in CSV)
     * If an alias exists then the alias is displayed, otherwise this is the raw field name
     * @var array
     */
    protected $alias;

    /**
     * Attribute containing data to be exported, array of arrays required,
     * please use formatter class to convert from array of objects
     * @var array
     */
    protected $data;

    /**
     * Instance of csv_export_writer of moodle core.
     * @var csv_export_writer
     */
    protected $csv;

    /**
     * Construct an exporter.
     * @param array $fields
     * @param array $alias
     * @param array $fieldstype
     * @param array $data
     * @param string $delimiter
     * @throws Exception
     */
    public function __construct(array  $fields=[],
                                array  $alias = [],
                                array  $fieldstype = [],
                                array  $data = [],
                                string $delimiter = 'comma') {
        $this->set_data($data);
        if (empty($fields) && !empty($this->data)) {
            $this->auto_fields();
        } else {
            $this->set_fields($fields);
        }
        $this->set_alias($alias);
        $this->set_fieldstype($fieldstype);
        $this->set_delimiter($delimiter);
    }

    /**
     * Gets the keys of the first tuple and sets them as fields of the outgoing file.
     */
    public function auto_fields() {
        $this->fields = [];

        if (!is_array($this->data) || count($this->data) == 0) {
            throw new Exception("Fields cannot be calculated automatically if there is no data");
        }

        foreach ($this->data[array_keys($this->data)[0]] as $key => $value) {
            array_push($this->fields, $key);
        }
    }

    /**
     * Manually set fields of the CSV file.
     * @param array $fields
     */
    public function set_fields(array $fields) {
        $preconditionarray = array_map('is_string', $fields);
        if (in_array(false, $preconditionarray)) {
            throw new Exception("\$fields must be an array of strings");
        }

        $this->fields = $fields;
    }

    /**
     * Add a tuple to the $data array.
     * @param array $data
     */
    public function add_data(array $data) {
        array_push($this->data, $data);
    }

    /**
     * Setter of "data".
     * @param array $data
     * @return void
     * @throws Exception
     */
    public function set_data(array $data) {
        $preconditionarray = array_map('is_array', $data);
        if (in_array(false, $preconditionarray)) {
            throw new Exception("The data must be passed to the exporter in the form of a table of tables");
        }

        $this->data = $data;
    }

    /**
     * Setter of "alias".
     * @param array $alias
     * @return void
     */
    public function set_alias(array $alias) {
        $this->alias = $alias;
    }

    /**
     * Setter of "fieldstype".
     * @param array $fieldstype
     * @return void
     */
    public function set_fieldstype(array $fieldstype) {
        $this->fieldstype = $fieldstype;
    }

    /**
     * Setter of "delimiter".
     * @param string $delimiter
     * @return void
     */
    public function set_delimiter(string $delimiter) {
        $this->delimiter = $delimiter;
        $this->csv = new csv_export_writer($this->delimiter);
    }

    /**
     * Construct the header using alias.
     * @return array
     */
    private function construct_fields_name(): array {
        $output = [];

        foreach ($this->fields as $field) {
            if (array_key_exists($field, $this->alias)) {
                $value = $this->alias[$field];
            } else {
                $value = $field;
            }

            array_push($output, $value);
        }

        return $output;
    }

    /**
     * Export the CSV file.
     * @param string $filename
     * @return void
     */
    public function create_csv(string $filename) {
        $this->csv->set_filename($filename);
        $this->csv->add_data($this->construct_fields_name());

        foreach ($this->data as $key => $record) {
            $row = [];
            foreach ($this->fields as $key => $field) {
                $value = $this->format_value($record, $key, $field);
                array_push($row, $value);
            }
            $this->csv->add_data($row);
        }
    }

    /**
     * Format a value.
     * @param array $record
     * @param string $key
     * @param string $field
     * @return mixed|string
     */
    protected function format_value(array $record, $key, $field) {
        if (
            in_array($field, array_keys($this->fieldstype))
            && ($this->fieldstype[$field] == REPORT_HYBRIDMETER_DOUBLE || $this->fieldstype[$field] == REPORT_HYBRIDMETER_FLOAT)
        ) {
            return sprintf("%.2f", $record[$field]);
        }

        return $record[$field];
    }


    /**
     * Download the CSV file.
     * @return void
     */
    public function download_file() {
        $this->csv->download_file();
    }
}
