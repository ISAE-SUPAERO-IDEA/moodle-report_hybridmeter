<?php

namespace report_hybridmeter\classes;

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__)."/../../../config.php");
require_once(__DIR__."/../constants.php");
require_once(__DIR__."/utils.php");
require_once(__DIR__."/data_provider.php");

use \report_hybridmeter\classes\data_provider as data_provider;
use \report_hybridmeter\classes\utils as utils;
use DateTime;

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
        $now = new DateTime("now");
        $before = strtotime("-1 months");
        $this->set_default_value("begin_date", $before);
        $this->set_default_value("end_date", $now->getTimestamp());
        $this->set_default_value("student_archetype", "student");
        $this->set_default_value("debug", 0);
        $this->set_default_value("running", REPORT_HYBRIDMETER_NON_RUNNING);

        $this->set_default_value("has_scheduled_calculation", 0);
        $this->set_default_value("scheduled_date", 0);
        
        $this->set_default_value("active_treshold", REPORT_HYBRIDMETER_ACTIVE_TRESHOLD);
        $this->set_default_value("usage_treshold", REPORT_HYBRIDMETER_USAGE_TRESHOLD);
        $this->set_default_value("digitalisation_treshold", REPORT_HYBRIDMETER_DIGITALISATION_TRESHOLD);

        $this->update_coeffs("usage_coeffs", REPORT_HYBRIDMETER_USAGE_COEFFS);
        $this->update_coeffs("digitalisation_coeffs", REPORT_HYBRIDMETER_DIGITALISATION_COEFFS);

        $this->set_default_value("blacklisted_courses", []);
        $this->set_default_value("blacklisted_categories", []);
    
        // Should save only if changes have been made
        $this->save();
    }
    // Get the singleton configuration instance
    public static function get_instance() {
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

    public function update($data){
        $this->data = array_merge($this->data, $data);
        $this->save();
    }

    public function update_key($key, $data){
        $this->data[$key] = $data;
        $this->save();
    }

    public function unset_key($key){
        unset($this->data[$key]);
        $this->save();
    }

    // Saves the data in the configuration file
    protected function save(){
        $fichier = fopen($this->path, 'w');
        fwrite($fichier, json_encode($this->data));
        fclose($fichier);
    }

    // Get debug status
    public function get_debug(){
        return $this->data['debug'];
    }

    // Update coefficients for a given $type (dynamic or static)
    public function update_coeffs($key, $default_coeffs){
        global $DB;
        $modules_shortname = array_map(
            function($module){
                return $module->name;
            },
            $DB->get_records("modules")
        );
        $this->set_default_value($key, []);
        foreach ($default_coeffs as $item => $value) {
            if (in_array($item, $modules_shortname) && !array_key_exists($item, $this->data[$key])) {
                $this->data[$key][$item] = array();
                $this->data[$key][$item]["value"] = $value;
                $this->data[$key][$item]["name"] = get_string('modulename', $item);
            }
        }
    }

    // Get a coefficient $key for a given $type
    public function get_coeff(string $key, string $item) {
        if (array_key_exists($item, $this->data[$key])) {
            return $this->data[$key][$item]["value"];
        }
        return 0;
    }
    // Get a static coefficient for a $key
    public function get_static_coeff(string $key) {
        return $this->get_coeff("digitalisation_coeffs", $key);
    }
    // Get a dynamic coefficient for a $key
    public function get_dynamic_coeff(string $key) {
        return $this->get_coeff("usage_coeffs", $key);
    }
    
    public function get_coeffs_grid(string $key) {
        $columns = array(
            get_string('module_name', 'report_hybridmeter'),
            get_string('coefficient', 'report_hybridmeter'),
        );
        
        if(!isset($this->data[$key])){
            return json_encode(
                array(
                    "columns" => $columns,
                    "rows" => array(),
                )
            );
        }

        $rows = array();
        $i=0;
        foreach ($this->data[$key] as $coeff){
            $rows[$i][$columns[0]]=$coeff["name"];
            $rows[$i][$columns[1]]=$coeff["value"];
            $i++;
        }

        return array(
            "columns" => $columns,
            "rows" => $rows,
        );
    }

    public function get_treshold_grid(){
        $columns = array(get_string('treshold_name', 'report_hybridmeter'), get_string('treshold_value', 'report_hybridmeter'));
        $rows = array(
            array(
                $columns[0] => get_string('digitalisation_treshold', 'report_hybridmeter'),
                $columns[1] => $this->data["digitalisation_treshold"],
            ),
            array(
                $columns[0] => get_string('digitalisation_treshold', 'report_hybridmeter'),
                $columns[1] => $this->data["usage_treshold"],
            ),
            array(
                $columns[0] => get_string('active_treshold', 'report_hybridmeter'),
                $columns[1] => $this->data["active_treshold"],
            ),
        );

        return array(
            "columns" => $columns,
            "rows" => $rows,
        );
    }

    // Get a dynamic coefficient far a $key

    public function update_usage_treshold($value){
        if(is_numeric($value))
            $this->update_key("usage_treshold", $value);
    }

    public function update_digitalisation_treshold($value){
        if(is_numeric($value))
            $this->update_key("digitalisation_treshold", $value);
    }

    /* Stores the blacklisted courses and categories descending from $id_category when the function is called,
     * to keep them in memory and restore the state at whitelisting time
     *
     * The persistent nature of the state allows you to refresh the page and keep the state for example
     */

    protected function save_blacklist_state_of_category(int $id_category) {
        if(!isset($this->data["save_blacklist_courses"]) || !is_array($this->data["save_blacklist_courses"]))
            $this->data["save_blacklist_courses"] = array();

        if(!isset($this->data["save_blacklist_categories"]) || !is_array($this->data["save_blacklist_categories"]))
            $this->data["save_blacklist_categories"] = array();

        $this->save_blacklist_state_of_category_rec($id_category);

        if(!in_array(strval($id_category), $this->data["save_blacklist_categories"]))
            array_push($this->data["save_blacklist_categories"], strval($id_category));

        $this->save();
    }

    protected function save_blacklist_state_of_category_rec(int $id_category) {
        $data_provider = data_provider::get_instance();
        $ids_subcategories=$data_provider->get_children_categories_ids($id_category);
        $ids_courses=$data_provider->get_children_courses_ids($id_category);

        foreach($ids_courses as $id_course){
            $course_is_not_in_save_blacklist = (!in_array(strval($id_course),$this->data["save_blacklist_courses"]));
            if($this->get_blacklisted_state("courses", $id_course) && $course_is_not_in_save_blacklist)
                array_push($this->data["save_blacklist_courses"], strval($id_course));
        }

        foreach($ids_subcategories as $id_category){
            $this->save_blacklist_state_of_category_rec($id_category);
        }
    }

    protected function delete_blacklist_savelist_of_category(int $id_category) {
        $data_provider = data_provider::get_instance();
        $id_subcategories=$data_provider->get_children_categories_ids($id_category);
        $id_courses=array_map(
            function($id_course){
                return strval($id_course);
            },
            $data_provider->get_children_courses_ids($id_category)
        );

        $this->data["save_blacklist_courses"] = array_diff($this->data["save_blacklist_courses"], $id_courses);

        if(in_array(strval($id_category), $this->data["save_blacklist_categories"])){
            $category_index = array_search(strval($id_category), $this->data["save_blacklist_categories"]);
            unset($this->data["save_blacklist_categories"][$category_index]);
            $this->data["save_blacklist_categories"] = array_values($this->data["save_blacklist_categories"]);
        }

        foreach($id_subcategories as $id_category){
            $this->delete_blacklist_savelist_of_category($id_category);
        }

        $this->save();
    }

    /* This function restores the pre-blacklisting state of the $id_category from
     * backup data, and then deletes the backup data, hence the term "consume".
     *
     * $id_category does not have to be the same $id_category as the one used to store the data,
     * it can be a child for example.
     */

    protected function spend_blacklist_savelist_of_category(int $id_category) {
        $this->spend_blacklist_savelist_of_category_rec($id_category);
        $this->delete_blacklist_savelist_of_category($id_category);
        $this->save();
    }

    protected function spend_blacklist_savelist_of_category_rec(int $id_category) {
        $data_provider = data_provider::get_instance();
        $id_subcategories=$data_provider->get_children_categories_ids($id_category);
        $id_courses=$data_provider->get_children_courses_ids($id_category);

        $array_categories = &$this->data["blacklisted_categories"];
        $array_courses = &$this->data["blacklisted_courses"];

        foreach($id_courses as $id_course) {
            if(!in_array($id_course, $this->data["save_blacklist_courses"])){
                unset($array_courses[$id_course]);
            }
        }

        foreach($id_subcategories as $id_category) {
            if(!in_array($id_category, $this->data["save_blacklist_categories"])){
                unset($array_categories[$id_category]);
            }
            $this->spend_blacklist_savelist_of_category_rec($id_category);
        }
    }

    public function get_blacklisted_state(string $type, int $id): bool {
        $array_key = "blacklisted_".$type;

        if(!isset($this->data[$array_key][$id]))
            return false;

        return $this->data[$array_key][$id];
    }

    // Set a blacklisted $value (true/false) for a course or category ($type) of the given $id
    public function set_blacklisted(string $type, int $id, $value, bool $rec = false) {
        $data_provider = data_provider::get_instance();

        $array_key = "blacklisted_".$type;
        if (!array_key_exists($array_key, $this->data)) {
            $this->data[$array_key] = [];
        }
        $array = &$this->data[$array_key];

        if($value == true) {
            $array[$id] = true;
        }
        else {
            unset($array[$id]);
        }

        if($type=="categories"){
            $id_categories=$data_provider->get_children_categories_ids($id);
            $id_courses=$data_provider->get_children_courses_ids($id);

            if($value == false){
                $this->spend_blacklist_savelist_of_category($id);
            }
            else{
                if(!$rec)
                    $this->save_blacklist_state_of_category($id);

                foreach($id_categories as $id_cat){
                    $this->set_blacklisted("categories", $id_cat, true, 1);
                }
                foreach($id_courses as $id_course){
                    $this->set_blacklisted("courses", $id_course, true, 1);
                }
            }
        }
        
        if(!$rec)
            $this->save();
    }

    public function set_as_running(DateTime $datetime) {
        $this->update_key("running", $datetime->getTimestamp());
    }

    public function unset_as_running() {
        $this->update_key("running", REPORT_HYBRIDMETER_NON_RUNNING);
    }

    public function get_running(): int {
        return $this->data['running'];
    }

    // Get begin date in DateTime format
    public function get_begin_date(): DateTime {
        $output = new DateTime();
        $output->setTimestamp($this->data['begin_date']);
        return $output;
    }

    // Get end date in DateTime format
    public function get_end_date(): DateTime{
        $output = new DateTime();
        $output->setTimestamp($this->data['end_date']);
        return $output;
    }

    public function get_begin_timestamp(): int {
        return $this->get_begin_date()->getTimestamp();
    }

    public function get_end_timestamp(): int {
        return $this->get_end_date()->getTimestamp();
    }

    public function get_student_archetype(): string {
        return $this->data["student_archetype"];
    }

    // Returns raw configuration data
    public function get_data(): array {
        return $this->data;
    }

    public function has_scheduled_calculation(): bool {
        return $this->data["has_scheduled_calculation"];
    }

    public function get_scheduled_date(){
        return $this->data["scheduled_date"];
    }

    public function unschedule_calculation(){
        data_provider::get_instance()->clear_adhoc_tasks();
        $this->update_key("has_scheduled_calculation", 0);
    }

    public function schedule_calculation($timestamp){
        data_provider::get_instance()->clear_adhoc_tasks();
        $this->update([
            "scheduled_date" => $timestamp,
            "has_scheduled_calculation" => 1,
        ]);
        data_provider::get_instance()->schedule_adhoc_task($timestamp);
    }

}
