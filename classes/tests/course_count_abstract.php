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
 * @package report_hybridmeter
 */
namespace report_hybridmeter\tests;

use report_hybridmeter\configurator as configurator;
use report_hybridmeter\utils as utils;

abstract class course_count_abstract extends \report_hybridmeter\tests\test_scenario_course {

    protected function __construct(string $name, int $courseid) {
        parent::__construct($name, $courseid);
    }

    public function inclusion() {
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
        $configurator = configurator::get_instance();
        $dataprovider = \report_hybridmeter\data_provider::get_instance();

        $begintimestamp = $configurator->get_begin_timestamp();
        $endtimestamp = $configurator->get_end_timestamp();

        echo "<h3>Testing the count_student_single_visitors_on_course function</h3>";

        echo "<p>The begin and end timestamps are ".$begintimestamp." and ".$endtimestamp."</p>";

        echo "<p>The function returned : </p>";

        var_dump($dataprovider->count_student_single_visitors_on_courses(
            [$this->course_id],
            $begintimestamp,
            $endtimestamp
        ));
    }

    protected function test_count_registered_students_of_course() {
        $dataprovider = \report_hybridmeter\data_provider::get_instance();

        echo "<h3>Testing the count_registered_students_of_course function</h3>";

        echo "<p>The function returned : </p>";

        var_dump($dataprovider->count_registered_students_of_course($this->course_id));
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

        $records = $DB->get_records_sql($sql, [$this->course_id]);

        echo utils::objects_array_to_html($records);
    }

    protected function dump_logs() {
        global $DB;

        echo "<h3>Dump of the first thousand entries in the course logs during the activity period</h3>";

        $configurator = configurator::get_instance();

        $begintimestamp = $configurator->get_begin_timestamp();
        $endtimestamp = $configurator->get_end_timestamp();

        $sql = "SELECT logs.*, role.shortname, role.archetype,
                       role.description, u.username,
                       u.firstname, u.lastname
                  FROM ".$DB->get_prefix()."logstore_standard_log logs
             LEFT JOIN ".$DB->get_prefix()."role_assignments assignments
                ON (logs.userid = assignments.userid AND logs.contextid = assignments.contextid)
             LEFT JOIN ".$DB->get_prefix()."role role ON assignments.roleid = role.id
                  JOIN ".$DB->get_prefix()."user u ON logs.userid = u.id
                 WHERE logs.courseid = :courseid
                   AND logs.timecreated BETWEEN :begintimestamp AND :endtimestamp
              ORDER BY logs.timecreated DESC";

        $params = [
            'courseid' => $this->course_id,
            'begintimestamp' => $begintimestamp,
            'endtimestamp' => $endtimestamp,
        ];

        $records = $DB->get_records_sql($sql, $params, 0, 1000);

        echo "<p>Here it is : </p>";

        echo utils::objects_array_to_html($records);
    }

    protected function dump_active_logs() {
        global $DB;

        echo "<h3>Dump of the first thousand entries in the course logs during the activity period</h3>";

        $configurator = configurator::get_instance();

        $begintimestamp = $configurator->get_begin_timestamp();
        $endtimestamp = $configurator->get_end_timestamp();

        $studentarchetype = configurator::get_instance()->get_student_archetype();

        $sql = "SELECT logs.id,
                    timecreated,
                    logs.target,
                    assign.id as assign_id,
                    role.id as role_id,
                    role.archetype,
                    context.id as context_id,
                    logs.userid, logs.courseid,
                    context.instanceid, context.contextlevel
                  FROM {logstore_standard_log} logs
                  JOIN {role_assignments} assign ON logs.userid = assign.userid
                  JOIN {role} role ON assign.roleid = role.id
                  JOIN {context} context ON logs.contextid = context.id
                 WHERE role.archetype = :archetype
                       AND logs.courseid = :courseid
                       AND logs.target = 'course_module'
                       AND (context.contextlevel = " . CONTEXT_COURSE . " OR context.contextlevel = " . CONTEXT_MODULE . ")
                       AND timecreated BETWEEN :begintimestamp AND :endtimestamp
              ";
        $params = [
            'archetype' => $studentarchetype,
            'courseid' => $this->course_id,
            'instanceid' => $this->course_id,
            'begintimestamp' => $begintimestamp,
            'endtimestamp' => $endtimestamp,
        ];
        debugging(print_r(CONTEXT_COURSE, 1));

        $records = $DB->get_records_sql($sql, $params, 0, 1000);

        echo "<p>Here it is : </p>";

        echo utils::objects_array_to_html($records);
    }

    protected function dump_module_activity() {
        global $DB;

        echo "<h3>Dump the module activity during the activity period</h3>";

        $configurator = configurator::get_instance();

        $begintimestamp = $configurator->get_begin_timestamp();
        $endtimestamp = $configurator->get_end_timestamp();

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
                       AND timecreated BETWEEN :begintimestamp AND :endtimestamp
                       GROUP BY logs.objecttable
              ";
        $params = [
            'archetype' => $studentarchetype,
            'courseid' => $this->course_id,
            'instanceid' => $this->course_id,
            'begintimestamp' => $begintimestamp,
            'endtimestamp' => $endtimestamp,
        ];

        $records = $DB->get_records_sql($sql, $params, 0, 1000);

        echo "<p>Here it is : </p>";

        echo utils::objects_array_to_html($records);
    }
}

