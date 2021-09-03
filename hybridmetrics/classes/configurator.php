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
		$this->path=__DIR__."/../records/config.json";
		$this->data = file_get_contents($this->path);
		if ($this->data == false) {
			$this->data = [];
			$this->data["begin_date"] = 0;
			$now = new \DateTime("now");
			$this->data["end_date"] = $now->getTimestamp();
			$this->save();
		}
		else{
			$this->data = json_decode($this->data, true);
		}
	}

	public function update($data){
		error_log(print_r($this->data, 1));
		$this->data = array_merge($this->data, $data);
		error_log(print_r($this->data, 1));
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