<?php

namespace report_hybridmetrics\classes;
require_once(__DIR__."/../constants.php");

defined('MOODLE_INTERNAL') || die();


// TODO: Gérer la configuration en base de données en utilisant les settings moodle (P2)
class configurator {
	protected $path;

	protected $data;

	protected $begin_date;

	protected $end_date;

	protected static $instance = null;

	public function __construct(){
		global $CFG;
		$this->path=$CFG->dataroot."/hybridmetrics/config.json";
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
		$this->set_default_value("begin_date", 0);
		$this->set_default_value("end_date", $now->getTimestamp());
		$this->set_default_value("blacklisted_courses", []);
		$this->set_default_value("blacklisted_categories", []);

		$this->update_coeffs("dynamic_coeffs", COEFF_DYNAMIQUES);
		$this->update_coeffs("static_coeffs", COEFF_STATIQUES);
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
		return 1;
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
	// Set a blacklisted $value (true/false) for a course or category ($type) of the given $id
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
	// Returns raw configuration data
	public function get_data() {
		return $this->data;
	}

	// Saves the data in the configuration file
	protected function save(){
		$fichier = fopen($this->path, 'w');
		fwrite($fichier, json_encode($this->data));
		fclose($fichier);
	}
}

