<?php

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
            logger::log($indicator_name." id=". $key." (".$i."/".count($this->data).")");
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
