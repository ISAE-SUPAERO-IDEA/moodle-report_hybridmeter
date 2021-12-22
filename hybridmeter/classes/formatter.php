<?php

namespace report_hybridmeter\classes;

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/logger.php');

// TODO: Expliquer ce que cette classe formatte
// TODO : Refactoriser (P3)
class formatter {
	//Le tableau structuré
	protected $array;

	public function __construct($lambda){
		$this->array = array();
		$this->import_objects_array($lambda());
	}
	
	protected function import_objects_array(Array $objectarr){
		foreach($objectarr as $key => $object){
			$this->array[$key]=$this->object_to_array($object);
		}
	}
	
	protected function object_to_array($object){
		$array=array();
		foreach ($object as $key => $value){
			$array[$key]=$value;
		}
		return $array;
	}

	//ajout d'un nouvel indicateur définit par une fonctoin lambda qui prend en paramètres
	public function calculate_new_indicator($lambda, String $indicator_name, $parameters=array()){
		$i = 1;
		foreach($this->array as $key => $value){
			logger::log($indicator_name." id=". $key." (".$i."/".count($this->array).")");
			$this->array[$key][$indicator_name]=$lambda($value,$parameters);
			$i++;
		}
	}

	public function get_array(){
		return $this->array;
	}

	public function get_length_array(){
		return count($this->array);
	}
}
