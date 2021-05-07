<?php

namespace report_hybridation\classes;

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/data.php');

class formatter {
	//Le tableau structuré
	protected $array;

	//Les données brutes
	protected $data;

	//La blacklist
	protected $blacklist;

	public function __construct($blacklist,$lambda){
		$this->array = array();
		$this->blacklist = $blacklist;
		$this->data=new \report_hybridation\classes\data();
		$this->import_objects_array($lambda($this->data, $this->blacklist));
	}

	//Structureur de données, structure les données de data dans array
	protected function import_objects_array(Array $objectarr){
		foreach($objectarr as $key => $object){
			$this->array[$key]=$this->object_to_array($object);
		}
	}

	public function add_to_blacklist(int $id){
		array_push($this->blacklist, $id);
	}

	public function get_blacklist(){
		return $this->blacklist;
	}

	public function add_list_to_blacklist(array $idarray){
		array_push($this->blacklist, ...$idarray);
	}
	
	protected function object_to_array(Object $object){
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