<?php

namespace report_hybridmeter\classes;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__."/configurator.php");
require_once(dirname(__FILE__).'/../../../config.php');
require_once(dirname(__FILE__).'/task/processing.php');
require_once(dirname(__FILE__).'/utils.php');

use \report_hybridmeter\classes\utils as utils;
use \report_hybridmeter\classes\configurator as configurator;
use \report_hybridmeter\task\processing as processing;
use \report_hybridmeter\classes\logger as logger;
use Exception;

class data_provider {

    protected static $instance = null;

    public function __construct(){
    }

    public static function get_instance() {
        if (self::$instance == null){
            self::$instance = new data_provider();
        }

        return self::$instance;
    }

    /* Indicator functions */

    // @return the number of activities for each activity type for the ID course $id_course
    public function count_activities_per_type_of_course(int $id_course): array {
        global $DB;
        $output=array();

        $sql = "SELECT modules.name, count(modules.name) AS count
                  FROM ".$DB->get_prefix()."course_modules course_modules
                  JOIN ".$DB->get_prefix()."modules modules ON course_modules.module = modules.id
                 WHERE course_modules.course = ?
              GROUP BY modules.name";

        $records = $DB->get_records_sql($sql, array($id_course));

        foreach($records as $key => $object){
            $output[$object->name] = $object->count;
        }

        $sql = "SELECT ".$DB->get_prefix()."modules.name AS name, 0 AS count
                  FROM ".$DB->get_prefix()."modules
                 WHERE name NOT IN (
                    SELECT modules.name AS name
                      FROM ".$DB->get_prefix()."course_modules course_modules
                      JOIN ".$DB->get_prefix()."modules modules ON course_modules.module = modules.id
                     WHERE course_modules.course = ?
                  GROUP BY modules.name
                )";

        $records = $DB->get_records_sql($sql, array($id_course));

        foreach($records as $key => $object){
            $output[$object->name] = $object->count;
        }

        return $output;
    }

    // @return the number of student visits to the ID course $id_course during the period from $begin_timestamp to $end_timestamp
    public function count_student_visits_on_course(int $id_course, int $begin_timestamp, int $end_timestamp): int {
        global $DB;
        $student_archetype = configurator::get_instance()->get_student_archetype();

        $sql = "SELECT count(DISTINCT logs.id) AS count
                  FROM ".$DB->get_prefix()."logstore_standard_log logs
                  JOIN ".$DB->get_prefix()."role_assignments assign ON logs.userid = assign.userid
                  JOIN ".$DB->get_prefix()."role role ON assign.roleid = role.id
                 WHERE role.archetype = :archetype
                       AND logs.eventname like '%course_viewed'
                       AND logs.courseid = :courseid
                       AND logs.contextlevel= :contextcourse
                       AND timecreated BETWEEN :begintimestamp AND :endtimestamp";

        $parameters = array(
            'archetype' => $student_archetype,
            'courseid' => $id_course,
            'instanceid' => $id_course,
            'contextcourse' => CONTEXT_COURSE,
            'begintimestamp' => $begin_timestamp,
            'endtimestamp' => $end_timestamp,
        );

        $record=$DB->get_record_sql($sql, $parameters);

        return $record->count;
    }

    // Counts the number of unique users according to the course and the period chosen
    public function count_student_single_visitors_on_courses(array $ids_courses, int $begin_timestamp, int $end_timestamp): int {
        global $DB;

        utils::precondition_ids($ids_courses);

        $student_archetype = configurator::get_instance()->get_student_archetype();

        $length = count($ids_courses);

        if($length === 0) {
            return 0;
        }

        $where_compil = "(".$ids_courses[0];
        for($i = 1; $i < $length; $i++){
            $where_compil .= ", ".$ids_courses[$i];
        }
        $where_compil .= ")";

        $sql = "SELECT count(DISTINCT logs.userid) AS count
                  FROM ".$DB->get_prefix()."logstore_standard_log logs
                  JOIN ".$DB->get_prefix()."role_assignments assign ON (logs.userid = assign.userid AND logs.contextid = assign.contextid)
                  JOIN ".$DB->get_prefix()."role role ON assign.roleid = role.id
                 WHERE role.archetype = :archetype
                       AND eventname like '%course_viewed'
                       AND logs.contextlevel = :coursecontext
                       AND logs.courseid in ".$where_compil."
                       AND logs.timecreated BETWEEN :begintimestamp AND :endtimestamp";

        $params = array(
            'archetype' => $student_archetype,
            'coursecontext' => CONTEXT_COURSE,
            'begintimestamp' => $begin_timestamp,
            'endtimestamp' => $end_timestamp,
        );

        $record=$DB->get_record_sql($sql, $params);

        return $record->count;
    }

    // @return the number of registrants in the ID course $id_course according to the assignment table
    public function count_registered_students_of_course(int $id_course): int {
        global $DB;
        $student_archetype = configurator::get_instance()->get_student_archetype();

        $sql = "SELECT count(DISTINCT role.id) AS count
                  FROM ".$DB->get_prefix()."context context
                  JOIN ".$DB->get_prefix()."role_assignments assign ON context.id = assign.contextid
                  JOIN ".$DB->get_prefix()."role role ON assign.roleid = role.id
                 WHERE role.archetype = :archetype
                       AND context.instanceid = :instanceid
                       AND context.contextlevel = :coursecontext";

        $params = array(
            'archetype' => $student_archetype,
            'instanceid' => $id_course,
            'coursecontext' => CONTEXT_COURSE,
        );

        $record=$DB->get_record_sql($sql, $params);

        return $record->count;
    }

    // @return the distinct number of students enrolled in at least one course whose ID is an element of the $ids_courses list
    public function count_distinct_registered_students_of_courses(array $ids_courses): int {
        global $DB;

        utils::precondition_ids($ids_courses);

        $length = count($ids_courses);

        if($length === 0) {
            return 0;
        }

        $where_courseid = "enrol.courseid in (" . $ids_courses[0];

        for($i = 1; $i < $length; $i++){
            if(!is_int($ids_courses[$i]))
                throw new Exception("Course IDs must be integers");

            $where_courseid .= ", " . $ids_courses[$i];
        }
        $where_courseid .= ")";

        $sql = "SELECT count(DISTINCT user_enrolments.userid) AS count
                  FROM mdl_user_enrolments user_enrolments
                  JOIN mdl_enrol enrol ON user_enrolments.enrolid = enrol.id
                 WHERE ".$where_courseid." AND (enrol.enrolenddate > ? OR enrol.enrolenddate = 0)";

        $record = $DB->get_record_sql($sql, array(strtotime("now")));

        return $record->count;
    }

    /* Counts the number of clicks on activities in the $id_course ID space
     * by activity type and over the period from $begin_timestamp to $end_timestamp
     */
    public function count_hits_on_activities_per_type(int $id_course, int $begin_timestamp, int $end_timestamp): array {
        global $DB;

        $student_archetype = configurator::get_instance()->get_student_archetype();

        $sql = "SELECT logs.objecttable AS module, count(DISTINCT logs.id) AS count
                  FROM ".$DB->get_prefix()."logstore_standard_log logs
                  JOIN ".$DB->get_prefix()."role_assignments assign ON logs.userid = assign.userid
                  JOIN ".$DB->get_prefix()."role role ON assign.roleid = role.id
                  JOIN ".$DB->get_prefix()."context context ON logs.contextid = context.id
                 WHERE role.archetype = :archetype
                       AND courseid = :courseid
                       AND logs.target = 'course_module'
                       AND context.instanceid = :instanceid
                       AND context.contextlevel = :coursecontext
                       AND timecreated BETWEEN :begintimestamp AND :endtimestamp
              GROUP BY logs.objecttable";

        $params=array(
            'archetype' => $student_archetype,
            'courseid' => $id_course,
            'instanceid' => $id_course,
            'coursecontext' => CONTEXT_COURSE,
            'begintimestamp' => $begin_timestamp,
            'endtimestamp' => $end_timestamp,
        );

        $records = $DB->get_records_sql($sql, $params);

        $output = array_map(function($obj): int {
            return $obj->count;
        }, $records);

        return $output;
    }


    /*Course retrieval function*/

    // @returns the courses of a category in the order chosen in the moodle settings
    public function get_children_courses_ordered(int $id_category): array {
        global $DB;

        $sql = "SELECT * from ".$DB->get_prefix()."course
                 WHERE category = ?
              ORDER BY sortorder ASC";

        $records = $DB->get_records_sql($sql, array($id_category));

        $output = array();

        $index = 0;
        foreach ($records as $record) {
            $output[$index] = $record;
            $index++;
        }

        return $output;
    }

    // @returns the sub-categories of a category in the order chosen in the moodle settings
    public function get_children_categories_ordered(int $id_category): array {
        global $DB;

        $sql = "SELECT * from ".$DB->get_prefix()."course_categories
                 WHERE parent = ? 
              ORDER BY sortorder ASC";

        $records = $DB->get_records_sql($sql, array($id_category));

        $output = array();

        $index = 0;
        foreach ($records as $record) {
            $output[$index] = $record;
            $index++;
        }

        return $output;
    }

    public function get_children_courses_ids(int $id_category): array {
        global $DB;

        $records = $DB->get_records("course", array("category" => $id_category));

        return array_map(
            function($course) {
                return intval($course->id);
            },
            $records
        );
    }

    public function get_children_categories_ids(int $id_category): array {
        global $DB;
        
        $records = $DB->get_records("course_categories", array("parent" => $id_category));

        return array_map(
            function($category) {
                return intval($category->id);
            },
            $records
        );
    }

    public function get_courses_tree(int $id_category = 1): array {
        global $DB;

        $children_categories = $this->get_children_categories_ordered($id_category);
        $children_courses = $this->get_children_courses_ordered($id_category);
        $cat_data = [];


        $cat_data['data'] = $DB->get_record("course_categories", array("id" => $id_category));

        $cat_data['children_courses'] = $children_courses;

        $cat_data['children_categories'] = array_map(
            function ($category) {
                return $this->get_courses_tree(intval($category->id));
            },
            $children_categories
        );

        return $cat_data;
    }

    // @returns the full path of the category $id_category
    public function get_category_path(int $id_category): string {
        return $this->get_category_path_rec($id_category, "");
    }


    protected function get_category_path_rec(int $id_category, string $output): string {
        global $DB;

        $record = $DB->get_record("course_categories", array("id" => $id_category));

        if($record->parent == 0)
            return $output . $record->name;
        else
            return $this->get_category_path_rec($record->parent, $output . $record->name . "/");
    }

    // @returns the ids of the visible active non-blacklisted courses in an array
    public function get_whitelisted_courses_ids(): array {
        global $DB;

        $config = configurator::get_instance();
        $data = $config->get_data();
        $blacklisted_courses = array_keys($data["blacklisted_courses"]);
        
        // the course that matches the site is blacklisted by default
        array_push($blacklisted_courses, 1);

        $sql = "SELECT course.id AS id 
                  FROM ".$DB->get_prefix()."course AS course
                 WHERE true";
         
        if (count($blacklisted_courses)>0) {
            $sql .= " AND course.id NOT IN (".implode(",",$blacklisted_courses).")";
        }

        $records=$DB->get_records_sql($sql);
        
        $output = array_map(
            function($course) {
                return intval($course->id);
            },
            $records
        );

        return $output;
    }

    /* returns in an array of objects the id, idnumber, full name and class name of the courses
     * whose id is an element of the $ids array and which have been visited by at least one learner
     * during the period from timestamp $begin_date to timestamp $end_date. 
     */
    public function filter_living_courses_on_period(array $ids_courses, int $begin_timestamp, int $end_timestamp): array {
        global $DB;

        $student_archetype = configurator::get_instance()->get_student_archetype();

        utils::precondition_ids($ids_courses);

        if(count($ids_courses) === 0) {
            return array();
        }

        $sql = "SELECT DISTINCT course.id AS id, course.idnumber AS idnumber, course.fullname AS fullname,
                       category.id AS category_id, category.name AS category_name
                  FROM ".$DB->get_prefix()."course course
                  JOIN ".$DB->get_prefix()."logstore_standard_log logs ON course.id = logs.courseid
                  JOIN ".$DB->get_prefix()."role_assignments assign ON logs.userid = assign.userid
                  JOIN ".$DB->get_prefix()."role role ON assign.roleid = role.id
                  JOIN ".$DB->get_prefix()."course_categories category ON category.id = course.category
                 WHERE role.archetype = :archetype
                       AND logs.timecreated BETWEEN :begintimestamp AND :endtimestamp
                       AND logs.eventname like '%course_viewed'
                       AND course.id IN (".implode(",",$ids_courses).")";

        $params = array(
            'archetype' => $student_archetype,
            'begintimestamp' => $begin_timestamp,
            'endtimestamp' => $end_timestamp,
        );

        $records = $DB->get_records_sql($sql, $params);
        
        return $records;
    }

    /* Adhoc task management */

    // counts the number of adhoc tasks
    public function count_adhoc_tasks(): int {
        global $DB;
        return $DB->count_records("task_adhoc", array('classname' => '\\report_hybridmeter\\task\\processing'));
    }

    // unschedule all ahdoc tasks
    public function clear_adhoc_tasks() {
        global $DB;
        return $DB->delete_records("task_adhoc", array('classname' => '\\report_hybridmeter\\task\\processing'));
    }

    public function get_adhoc_tasks_list(): int {
        global $DB;

        return array_values(
            array_map(
                function($task){
                    return array(
                        'id' => intval($task->id),
                        'nextruntime' => $task->nextruntime,
                    );
                },
                $DB->get_records("task_adhoc", array('classname' => '\\report_hybridmeter\\task\\processing'))
            )
        );
    }

    // schedule an adhoc task at timestamp $timestamp
    public function schedule_adhoc_task($timestamp){
        $task = new processing();
        $task->set_next_run_time($timestamp);
        \core\task\manager::queue_adhoc_task($task);
    }
}
