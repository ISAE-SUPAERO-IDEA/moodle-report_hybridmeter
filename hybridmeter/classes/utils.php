<?php

namespace report_hybridmeter\classes;

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

	static function tomorrow_midnight() {
		$tomorrow_midnight = strtotime("tomorrow 00:00");
		return $tomorrow_midnight;
	}
}