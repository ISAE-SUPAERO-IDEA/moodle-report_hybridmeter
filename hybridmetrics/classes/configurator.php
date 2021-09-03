<?php

namespace report_hybridmetrics\classes;

defined('MOODLE_INTERNAL') || die();



class configurator {
	protected $path;

	protected $data;

	protected $begin_date;

	protected $end_date;

	public function __construct($path, $data){
		$this->path=$path;
		$configContent == file_get_contents($path);
		if ($configContent == false) {
			$this->begin_date=new \DateTime();
			$this->begin_date->setTimestamp(0);
			$this->end_date=new \DateTime("now");
			$this->generate_all();
		}
		else{
			$array = json_decode($configContent, true);
			$this->begin_date=new \DateTime($array['begin_date']);
			$this->end_date=new \DateTime($array['end_date']);
		}
		$this->data=$data;
	}

	public function set_begin_date($begin_date){
		$this->begin_date=$begin_date;
	}

	public function set_end_date($end_date){
		$this->end_date=$end_date;
	}

	public function get_begin_date(){
		return $this->begin_date;
	}

	public function get_end_date(){
		return $this->end_date;
	}

	private function generate_all(){
		$this->generateJSON();
	}

	protected function generateJSON(){
		$output=array();
		$output["begin_date"]=$this->begin_date->getTimestamp();
		$output["end_date"]=$this->end_date->getTimestamp();
		$fichier = fopen($this->path, 'w');
		fwrite($fichier, json_encode($output));
		fclose($fichier);
	}
}