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
 * HybridMeter configuration.
 *
 * @author Nassim Bennouar, Bruno Ilponse, John Tranier
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2020  ISAE-SUPAERO (https://www.isae-supaero.fr/)
 * @package report_hybridmeter
 */
namespace report_hybridmeter;

use report_hybridmeter\data_provider as data_provider;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__."/../constants.php");

/**
 * HybridMeter configuration.
 */
class config {

    /**
     * Singleton instance.
     * @var config
     */
    private static $instance;

    /**
     * Filepath of the config file.
     * @var string
     */
    private $filepath;

    /**
     * Timestamp of the beginning of the report period.
     * @var int
     */
    public $begin_date;

    /**
     * Timestamp of the end of the report period.
     * @var int
     */
    public $end_date;

    /**
     * Student roles
     * coma separated list of role short names
     * @var array
     */
    public $student_roles = ["student"];

    /**
     * Debug mode.
     * @var bool
     */
    public $debug = false;

    /**
     * Is the report being computed.
     * @var int
     */
    public $running = REPORT_HYBRIDMETER_NON_RUNNING;

    /**
     * Has a report computation been scheduled?
     * @var int
     */
    public $has_scheduled_calculation = 0;

    /**
     * Timestamp of the next scheduled computation.
     * @var int
     */
    public $scheduled_date = 0;

    /**
     * Threshold to consider a course as active.
     * @var int
     */
    public $active_treshold = REPORT_HYBRIDMETER_ACTIVE_TRESHOLD;

    /**
     * Threshold to consider a course as used.
     * @var int
     */
    public $usage_treshold = REPORT_HYBRIDMETER_USAGE_TRESHOLD;

    /**
     * Threshold to consider a course as digitalized.
     * @var int
     */
    public $digitalisation_treshold = REPORT_HYBRIDMETER_DIGITALISATION_TRESHOLD;

    /**
     * Coefficients applied on module for the "usage" perspective.
     * @var array
     */
    public $usage_coeffs = [];

    /**
     * Coefficients applied on module for the "digitalized" perspective.
     * @var array
     */
    public $digitalisation_coeffs = [];


    /**
     * Autoscheduled mode.
     * @var string
     */
    public $autoscheduler = "none";

    /**
     * List of excluded courses.
     * @var array
     */
    public $excluded_courses = [];

    /**
     * List of excluded of categories.
     * @var array
     */
    public $excluded_categories = [];

    /**
     * Create the config loading the config if it exists.
     */
    public function __construct() {
        global $CFG;

        $this->filepath = $CFG->dataroot."/hybridmeter/config.json";

        $this->begin_date = strtotime("-1 months");
        $this->end_date = strtotime("now");

        if (file_exists($this->filepath)) {
            $data = file_get_contents($this->filepath);
            $storedconfig = json_decode($data, true);
            foreach ($storedconfig as $key => $val) {
                $this->$key = $val;
            }
        }

        if (empty($this->usage_coeffs)) {
            $this->usage_coeffs = $this->generate_coeffs_config(
                REPORT_HYBRIDMETER_USAGE_COEFFS
            );
        }

        if (empty($this->digitalisation_coeffs)) {
            $this->digitalisation_coeffs = $this->generate_coeffs_config(
                REPORT_HYBRIDMETER_DIGITALISATION_COEFFS
            );
        }

        $this->save();
    }

    /**
     * Get the singleton instance.
     * @return config
     */
    public static function get_instance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Saves the data in the configuration file.
     */
    protected function save() {
        $file = fopen($this->filepath, 'w');
        fwrite($file, json_encode($this, JSON_FORCE_OBJECT));
        fclose($file);
    }

    /**
     * Update the period of the report.
     * @param $begindate
     * @param $enddate
     * @return void
     */
    public function update_period($begindate, $enddate) {
        $this->begin_date = $begindate;
        $this->end_date = $enddate;
        $this->save();
    }

    /**
     * Update the additional info of the config.
     * @param $studentroles
     * @param $debug
     * @return void
     */
    public function update_additionnal_config($studentroles, $debug) {
        $this->student_roles = $studentroles;
        $this->debug = $debug;
        $this->save();
    }

    /**
     * Getter.
     * @return int
     */
    public function get_begin_date() {
        return $this->begin_date;
    }

    /**
     * Getter.
     * @return int
     */
    public function get_end_date() {
        return $this->end_date;
    }

    /**
     * Getter.
     * @return array
     */
    public function get_student_roles(): array {

        return $this->student_roles;
    }

    /**
     * Getter.
     * @return bool
     */
    public function is_debug(): bool {
        return $this->debug;
    }

    /**
     * Set the debug mode and save the config.
     * @param bool $debug
     * @return void
     */
    public function set_debug(bool $debug) {
        $this->debug = $debug;
        $this->save();
    }

    /**
     * Getter.
     * @return int
     */
    public function is_running(): int {
        return $this->running;
    }

    /**
     * Setter that saves the config.
     * @param int $timestamp
     */
    public function set_running(int $timestamp): void {
        $this->running = $timestamp;
        $this->save();
    }

    /**
     * Getter.
     * @return int
     */
    public function get_has_scheduled_calculation(): int {
        return $this->has_scheduled_calculation;
    }

    /**
     * Getter.
     * @return int
     */
    public function get_scheduled_date(): int {
        return $this->scheduled_date;
    }

    /**
     * Set a scheduled date and save the config.
     * @param int $scheduled_date
     */
    public function set_scheduled_date(int $scheduleddate): void {
        $this->scheduled_date = $scheduleddate;
        $this->has_scheduled_calculation = 1;
        $this->save();
    }

    /**
     * Unschedule the report computation and save the config.
     * @return void
     */
    public function unschedule_calculation(): void {
        $this->has_scheduled_calculation = 0;
        $this->scheduled_date = 0;
        $this->save();
    }

    /**
     * Getter.
     * @return int
     */
    public function get_active_treshold(): int {
        return $this->active_treshold;
    }

    /**
     * Getter.
     * @return int
     */
    public function get_usage_treshold(): int {
        return $this->usage_treshold;
    }

    /**
     * Getter.
     * @return int
     */
    public function get_digitalisation_treshold(): int {
        return $this->digitalisation_treshold;
    }

    /**
     * Get the usage coeff for a module.
     * @param $modulename string
     * @return int
     */
    public function get_usage_coeff($modulename): int {
        if (!array_key_exists($modulename, $this->usage_coeffs)) {
            return 0;
        }
        return $this->usage_coeffs[$modulename]["value"];
    }

    /**
     * Get the digitalization coeff for a module.
     * @param $modulename string
     * @return int
     */
    public function get_digitalisation_coeffs($modulename): int {
        if (!array_key_exists($modulename, $this->digitalisation_coeffs)) {
            return 0;
        }
        return $this->digitalisation_coeffs[$modulename]["value"];
    }

    /**
     * Get the coeff for "usage" or "digitalization" for a module.
     * @param $type
     * @param $modulename
     * @return int
     */
    public function get_coeff($type, $modulename): int {
        switch($type) {
            case "digitalisation_coeffs":
                return $this->get_digitalisation_coeffs($modulename);

            case "usage_coeffs":
                return $this->get_usage_coeff($modulename);

            default:
                return 0;

        }
    }

    /**
     * Gets the coeffs associated to module names either for "usage" or for "digitalization"
     * @param $type
     * @return array
     * @throws \Exception
     */
    public function get_coeffs($type): array {
        switch($type) {
            case "digitalisation_coeffs":
                return $this->digitalisation_coeffs;

            case "usage_coeffs":
                return $this->usage_coeffs;

            default:
                throw new \Exception("Invalid coeff type");

        }
    }

    /**
     * Get the modules associated with coeffs.
     * @return string[]
     */
    public function get_modules() {
        return array_keys($this->usage_coeffs) + array_keys($this->digitalisation_coeffs);
    }

    /**
     * Getter.
     * @return string
     */
    public function get_autoscheduler(): string {
        return $this->autoscheduler;
    }

    /**
     * Getter.
     * @return array
     */
    public function get_excluded_courses(): array {
        return $this->excluded_courses;
    }

    /**
     * Update the usage coefficients and the digitalization coefficients.
     * @param $usagecoeffs
     * @param $digitalizationcoeffs
     * @return void
     */
    public function update_coeffs($usagecoeffs, $digitalizationcoeffs) {
        $this->usage_coeffs = $this->generate_coeffs_config($usagecoeffs);
        $this->digitalisation_coeffs = $this->generate_coeffs_config($digitalizationcoeffs);
        $this->save();
    }

    /**
     * Transform a description of coefficients in the form of [<module_name> => <coeff>]* to the form
     * required by the config object, which [<module_name> => ["value" => <coeff>, "name" => "modulename"]]*
     * @param $coeffs
     * @return array
     */
    private function generate_coeffs_config($coeffs): array {
        $coeffsconfig = [];

        foreach ($coeffs as $modulename => $coeff) {
            $coeffsconfig[$modulename] = [
                "value" => $coeff,
                "name" => $modulename,
            ];
        }

        return $coeffsconfig;
    }

    /**
     * Add or remove a course from the excluded list.
     * @param int $courseid
     * @param bool $value
     * @param bool $save
     * @return void
     */
    public function set_excluded_course(int $courseid, bool $value, bool $save = false): void {
        $this->excluded_courses[$courseid] = $value;
        if ($save) {
            $this->save();
        }
    }

    /**
     * Add or remove a single category from the excluded list.
     * @param int $categoryid
     * @param bool $value
     * @return void
     */
    public function set_excluded_category(int $categoryid, bool $value): void {
        $this->excluded_categories[$categoryid] = $value;
    }

    /**
     * Add or remove a category and all its subtree to the excluded list
     * @param int $categoryid
     * @param bool $value
     * @param bool $root
     * @return void
     */
    public function set_excluded_category_subtree(int $categoryid, bool $value, bool $root = true): void {
        $dataprovider = data_provider::get_instance();
        $this->excluded_categories[$categoryid] = $value;

        foreach ($dataprovider->get_children_courses_ids($categoryid) as $courseid) {
            $this->set_excluded_course($courseid, $value);
        }

        foreach ($dataprovider->get_children_categories_ids($categoryid) as $categoryid) {
            $this->set_excluded_category_subtree($categoryid, $value, false);
        }

        if ($root) {
            $this->save();
        }
    }

    /**
     * Update the excluded list with the actual tree of courses to integrate new courses and categories.
     * @return void
     */
    public function update_excluded_data() {
        logger::log("Update exclusions");
        $dataprovider = data_provider::get_instance();
        $coursestree = $dataprovider->get_courses_tree();
        logger::log($coursestree);

        $this->update_excluded_data_rec($coursestree);
    }

    /**
     * Update the excluded list with the provided tree of courses to integrate new courses and categories.
     * @param $tree
     * @return void
     */
    public function update_excluded_data_rec($tree) {
        if ($tree['data']) {
            logger::log("Update excluded list for course_id=".$tree['data']->id);
            if (!array_key_exists($tree['data']->id, $this->excluded_categories)) {
                $parentid = $tree['data']->parent;
                if ($parentid == 0) {
                    $value = false;
                } else {
                    $value = $this->excluded_categories[$parentid];
                }
                $this->set_excluded_category($tree['data']->id, $value);
            }

            if (in_array('children_courses', $tree)) {
                foreach ($tree['children_courses'] as $course) {
                    $id = $course->id;

                    if (!array_key_exists($id, $this->excluded_courses)) {
                        $categoryid = $course->category;
                        $this->set_excluded_course($id, $this->excluded_categories[$categoryid]);
                    }
                }
            }

            if (in_array('children_categories', $tree)) {
                foreach ($tree['children_categories'] as $category) {
                    $this->update_excluded_data_rec($category);
                }
            }
        }

        $this->save();
    }
}
