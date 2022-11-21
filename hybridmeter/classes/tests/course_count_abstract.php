<?php

namespace report_hybridmeter\classes\tests;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__."/../test_scenario_course.php");
require_once(__DIR__."/../utils.php");

use \report_hybridmeter\classes\utils as utils;

abstract class course_count_abstract extends \report_hybridmeter\classes\test_scenario_course {
    
    protected function __construct(string $name, int $course_id) {
        parent::__construct($name, $course_id);
    }

    public function inclusion() {
        require_once(__DIR__."/../../../../config.php");
        include_once(__DIR__."/../indicators.php");
        include_once(__DIR__."/../data_provider.php");
        include_once(__DIR__."/../configurator.php");
    }

    public function common_tests() {
        $this->test_timestamps();
        $this->test_student_archetype();
    }

    abstract public function specific_tests();

    protected function test_count_student_single_visitors_on_course() {
        $configurator = \report_hybridmeter\classes\configurator::get_instance();
        $data_provider = \report_hybridmeter\classes\data_provider::get_instance();

        $begin_timestamp = $configurator->get_begin_timestamp();
        $end_timestamp = $configurator->get_end_timestamp();

        echo "<h3>Testing the count_student_single_visitors_on_course function</h3>";
        
        echo "<p>The begin and end timestamps are ".$begin_timestamp." and ".$end_timestamp."</p>";

        echo "<p>The function returned : </p>";

        var_dump($data_provider->count_student_single_visitors_on_courses(
            array($this->course_id),
            $begin_timestamp,
            $end_timestamp
        ));
    }

    protected function test_count_registered_students_of_course() {
        $data_provider = \report_hybridmeter\classes\data_provider::get_instance();

        echo "<h3>Testing the count_registered_students_of_course function</h3>";

        echo "<p>The function returned : </p>";

        var_dump($data_provider->count_registered_students_of_course($this->course_id));
    }

    protected function dump_registered_students() {
        global $DB;

        echo "<h3>Dump of the first thousand registrants of the course (regardless of their role)</h3>";

        echo "<p>Here is the result of the query :</p>";

        $sql = "SELECT assign.*, cont.contextlevel, cont.instanceid, cont.path, cont.depth, cont.locked
                  FROM ".$DB->get_prefix()."role_assignments assign
                  JOIN ".$DB->get_prefix()."context cont ON assign.contextid = cont.id
                 WHERE cont.instanceid = ?
                 LIMIT 1000";

        $records = $DB->get_records_sql($sql, array($this->course_id));

        echo utils::objects_array_to_html($records);
    }

    protected function dump_active_logs() {
        global $DB;
        
        echo "<h3>Dump of the first thousand entries in the course logs during the activity period</h3>";

        $configurator = \report_hybridmeter\classes\configurator::get_instance();

        $begin_timestamp = $configurator->get_begin_timestamp();
        $end_timestamp = $configurator->get_end_timestamp();

        $sql = "SELECT logs.*, role.shortname, role.archetype,
                       role.description, user.username,
                       user.firstname, user.lastname
                  FROM ".$DB->get_prefix()."logstore_standard_log logs
             LEFT JOIN ".$DB->get_prefix()."role_assignments assignments ON (logs.userid = assignments.userid AND logs.contextid = assignments.contextid)
             LEFT JOIN ".$DB->get_prefix()."role role ON assignments.roleid = role.id
                  JOIN ".$DB->get_prefix()."user user ON logs.userid = user.id
                 WHERE logs.courseid = :courseid
                   AND logs.timecreated BETWEEN :begintimestamp AND :endtimestamp
              ORDER BY logs.timecreated DESC";

        $params = array(
            'courseid' => $this->course_id,
            'begintimestamp' => $begin_timestamp,
            'endtimestamp' => $end_timestamp,
        );

        $records = $DB->get_records_sql($sql, $params, 0, 1000);

        echo "<p>Here it is : </p>";

        echo utils::objects_array_to_html($records);
    }
}

