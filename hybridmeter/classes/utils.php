<?php

namespace report_hybridmeter\classes;

require_once(__DIR__."/formatter.php");

defined('MOODLE_INTERNAL') || die();

class utils{
	public static function object_to_array(Object $object){
		$array=array();
		foreach ($object as $key => $value){
			$array[$key]=$value;
		}
		return $array;
	}

	public static function id_objects_array_to_array(array $array){
		return array_values(array_map(function($obj) {
			return $obj->id;
		}, $array));
	}

    public static function precondition_ids($ids_courses) {
        if(!is_array($ids_courses)){
            $ids_courses=array($ids_courses);
        }

        $precondition_array = array_map('is_numeric', $ids_courses);

        if(in_array(false, $precondition_array))
            throw new \Error("Les IDs doivent être des entiers");
    }

	public static function tomorrow_midnight() {
		$tomorrow_midnight = strtotime("tomorrow 00:00");
		return $tomorrow_midnight;
	}

	public static function objects_array_to_html($array) {
        $array = (new \report_hybridmeter\classes\formatter($array))->get_array();

        $output = "<table>";
        $output .= "<thead><tr>";
        $keys = array_keys(current($array));
        foreach ($keys as $key) {
            $output .= "<th>".$key."</th>";
        }
        $output .= "</tr></thead>";
        $output .= "<tbody>";
        foreach ($array as $elem) {
            $output .= "<tr>";
            foreach ($keys as $key) {
                $output .= "<td>".$elem[$key]."</td>";
            }
            $output .= "</tr>";
        }
        $output .= "</tbody>";
        $output .= "</table>";

        return $output;
    }

    public static function data_grouped_by_to_html($array) {
        $output = "<table>";
        $output .= "<tbody>";
        foreach ($array as $key =>$elem) {
            $output .= "<tr>";
			$output .= "<th>";
            $output .= $key;
            $output .= "</th>";
			$output .= "<td>";
            $output .= $elem;
			$output .= "</td>";
            $output .= "</tr>";
        }
        $output .= "</tbody>";
        $output .= "</table>";

		return $output;
    }

    public static function columns_rows_array_to_html($array) {
        $output = "<table>";
        $output .= "<thead><tr>";
        foreach ($array['columns'] as $key) {
            $output .= "<th>".$key."</th>";
        }
        $output .= "</tr></thead>";
        $output .= "<tbody>";
        foreach ($array['rows'] as $elem) {
            $output .= "<tr>";
            foreach ($array['columns'] as $key) {
                $output .= "<td>".$elem[$key]."</td>";
            }
            $output .= "</tr>";
        }
        $output .= "</tbody>";
        $output .= "</table>";

		return $output;
    }

    public function array_to_n_uplets_table_html($array, int $n = 10) {
        $output = "<table>";
        $output .= "<tbody>";
        $length = count($array);
        $i = 0;
        $output .= "<tr class=\"n_uplets\">";
        while($i<$length) {
            if ($i != 0 && ($i % $n) === 0)
                $output .= "</tr><tr>";
            $output .= "<td>".$array[$i]."</td>";
            $i++;
        }
        $output .= "</tr>";
        $output .= "</tbody>";
        $output .= "</table>";

        return $output;
    }

	public static function timestamp_to_datetime($timestamp, $format = 'd/m/Y H:i:s e') {
        $datetime = new \DateTime();
        $datetime->setTimestamp($timestamp);

        return $datetime->format($format);
    }
}