<?php

namespace report_hybridmeter\classes;

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/data.php');

// TODO: Expliquer ce que cette classe formatte
// TODO : Refactoriser (P3)
class formatter {
	//Le tableau structuré
	protected $array;

	//Les données brutes
	protected $data;

	public function __construct($data, $blacklist,$lambda){
		$this->array = array();
		$this->data=$data;
		$this->import_objects_array($lambda($this->data, $blacklist));
	}

	//Structureur de données, structure les données de data dans array
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
		foreach($this->array as $key => $value){
			$this->array[$key][$indicator_name]=$lambda($value,$this->data,$parameters);
		}
	}

	public function get_array(){
		return $this->array;
	}

	public function get_length_array(){
		return count($this->array);
	}
}
