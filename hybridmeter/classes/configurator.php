<?php

namespace report_hybridmeter\classes;

require_once(dirname(__FILE__)."/../../../config.php");
require_once(__DIR__."/../constants.php");
require_once(__DIR__."/utils.php");
require_once(__DIR__."/data_provider.php");

defined('MOODLE_INTERNAL') || die();

use \report_hybridmeter\classes\data_provider as data_provider;
use \report_hybridmeter\classes\utils as utils;

// Manage hybridmeter's configuration file
class configurator {
	protected $path;

	protected $data;

	protected $begin_date;

	protected $end_date;

	protected static $instance = null;

	public function __construct(){
		global $CFG;

		$this->path=$CFG->dataroot."/hybridmeter/config.json";
		// Initialize empty data if no configuration file exists 
		if (!file_exists($this->path)) {
			$this->data = [];
		}
		// Read data from configuration file if it exists
		else{
			$this->data = file_get_contents($this->path);
			$this->data = json_decode($this->data, true);
		}
		// Sanitize data
		$now = new \DateTime("now");
		$before = strtotime("-1 months");
		$this->set_default_value("begin_date", $before);
		$this->set_default_value("end_date", $now->getTimestamp());
		$this->set_default_value("debug", 0);
		$this->set_default_value("blacklisted_courses", []);
		$this->set_default_value("blacklisted_categories", []);
		$this->set_default_value("running", NON_RUNNING);

		$this->set_default_value("has_scheduled_calculation", 1);
		$tomorrow_midnight = utils::tomorrow_midnight();
		$this->set_default_value("scheduled_date", $tomorrow_midnight);
		/*
		$this->set_default_value("seuil_actif", SEUIL_ACTIF);
		$this->set_default_value("seuil_dynamique", SEUIL_DYNAMIQUE);
		$this->set_default_value("seuil_statique", SEUIL_STATIQUE);
		$this->update_coeffs("dynamic_coeffs", COEFF_DYNAMIQUES);
		$this->update_coeffs("static_coeffs", COEFF_STATIQUES);
		*/
		$this->data["seuil_actif"] = SEUIL_ACTIF;
		$this->data["seuil_dynamique"] = SEUIL_DYNAMIQUE;
		$this->data["seuil_statique"] = SEUIL_STATIQUE;
		$this->data["dynamic_coeffs"] = COEFF_DYNAMIQUES;
		$this->data["static_coeffs"] = COEFF_STATIQUES;
		// Should save only if changes have been made
		$this->save();
	}
	// Get the singleton configuration instance
	public static function getInstance() {
		if (self::$instance == null) {
			self::$instance = new configurator();
		}
        return self::$instance;
    }
    // Sets a default value for a configuration key
	public function set_default_value($key, $value){
		if (!array_key_exists($key, $this->data)) {
			$this->data[$key] = $value;
		}
	}

	public function has_scheduled_calculation(){
		return $this->data["has_scheduled_calculation"];
	}

	public function get_scheduled_date(){
		return $this->data["scheduled_date"];
	}

	public function unschedule_calculation(){
		data_provider::getInstance()->clear_adhoc_tasks();
		$this->update_key("has_scheduled_calculation", 0);
	}

	public function schedule_calculation($timestamp){
		data_provider::getInstance()->clear_adhoc_tasks();
		$this->update([
			"scheduled_date" => $timestamp,
			"has_scheduled_calculation" => 1,
		]);
		data_provider::getInstance()->schedule_adhoc_task($timestamp);
	}

	// Update coefficients for a given $type (dynamic or static)
	public function update_coeffs($key, $default_coeffs){
		global $DB;
		$modules = $DB->get_records("modules");
		$this->set_default_value($key, []);
		foreach ($default_coeffs as $item => $value) {
			if (!array_key_exists($item, $this->data[$key])) {
				$this->data[$key][$item] = $value;
			}
		}
	}

	// Get a coefficient $key for a given $type
	public function get_coeff($key, $item) {
		if (array_key_exists($item, $this->data[$key])) {
			return $this->data[$key][$item];
		}
		return 0;
	}
	// Get a static coefficient for a $key
	public function get_static_coeff($key) {
		return $this->get_coeff("static_coeffs", $key);
	}
	// Get a dynamic coefficient for a $key
	public function get_dynamic_coeff($key) {
		return $this->get_coeff("dynamic_coeffs", $key);
	}
	// Get a dynamic coefficient far a $key
	public function update($data){
		$this->data = array_merge($this->data, $data);
		$this->save();
	}

	public function update_key($key, $data){
		$this->data[$key] = $data;
		$this->save();
	}

	public function update_seuil_dynamique($value){
		if(is_numeric($value))
			$this->update_key("seuil_dynamique", $value);
	}

	public function update_seuil_statique($value){
		if(is_numeric($value))
			$this->update_key("seuil_statique", $value);
	}

	// Set a blacklisted $value (true/false) for a course or category ($type) of the given $id
	public function set_blacklisted($type, $id, $value) {
		$data_provider = data_provider::getInstance();

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

		if($type=="categories"){
			$id_categories=$data_provider->get_subcategories_id($id);

			foreach($id_categories as $id_cat){
				$this->set_blacklisted($type, $id_cat, $value);
			}
		}

		$this->save();
	}

	public function set_as_running($timestamp) {
		$this->update_key("running", $timestamp);
	}

	public function unset_as_running() {
		$this->update_key("running", NON_RUNNING);
	}

	public function get_running(){
		return $this->data['running'];
	}

	// Get begin date in DateTime format
	public function get_begin_date() {
		$output = new \DateTime();
		$output->setTimestamp($this->data['begin_date']);
		return $output;
	}

	// Get end date in DateTime format
	public function get_end_date(){
		$output = new \DateTime();
		$output->setTimestamp($this->data['end_date']);
		return $output;
	}
	// Get debug status
	public function get_debug(){
		return $this->data['debug'];
	}

	public function get_begin_timestamp(){
		return $this->get_begin_date()->getTimestamp();
	}

	public function get_end_timestamp(){
		return $this->get_end_date()->getTimestamp();
	}

	// Returns raw configuration data
	public function get_data() {
		return $this->data;
	}

	public function get_coeffs_grid($key) {
		$columns = array("Nom du module", "Coefficient");
		if(!isset($this->data[$key])){
			return json_encode(
				array(
					"columns" => $columns,
					"rows" => array()
				)
			);
		}

		$rows = array();
		$i=0;
		foreach ($this->data[$key] as $key => $value){
			$rows[$i][$columns[0]]=$key;
			$rows[$i][$columns[1]]=$value;
			$i++;
		}

		return array(
			"columns" => $columns,
			"rows" => $rows
		);
	}

	public function get_seuils_grid(){
		$columns = array("Nom du seuil", "Valeur du seuil");
		$rows = array(
			array(
				$columns[0] => "Seuil d'hybridation selon le niveau de digitalisation",
				$columns[1] => $this->data["seuil_statique"]
			),
			array(
				$columns[0] => "Seuil d'hybridation selon le niveau d'utilisation",
				$columns[1] => $this->data["seuil_dynamique"]
			),
			array(
				$columns[0] => "Nombre d'étudiants actifs minimum pour catégoriser un cours comme actif",
				$columns[1] => $this->data["seuil_actif"]
			)
		);

		return array(
			"columns" => $columns,
			"rows" => $rows
		);
	}

	// Saves the data in the configuration file
	protected function save(){
		$fichier = fopen($this->path, 'w');
		fwrite($fichier, json_encode($this->data));
		fclose($fichier);
	}
}

