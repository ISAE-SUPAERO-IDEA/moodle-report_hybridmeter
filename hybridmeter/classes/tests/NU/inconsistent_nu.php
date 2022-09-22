<?php

namespace report_hybridmeter\classes\tests\NU;

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__)."/../../../../../config.php");
require_once(__DIR__."/../indicator_abstract.php");
require_once(__DIR__."/../../utils.php");

use \report_hybridmeter\classes\utils as utils;

class inconsistent_nu extends \report_hybridmeter\classes\tests\indicator_abstract {
    function __construct(int $course_id) {
        parent::__construct("nu", get_string('inconsistent_nu', 'report_hybridmeter'), $course_id);
    }

    public function specific_tests() {
        //$this->test_nd();
        $this->test_coeffs();
        $this->test_hybridation_calculus();
        $this->test_timestamps();
        $this->test_student_archetype();
        $this->test_count_hits_on_activities_per_type();
        $this->dump_hits_on_activities();
    }

    private function test_nd() {
        echo "<h3>verification of the non-nullity of the digitisation level</h3>";

        if ( $data_unserialized['data'][$this->course_id][REPORT_HYBRIDMETER_FIELD_DIGITALISATION_LEVEL] == 0 ) {
            echo "<h3>The level of digitisation seems to be zero, launch of a test set</h3>";
            echo "<div class='subtest'>";
            $nd_nul = (new nd_nul_scenario($this->course_id))->test();
            echo "</div>";

            if($nd_nul)
                echo "<p>The level of digitisation seems to be really zero, so it is normal that the level of use is also zero.</p>";
            else
                echo "<p>The level of digitisation is also an issue and it is necessary to analyse the test results.</p>";
        }
    }

    private function test_count_hits_on_activities_per_type() {
        echo "<h3>Checking database queries</h3>";

        $data_provider = \report_hybridmeter\classes\data_provider::get_instance();
        $configurator = \report_hybridmeter\classes\configurator::get_instance();
        $begin_timestamp = $configurator->get_begin_timestamp();
        $end_timestamp = $configurator->get_end_timestamp();
        
        echo "<p>begin and end timestamps are ".$begin_timestamp." and ".$end_timestamp."</p>";

        echo "<p>count_hits_on_activities_per_type function returns :</p>";
        echo utils::data_grouped_by_to_html($data_provider->count_hits_on_activities_per_type(
            $this->course_id, 
            $begin_timestamp,
            $end_timestamp
        ));
    }

    private function dump_hits_on_activities() {
        global $DB;

        echo "<h3>Dump of hits on course activities during the current capture period :</h3>";
        
        $data_provider = \report_hybridmeter\classes\data_provider::get_instance();
        $configurator = \report_hybridmeter\classes\configurator::get_instance();
        $begin_timestamp = $configurator->get_begin_timestamp();
        $end_timestamp = $configurator->get_end_timestamp();

        $sql = "SELECT * FROM ".$DB->get_prefix()."logstore_standard_log log
                  JOIN ".$DB->get_prefix()."role_assignments ass ON log.userid = ass.userid
                  JOIN ".$DB->get_prefix()."role role ON ass.roleid = role.id
                 WHERE log.courseid = :courseid
                       AND log.target = 'course_module'
                       AND log.timecreated BETWEEN :begintimestamp AND :endtimestamp
              GROUP BY log.objecttable
                 LIMIT 1000";

        $params=array(
            'courseid' => $this->course_id,
            'begintimestamp' => $begin_timestamp,
            'endtimestamp' => $end_timestamp,
        );

        $records = $DB->get_records_sql($sql, $params);

        echo "<p>Here it is : </p>";

        echo utils::objects_array_to_html($records);
    }
}