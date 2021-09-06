<?php

namespace report_hybridmetrics\classes;

defined('MOODLE_INTERNAL') || die();


// TODO: Gérer la configuration en base de données en utilisant les settings moodle (P2)
class configurator {
	protected $path;

	protected $data;

	protected $begin_date;

	protected $end_date;

	public function __construct(){
		global $CFG;
		$this->path=$CFG->dataroot."/hybridmetrics/hybridmetrics.json";
		$this->data = file_get_contents($this->path);
		if ($this->data == false) {
			$this->data = [];
			$this->data["begin_date"] = 0;
			$now = new \DateTime("now");
			$this->data["end_date"] = $now->getTimestamp();
			$this->data["blacklisted_courses"] = [];
			$this->data["blacklisted_categories"] = [];
			$this->save();
		}
		else{
			$this->data = json_decode($this->data, true);
		}
	}

	public function update($data){
		$this->data = array_merge($this->data, $data);
		$this->save();
	}
	public function set_blacklisted($type, $id, $value) {
		$array_key = "blacklisted_".$type;
		if (!array_key_exists($array_key, $this->data)) {
			$this->data[$array_key] = [];
		}
		$array = &$this->data[$array_key];
		if ($value == true) {
			$array[$id] = true;
		}
		else {
			unset($array[$id]);
		}
		$this->save();

	}


	public function get_begin_date() {
		return new \DateTime($this->data['begin_date']);
	}

	public function get_end_date(){
		return new \DateTime($this->data['end_date']);
	}
	public function get_data() {
		return $this->data;
	}

	protected function save(){
		$output=array();
		$fichier = fopen($this->path, 'w');
		fwrite($fichier, json_encode($this->data));
		fclose($fichier);
	}
}