<?php
// This file is part of Moodle - http://moodle.org
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @author Nassim Bennouar, Bruno Ilponse
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2020  ISAE-SUPAERO (https://www.isae-supaero.fr/)
 * @package
 */
namespace report_hybridmeter\classes;

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__)."/../../../config.php");
require_once(__DIR__."/../constants.php");
require_once(__DIR__."/utils.php");
require_once(__DIR__."/data_provider.php");

use report_hybridmeter\classes\data_provider as data_provider;
use report_hybridmeter\classes\utils as utils;
use DateTime;

// Manage hybridmeter's configuration file
class configurator {
    protected $path;

    protected $data;

    protected $begindate;

    protected $enddate;

    protected static $instance = null;

    public function __construct() {
        global $CFG;

        $this->path = $CFG->dataroot."/hybridmeter/config.json";
        // Initialize empty data if no configuration file exists
        if (!file_exists($this->path)) {
            $this->data = [];
        }
        // Read data from configuration file if it exists
        else{
            $data = file_get_contents($this->path);
            $this->data = json_decode($data, true);
        }
        // Sanitize data
        $now = new DateTime("now");
        $before = strtotime("-1 months");
        $this->set_default_value("begin_date", $before);
        $this->set_default_value("end_date", $now->getTimestamp());
        $this->set_default_value("student_archetype", "student");
        $this->set_default_value("debug", false);
        $this->set_default_value("running", REPORT_HYBRIDMETER_NON_RUNNING);

        $this->set_default_value("has_scheduled_calculation", 0);
        $this->set_default_value("scheduled_date", 0);

        $this->set_default_value("active_treshold", REPORT_HYBRIDMETER_ACTIVE_TRESHOLD);
        $this->set_default_value("usage_treshold", REPORT_HYBRIDMETER_USAGE_TRESHOLD);
        $this->set_default_value("digitalisation_treshold", REPORT_HYBRIDMETER_DIGITALISATION_TRESHOLD);

        $this->update_coeffs("usage_coeffs", REPORT_HYBRIDMETER_USAGE_COEFFS);
        $this->update_coeffs("digitalisation_coeffs", REPORT_HYBRIDMETER_DIGITALISATION_COEFFS);

        $this->set_default_value("autoscheduler", "none");

        $this->set_default_value("blacklisted_courses", []);
        $this->set_default_value("blacklisted_categories", []);
        $this->set_default_value("save_blacklist_courses", []);
        $this->set_default_value("save_blacklist_categories", []);

        /*
        $blacklist_loaded = (!array_key_exists("blacklisted_courses", $this->data)
            || !array_key_exists("blacklisted_categories", $this->data));

        if(!$blacklist_loaded)
            $this->update_blacklisted_data();*/

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
    public function set_default_value($key, $value) {
        if (!array_key_exists($key, $this->data)) {
            $this->data[$key] = $value;
        }
    }

    public function update($data) {
        $this->data = array_merge($this->data, $data);
        $this->save();
    }

    public function update_key($key, $data) {
        $this->data[$key] = $data;
        $this->save();
    }

    public function unset_key($key) {
        unset($this->data[$key]);
        $this->save();
    }

    // Saves the data in the configuration file
    protected function save() {
        $fichier = fopen($this->path, 'w');
        fwrite($fichier, json_encode($this->data, JSON_FORCE_OBJECT));
        fclose($fichier);
    }

    // Get debug status
    public function get_debug() {
        return $this->data['debug'];
    }

    public function set_debug() {
        $this->update_key('debug', true);
    }

    public function unset_debug() {
        $this->update_key('debug', false);
    }
    // Get autoscheduler status
    public function get_autoscheduler() {
        return $this->data['autoscheduler'];
    }

    // Update coefficients for a given $type (dynamic or static)
    public function update_coeffs($key, $defaultcoeffs) {
        global $DB;
        $modulesshortname = array_map(
            function($module){
                return $module->name;
            },
            $DB->get_records("modules")
        );
        $this->set_default_value($key, []);
        foreach ($defaultcoeffs as $item => $value) {
            if (in_array($item, $modulesshortname) && !array_key_exists($item, $this->data[$key])) {
                $this->data[$key][$item] = [];
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

    public function get_all_coeffs_rows(): array {
        $keys = array_keys($this->data["usage_coeffs"]);
        $rows = [];

        $i = 0;

        foreach ($keys as $key) {
            $i++;
            $row = [
                "name" => $key,
                "usage_coeff" => $this->data["usage_coeffs"][$key]["value"],
                "digitalisation_coeff" => $this->data["digitalisation_coeffs"][$key]["value"],
            ];
            array_push($rows, $row);
        }

        $output = [
            "rows" => $rows,
            "count" => count($rows),
        ];

        return $output;
    }

    public function get_tresholds_rows(): array {
        $columns = ["name", "value"];
        $rows = [
            [
                $columns[0] => get_string('digitalisation_treshold', 'report_hybridmeter'),
                $columns[1] => $this->data["digitalisation_treshold"],
            ],
            [
                $columns[0] => get_string('usage_treshold', 'report_hybridmeter'),
                $columns[1] => $this->data["usage_treshold"],
            ],
            [
                $columns[0] => get_string('active_treshold', 'report_hybridmeter'),
                $columns[1] => $this->data["active_treshold"],
            ],
        ];

        return ["rows" => $rows, "count" => count($rows)];
    }

    public function get_coeffs_grid(string $key) {
        $columns = [
            get_string('module_name', 'report_hybridmeter'),
            get_string('coefficient', 'report_hybridmeter'),
        ];

        if(!isset($this->data[$key])){
            return json_encode(
                [
                    "columns" => $columns,
                    "rows" => [],
                ]
            );
        }

        $rows = [];
        $i = 0;
        foreach ($this->data[$key] as $coeff){
            $rows[$i][$columns[0]] = $coeff["name"];
            $rows[$i][$columns[1]] = $coeff["value"];
            $i++;
        }

        return [
            "columns" => $columns,
            "rows" => $rows,
        ];
    }

    public function get_treshold_grid() {
        $columns = [get_string('treshold_name', 'report_hybridmeter'), get_string('treshold_value', 'report_hybridmeter')];
        $rows = [
            [
                $columns[0] => get_string('digitalisation_treshold', 'report_hybridmeter'),
                $columns[1] => $this->data["digitalisation_treshold"],
            ],
            [
                $columns[0] => get_string('usage_treshold', 'report_hybridmeter'),
                $columns[1] => $this->data["usage_treshold"],
            ],
            [
                $columns[0] => get_string('active_treshold', 'report_hybridmeter'),
                $columns[1] => $this->data["active_treshold"],
            ],
        ];

        return [
            "columns" => $columns,
            "rows" => $rows,
        ];
    }

    // Get a dynamic coefficient far a $key

    public function update_usage_treshold($value) {
        if(is_numeric($value)) {
            $this->update_key("usage_treshold", $value);
        }
    }

    public function update_digitalisation_treshold($value) {
        if(is_numeric($value)) {
            $this->update_key("digitalisation_treshold", $value);
        }
    }

    public function is_blacklisted_element(string $type, int $id): bool {
        $arraykey = "blacklisted_".$type;
        $array = &$this->data[$arraykey];

        return (isset($array[$id]) && $array[$id]);
    }

    public function update_blacklisted_data() {
        logger::log("Update blacklist");
        $dataprovider = data_provider::get_instance();
        $coursestree = $dataprovider->get_courses_tree();
        logger::log($coursestree);

        $this->update_blacklisted_data_rec($coursestree);
    }

    private function update_blacklisted_data_rec($tree) {
        $blacklistedcourses = &$this->data["blacklisted_courses"];
        $blacklistedcategories = &$this->data["blacklisted_categories"];
        if ($tree['data']) {
            logger::log("Update blacklist for course_id=".$tree['data']->id);
            if(!array_key_exists($tree['data']->id, $blacklistedcategories)) {
                error_log(print_r("aa", 1));
                $parentid = $tree['data']->parent;
                if($parentid == 0){
                    $value = false;
                }
                else {
                    $value = $blacklistedcategories[$parentid];
                }
                $this->atomic_set_blacklisted("categories", $tree['data']->id, $value);
            }
        }
        if (in_array('children_courses', $tree)) {
            foreach($tree['children_courses'] as &$course) {
                $id = $course->id;

                if(!array_key_exists($id, $blacklistedcourses)){
                    $categoryid = $course->category;
                    $this->atomic_set_blacklisted("courses", $id, $blacklistedcategories[$categoryid]);
                }
            }
        }
        if (in_array('children_categories', $tree)) {
            foreach($tree['children_categories'] as &$category) {
                $this->update_blacklisted_data_rec($category);
            }
        }
    }

    private function is_saved_element(string $type, $id) {
        $arraykey = "save_blacklist_" . $type;
        $array = &$this->data[$arraykey];

        return in_array(strval($id), $array);
    }

    private function save_blacklisted_element(string $type, $id) {
        $arraykey = "save_blacklist_" . $type;
        $array = &$this->data[$arraykey];

        if(!in_array(strval($id), $array)) {
            array_push($array, strval($id));
        }
    }

    private function remove_blacklisted_element_from_save(string $type, $id) {
        $arraykey = "save_blacklist_" . $type;
        $array = &$this->data[$arraykey];

        if(in_array(strval($id), $array)){
            $categoryindex = array_search(strval($id), $array);
            unset($array[$categoryindex]);
            $array = array_values($array);
        }
    }

    public function atomic_set_blacklisted(string $type, $id, bool $value) {
        $arraykey = "blacklisted_".$type;
        if (!array_key_exists($arraykey, $this->data)) {
            $this->data[$arraykey] = [];
        }
        $array = &$this->data[$arraykey];

        $array[$id] = $value;
    }

    // Set a blacklisted $value (true/false) for a course or category ($type) of the given $id
    public function set_blacklisted(string $type, int $id, bool $value, bool $rec = false) {
        $dataprovider = data_provider::get_instance();

        $this->atomic_set_blacklisted($type, $id, $value);

        if($type == "categories") {
            $blacklistedcategories = &$this->data["blacklisted_categories"];

            $idcategories = $dataprovider->get_children_categories_ids($id);
            $idcourses = $dataprovider->get_children_courses_ids($id);

            if($value) {
                foreach ($idcourses as &$course) {
                    if($this->is_blacklisted_element("courses", $course)) {
                        $this->save_blacklisted_element("courses", $course);
                    }
                    $this->atomic_set_blacklisted("courses", $course, true);
                }

                foreach ($idcategories as &$category) {
                    $isblacklisted = (isset($blacklistedcategories[$category]) && $blacklistedcategories[$category]);

                    if($isblacklisted) {
                        $this->save_blacklisted_element("categories", $category);
                    } else {
                        $this->set_blacklisted("categories", $category, true, true);
                    }
                }
            }
            else {
                foreach ($idcourses as &$course) {
                    if(!$this->is_saved_element("courses", $course)){
                        $this->atomic_set_blacklisted("courses", $course, false);
                    }
                    $this->remove_blacklisted_element_from_save("courses", $course);
                }

                foreach ($idcategories as &$category) {
                    if(!$this->is_saved_element("categories", $category)) {
                        $this->set_blacklisted("categories", $category, false, true);
                    }
                    else {
                        $this->remove_blacklisted_element_from_save("categories", $category);
                    }
                }
            }
        }

        if(!$rec) {
            $this->save();
        }
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
    public function get_end_date(): DateTime {
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

    public function get_scheduled_date() {
        return $this->data["scheduled_date"];
    }

    public function unschedule_calculation() {
        data_provider::get_instance()->clear_adhoc_tasks();
        $this->update_key("has_scheduled_calculation", 0);
    }

    public function schedule_calculation($timestamp) {
        data_provider::get_instance()->clear_adhoc_tasks();
        $this->update([
            "scheduled_date" => $timestamp,
            "has_scheduled_calculation" => 1,
        ]);
        data_provider::get_instance()->schedule_adhoc_task($timestamp);
    }

}
