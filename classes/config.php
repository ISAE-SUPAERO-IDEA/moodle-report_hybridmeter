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

require_once(__DIR__."/../constants.php");

/**
 * HybridMeter configuration.
 */
class config {

    private $filepath;

    public $begin_date;
    public $end_date;
    public $student_archetype = "student";
    public $debug = false;
    public $running = REPORT_HYBRIDMETER_NON_RUNNING;

    public $has_scheduled_calculation = 0;
    public $scheduled_date = 0;

    public $active_treshold = REPORT_HYBRIDMETER_ACTIVE_TRESHOLD;
    public $usage_treshold = REPORT_HYBRIDMETER_USAGE_TRESHOLD;
    public $digitalisation_treshold = REPORT_HYBRIDMETER_DIGITALISATION_TRESHOLD;
    public $usage_coeffs = [];
    public $digitalisation_coeffs = [];
    public $autoscheduler = "none";

    public $blacklisted_courses = [];
    public $blacklisted_categories = [];
    public $save_blacklist_courses = [];
    public $save_blacklist_categories = [];

    public function __construct($filepath) {
        $this->filepath = $filepath;

        $this->begin_date = strtotime("-1 months");
        $this->end_date = strtotime("now");

        if(file_exists($this->filepath)) {
            $data = file_get_contents($this->filepath);
            $storedconfig = json_decode($data, true);
            foreach($storedconfig as $key => $val) {
                $this->$key = $val;
            }
        }

        if(empty($this->usage_coeffs)) {
            $this->usage_coeffs = $this->generate_coeffs_config(
                REPORT_HYBRIDMETER_USAGE_COEFFS
            );
        }

        if(empty($this->digitalisation_coeffs)) {
            $this->digitalisation_coeffs = $this->generate_coeffs_config(
                REPORT_HYBRIDMETER_DIGITALISATION_COEFFS
            );
        }

        $this->save();
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
     * @param $studentarchetype
     * @param $debug
     * @return void
     */
    public function update_additionnal_config($studentarchetype, $debug) {
        $this->student_archetype = $studentarchetype;
        $this->debug = $debug;
        $this->save();
    }

    /**
     * @return int
     */
    public function get_begin_date() {
        return $this->begin_date;
    }

    /**
     * @return int
     */
    public function get_end_date() {
        return $this->end_date;
    }

    /**
     * @return string
     */
    public function get_student_archetype(): string {
        return $this->student_archetype;
    }

    /**
     * @return bool
     */
    public function is_debug(): bool {
        return $this->debug;
    }

    /**
     * @return int
     */
    public function is_running(): int {
        return $this->running;
    }

    /**
     * @param int $running
     */
    public function set_running(int $running): void {
        $this->running = $running;
        $this->save();
    }

    /**
     * @return int
     */
    public function get_has_scheduled_calculation(): int {
        return $this->has_scheduled_calculation;
    }

    /**
     * @return int
     */
    public function get_scheduled_date(): int {
        return $this->scheduled_date;
    }

    /**
     * @param int $scheduled_date
     */
    public function set_scheduled_date(int $scheduled_date): void {
        $this->scheduled_date = $scheduled_date;
        $this->has_scheduled_calculation = 1;
        $this->save();
    }

    public function unschedule_calculation(): void {
        $this->has_scheduled_calculation = 0;
        $this->scheduled_date = 0;
        $this->save();
    }

    /**
     * @return int
     */
    public function get_active_treshold(): int {
        return $this->active_treshold;
    }

    /**
     * @return int
     */
    public function get_usage_treshold(): int {
        return $this->usage_treshold;
    }

    /**
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
        if(!array_key_exists($modulename, $this->usage_coeffs)) {
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
        if(!array_key_exists($modulename, $this->digitalisation_coeffs)) {
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
     * @return string
     */
    public function get_autoscheduler(): string
    {
        return $this->autoscheduler;
    }

    /**
     * @return array
     */
    public function get_blacklisted_courses(): array
    {
        return $this->blacklisted_courses;
    }

    /**
     * @return array
     */
    public function get_blacklisted_categories(): array
    {
        return $this->blacklisted_categories;
    }

    /**
     * @return array
     */
    public function get_save_blacklist_courses(): array
    {
        return $this->save_blacklist_courses;
    }

    /**
     * @return array
     */
    public function get_save_blacklist_categories(): array
    {
        return $this->save_blacklist_categories;
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

        foreach($coeffs as $modulename => $coeff) {
            $coeffsconfig[$modulename] = [
                "value" => $coeff,
                "name" => $modulename,
            ];
        }

        return $coeffsconfig;
    }

    public function set_blacklisted_course(int $courseid, bool $value, bool $save = false): void {
        $this->blacklisted_courses[$courseid] = $value;
        if($save) {
            $this->save();
        }
    }

    public function set_blacklisted_category(int $categoryid, bool $value): void {
        $this->blacklisted_categories[$categoryid] = $value;
    }

    public function set_blacklisted_category_subtree(int $categoryid, bool $value, bool $root = true): void {
        $dataprovider = data_provider::get_instance();
        $this->blacklisted_categories[$categoryid] = $value;

        foreach ($dataprovider->get_children_courses_ids($categoryid) as $courseid) {
            $this->set_blacklisted_course($courseid, $value);
        }

        foreach ($dataprovider->get_children_categories_ids($categoryid) as $categoryid) {
            $this->set_blacklisted_category_subtree($categoryid, $value, false);
        }

        if ($root) {
            $this->save();
        }
    }

    public function update_blacklisted_data() {
        logger::log("Update blacklist");
        $dataprovider = data_provider::get_instance();
        $coursestree = $dataprovider->get_courses_tree();
        logger::log($coursestree);

        $this->update_blacklisted_data_rec($coursestree);
    }

    public function update_blacklisted_data_rec($tree) {
        if ($tree['data']) {
            logger::log("Update blacklist for course_id=".$tree['data']->id);
            if (!array_key_exists($tree['data']->id, $this->blacklisted_categories)) {
                $parentid = $tree['data']->parent;
                if ($parentid == 0) {
                    $value = false;
                } else {
                    $value = $this->blacklisted_categories[$parentid];
                }
                $this->set_blacklisted_category($tree['data']->id, $value);
            }

            if (in_array('children_courses', $tree)) {
                foreach ($tree['children_courses'] as $course) {
                    $id = $course->id;

                    if (!array_key_exists($id, $this->blacklisted_courses)) {
                        $categoryid = $course->category;
                        $this->set_blacklisted_course($id, $this->blacklisted_categories[$categoryid]);
                    }
                }
            }

            if (in_array('children_categories', $tree)) {
                foreach ($tree['children_categories'] as $category) {
                    $this->update_blacklists($category);
                }
            }
        }

        $this->save();
    }
}