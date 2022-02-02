<?php

namespace report_hybridmeter\classes;

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/logger.php');

// TODO: Expliquer ce que cette classe formatte
// TODO : Refactoriser (P3)
class formatter {
	//Le tableau structuré
	protected $data;

	public function __construct(array $data){
		$this->precondition_record($data);
		$this->data = $this->objects_array_to_2D_array($data);
	}

	protected function objects_array_to_2D_array(array $data) {
		return (array) array_map(
			function($element){
				return (array) $element;
			},
			$data
		);
	}

	protected function precondition_record($data) { 
		$precondition_array = array_map(function ($record) {
			return (is_object($record) || is_array($record));
		}, $data);

		if (in_array(false, $precondition_array)) {
			throw new Exception("Les données ne sont pas formatées correctement");
		}
	}

	//ajout d'un nouvel indicateur définit par une fonctoin lambda qui prend en paramètres
	public function calculate_new_indicator($lambda, String $indicator_name, $parameters=array()){
		$i = 1;
		foreach($this->data as $key => $value){
			logger::log($indicator_name." id=". $key." (".$i."/".count($this->data).")");
			$this->data[$key][$indicator_name]=$lambda($value,$parameters);
			$i++;
		}
	}

	public function get_array(){
		return $this->data;
	}

	public function get_length_array(){
		return count($this->data);
	}
}
