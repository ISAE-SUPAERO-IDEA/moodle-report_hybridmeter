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
 * Provide data that will serve as a basis to compute indicators.
 *
 * @author Nassim Bennouar, Bruno Ilponse
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2020  ISAE-SUPAERO (https://www.isae-supaero.fr/)
 * @package report_hybridmeter
 */
namespace report_hybridmeter;

use report_hybridmeter\utils as utils;
use report_hybridmeter\configurator as configurator;
use report_hybridmeter\task\processing as processing;
use Exception;

/**
 * Provide data that will serve as a basis to compute indicators.
 */
class data_provider {

    /**
     * Singleton instance.
     * @var data_provider
     */
    protected static $instance = null;

    /**
     * Get the singleton instance.
     * @return data_provider
     */
    public static function get_instance() {
        if (self::$instance == null) {
            self::$instance = new data_provider();
        }

        return self::$instance;
    }

    /**
     * Count the number of activities per type in a course.
     * @param int $idcourse
     * @return array
     */
    public function count_activities_per_type_of_course(int $idcourse): array {
        global $DB;
        $output = [];

        $sql = "SELECT modules.name, count(modules.name) AS count
                  FROM {course_modules} course_modules
                  JOIN {modules} modules ON course_modules.module = modules.id
                 WHERE course_modules.course = ?
              GROUP BY modules.name";

        $records = $DB->get_records_sql($sql, [$idcourse]);

        foreach ($records as $key => $object) {
            $output[$object->name] = $object->count;
        }

        $sql = "SELECT modules.name AS name, 0 AS count
                  FROM {modules} modules
                 WHERE name NOT IN (
                    SELECT modules.name AS name
                      FROM {course_modules} course_modules
                      JOIN {modules} modules ON course_modules.module = modules.id
                     WHERE course_modules.course = ?
                  GROUP BY modules.name
                )";

        $records = $DB->get_records_sql($sql, [$idcourse]);

        foreach ($records as $key => $object) {
            $output[$object->name] = $object->count;
        }

        return $output;
    }

    /**
     * Counts the number of unique users according to the course and the period chosen.
     */
    public function count_student_single_visitors_on_courses(array $idscourses, int $begintimestamp, int $endtimestamp): int {
        global $DB;

        utils::precondition_ids($idscourses);

        $studentarchetype = configurator::get_instance()->get_student_archetype();

        $length = count($idscourses);

        if ($length === 0) {
            return 0;
        }

        $wherecompil = "(".$idscourses[0];
        for ($i = 1; $i < $length; $i++) {
            $wherecompil .= ", ".$idscourses[$i];
        }
        $wherecompil .= ")";

        $sql = "SELECT count(DISTINCT logs.userid) AS count
                  FROM {logstore_standard_log} logs
                  JOIN {role_assignments} assign ON (logs.userid = assign.userid AND logs.contextid = assign.contextid)
                  JOIN {role} role ON assign.roleid = role.id
                 WHERE role.archetype = :archetype
                       AND eventname like '%course_viewed'
                       AND logs.contextlevel = :coursecontext
                       AND logs.courseid in ".$wherecompil."
                       AND logs.timecreated BETWEEN :begintimestamp AND :endtimestamp";

        $params = [
            'archetype' => $studentarchetype,
            'coursecontext' => CONTEXT_COURSE,
            'begintimestamp' => $begintimestamp,
            'endtimestamp' => $endtimestamp,
        ];

        $record = $DB->get_record_sql($sql, $params);

        return $record->count;
    }

    /**
     * Return the number of registrants in the ID course $id_course according to the assignment table.
     */
    public function count_registered_students_of_course(int $idcourse): int {
        global $DB;
        $studentarchetype = configurator::get_instance()->get_student_archetype();

        $sql = "SELECT count(DISTINCT assign.userid) AS count
                  FROM {context} context
                  JOIN {role_assignments} assign ON context.id = assign.contextid
                  JOIN {role} role ON assign.roleid = role.id
                 WHERE role.archetype = :archetype
                       AND context.instanceid = :instanceid
                       AND context.contextlevel = :coursecontext";

        $params = [
            'archetype' => $studentarchetype,
            'instanceid' => $idcourse,
            'coursecontext' => CONTEXT_COURSE,
        ];

        $record = $DB->get_record_sql($sql, $params);

        return $record->count;
    }

    /**
     * Return the distinct number of students enrolled in at least one course whose ID is an element of the $ids_courses list.
     */
    public function count_distinct_registered_students_of_courses(array $idscourses): int {
        global $DB;

        utils::precondition_ids($idscourses);

        $length = count($idscourses);

        if ($length === 0) {
            return 0;
        }

        $wherecourseid = "enrol.courseid in (" . $idscourses[0];

        for ($i = 1; $i < $length; $i++) {
            if (!is_int($idscourses[$i])) {
                throw new Exception("Course IDs must be integers");
            }

            $wherecourseid .= ", " . $idscourses[$i];
        }
        $wherecourseid .= ")";

        $sql = "SELECT count(DISTINCT user_enrolments.userid) AS count
                  FROM mdl_user_enrolments user_enrolments
                  JOIN mdl_enrol enrol ON user_enrolments.enrolid = enrol.id
                 WHERE ".$wherecourseid." AND (enrol.enrolenddate > ? OR enrol.enrolenddate = 0)";

        $record = $DB->get_record_sql($sql, [strtotime("now")]);

        return $record->count;
    }

    /**
     * Counts the number of clicks on activities in the $id_course ID space
     * by activity type and over the period from $begin_timestamp to $end_timestamp
     */
    public function count_hits_on_activities_per_type(int $idcourse, int $begintimestamp, int $endtimestamp): array {
        global $DB;

        $studentarchetype = configurator::get_instance()->get_student_archetype();

        $sql = "SELECT logs.objecttable AS module, count(DISTINCT logs.id) AS count
                  FROM {logstore_standard_log} logs
                  JOIN {role_assignments} assign ON logs.userid = assign.userid
                  JOIN {role} role ON assign.roleid = role.id
                  JOIN {context} context ON logs.contextid = context.id
                 WHERE role.archetype = :archetype
                       AND logs.courseid = :courseid
                       AND logs.target = 'course_module'
                       AND (context.contextlevel = " . CONTEXT_COURSE . " OR context.contextlevel = " . CONTEXT_MODULE . ")
              GROUP BY logs.objecttable";

        $params = [
            'archetype' => $studentarchetype,
            'courseid' => $idcourse,
            'instanceid' => $idcourse,
            'coursecontext' => CONTEXT_COURSE,
            'begintimestamp' => $begintimestamp,
            'endtimestamp' => $endtimestamp,
        ];

        $records = $DB->get_records_sql($sql, $params);

        $output = array_map(function($obj): int {
            return $obj->count;
        }, $records);

        return $output;
    }


    /**
     * Returns the courses of a category in the order chosen in the moodle settings.
     */
    public function get_children_courses_ordered(int $idcategory): array {
        global $DB;

        $sql = "SELECT * from {course}
                 WHERE category = ?
              ORDER BY sortorder ASC";

        $records = $DB->get_records_sql($sql, [$idcategory]);

        $output = [];

        $index = 0;
        foreach ($records as $record) {
            $output[$index] = $record;
            $index++;
        }

        return $output;
    }

    /**
     * Returns the sub-categories of a category in the order chosen in the moodle settings.
     */
    public function get_children_categories_ordered(int $idcategory): array {
        global $DB;

        $sql = "SELECT * from {course_categories}
                 WHERE parent = ?
              ORDER BY sortorder ASC";

        $records = $DB->get_records_sql($sql, [$idcategory]);

        $output = [];

        $index = 0;
        foreach ($records as $record) {
            $output[$index] = $record;
            $index++;
        }

        return $output;
    }

    /**
     * Get the children courses ids of a specific parent category.
     * @param int $idcategory
     * @return array
     */
    public function get_children_courses_ids(int $idcategory): array {
        global $DB;

        $records = $DB->get_records("course", ["category" => $idcategory]);

        return array_map(
            function($course) {
                return intval($course->id);
            },
            $records
        );
    }

    /**
     * Get the children categories IDs of a specific parent category.
     * @param int $idcategory
     * @return array
     */
    public function get_children_categories_ids(int $idcategory): array {
        global $DB;

        $records = $DB->get_records("course_categories", ["parent" => $idcategory]);

        return array_map(
            function($category) {
                return intval($category->id);
            },
            $records
        );
    }

    public function get_courses_tree(): array {
        global $DB;

        $lowestparent = $DB->get_field_sql('SELECT MIN(parent) FROM {course_categories}');

        return $this->get_courses_tree_rec($lowestparent, true);
    }

    public function get_courses_tree_rec(int $idcategory, bool $root = false): array {
        global $DB;

        $catdata = [];
        $catdata['data'] = $DB->get_record("course_categories", ["id" => $idcategory]);

        if (!$root) {
            $childrencourses = $this->get_children_courses_ordered($idcategory);
            $catdata['children_courses'] = $childrencourses;
        }

        $childrencategories = $this->get_children_categories_ordered($idcategory);
        $catdata['children_categories'] = array_map(
            function ($category) {
                return $this->get_courses_tree_rec(intval($category->id));
            },
            $childrencategories
        );

        return $catdata;
    }

    /**
     * Returns the full path of the category $id_category.
     */
    public function get_category_path(int $idcategory): string {
        return $this->get_category_path_rec($idcategory, "");
    }


    /**
     * Get recursively all the ancestors of a category
     * @param int $idcategory
     * @param string $output
     * @return string
     */
    protected function get_category_path_rec(int $idcategory, string $output): string {
        global $DB;

        $record = $DB->get_record("course_categories", ["id" => $idcategory]);

        if ($record->parent == 0) {
            return $output . $record->name;
        } else {
            return $this->get_category_path_rec($record->parent, $output . $record->name . "/");
        }
    }

    /**
     * Returns the ids of the visible active non-blacklisted courses in an array.
     */
    public function get_whitelisted_courses_ids(): array {
        global $DB;

        $config = configurator::get_instance();
        $data = $config->get_data();

        // The course that matches the site is blacklisted by default.
        $blacklistedcourses = [1];
        foreach ($data["blacklisted_courses"] as $courseid => $value) {
            if ($value == 1) {
                $blacklistedcourses[] = $courseid;
            }
        }

        $sql = "SELECT course.id AS id
                  FROM {course} AS course
                 WHERE true";
        if (count($blacklistedcourses) > 0) {
            $sql .= " AND course.id NOT IN (".implode(",", $blacklistedcourses).")";
        }

        $records = $DB->get_records_sql($sql);

        $output = array_map(
            function($course) {
                return intval($course->id);
            },
            $records
        );
        return $output;
    }

    /**
     * Returns in an array of objects the id, idnumber, full name and class name of the courses
     * whose id is an element of the $ids array and which have been visited by at least one learner
     * during the period from timestamp $begin_date to timestamp $end_date.
     */
    public function filter_living_courses_on_period(array $idscourses, int $begintimestamp, int $endtimestamp): array {
        global $DB;

        $studentarchetype = configurator::get_instance()->get_student_archetype();

        utils::precondition_ids($idscourses);

        if (count($idscourses) === 0) {
            return [];
        }

        $sql = "SELECT DISTINCT course.id AS id, 
                                course.idnumber AS idnumber, 
                                course.fullname AS fullname,
                                category.id AS category_id, 
                                category.name AS category_name
                  FROM {course} course
                  JOIN {logstore_standard_log} logs ON course.id = logs.courseid
                  JOIN {role_assignments} assign ON logs.userid = assign.userid
                  JOIN {role} role ON assign.roleid = role.id
                  JOIN {course_categories} category ON category.id = course.category
                 WHERE role.archetype = :archetype
                       AND logs.timecreated BETWEEN :begintimestamp AND :endtimestamp
                       AND logs.eventname like '%course_viewed'
                       AND course.id IN (".implode(",", $idscourses).")";

        $params = [
            'archetype' => $studentarchetype,
            'begintimestamp' => $begintimestamp,
            'endtimestamp' => $endtimestamp,
        ];

        $records = $DB->get_records_sql($sql, $params);
        foreach ($records as &$record) {
            $record->id = intval($record->id);
            $record->category_id = intval($record->category_id);
        }

        return $records;
    }

    /* Adhoc task management */

    // Counts the number of adhoc tasks.
    public function count_adhoc_tasks(): int {
        global $DB;
        return $DB->count_records("task_adhoc", ['classname' => '\\report_hybridmeter\\task\\processing']);
    }

    // Unschedule all ahdoc tasks.
    public function clear_adhoc_tasks() {
        global $DB;
        return $DB->delete_records("task_adhoc", ['classname' => '\\report_hybridmeter\\task\\processing']);
    }

    // Schedule an adhoc task at timestamp $timestamp.
    public function schedule_adhoc_task($timestamp) {
        $task = new processing();
        $task->set_next_run_time($timestamp);
        \core\task\manager::queue_adhoc_task($task);
    }
}
