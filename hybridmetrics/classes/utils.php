<?php

namespace report_hybridmetrics\classes;

defined('MOODLE_INTERNAL') || die();

class utils{
	static function object_to_array(Object $object){
		$array=array();
		foreach ($object as $key => $value){
			$array[$key]=$value;
		}
		return $array;
	}

	static function id_objects_array_to_array(array $array){
		return array_values(array_map(function($obj) {
			return $obj->id;
		}, $array));
	}
}