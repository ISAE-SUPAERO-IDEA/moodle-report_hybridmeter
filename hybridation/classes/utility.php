<?php

static function object_to_array(Object $object){
	$array=array();
	foreach ($object as $key => $value){
		$array[$key]=$value;
	}
	return $array;
}