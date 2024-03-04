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
namespace report_hybridmeter\classes\tests\NU;

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__)."/../../../../../config.php");
require_once(__DIR__."/../indicator_abstract.php");
require_once(__DIR__."/../../utils.php");

use report_hybridmeter\classes\utils as utils;

class inconsistent_nu extends \report_hybridmeter\classes\tests\indicator_abstract {
    function __construct(int $courseid) {
        parent::__construct("nu", get_string('inconsistent_nu', 'report_hybridmeter'), $courseid);
    }

    public function specific_tests() {
        // $this->test_nd();
        $this->test_coeffs();
        $this->test_hybridation_calculus();
        $this->test_timestamps();
        $this->test_student_archetype();
        $this->test_count_hits_on_activities_per_type();
        $this->dump_hits_on_activities();
    }

    private function test_nd() {
        echo "<h3>verification of the non-nullity of the digitisation level</h3>";

        if ( $dataunserialized['data'][$this->course_id][REPORT_HYBRIDMETER_FIELD_DIGITALISATION_LEVEL] == 0 ) {
            echo "<h3>The level of digitisation seems to be zero, launch of a test set</h3>";
            echo "<div class='subtest'>";
            $ndnul = (new nd_nul_scenario($this->course_id))->test();
            echo "</div>";

            if($ndnul) {
                echo "<p>The level of digitisation seems to be really zero, so it is normal that the level of use is also zero.</p>";
            } else {
                echo "<p>The level of digitisation is also an issue and it is necessary to analyse the test results.</p>";
            }
        }
    }

    private function test_count_hits_on_activities_per_type() {
        echo "<h3>Checking database queries</h3>";

        $dataprovider = \report_hybridmeter\classes\data_provider::get_instance();
        $configurator = \report_hybridmeter\classes\configurator::get_instance();
        $begintimestamp = $configurator->get_begin_timestamp();
        $endtimestamp = $configurator->get_end_timestamp();

        echo "<p>begin and end timestamps are ".$begintimestamp." and ".$endtimestamp."</p>";

        echo "<p>count_hits_on_activities_per_type function returns :</p>";
        echo utils::data_grouped_by_to_html($dataprovider->count_hits_on_activities_per_type(
            $this->course_id,
            $begintimestamp,
            $endtimestamp
        ));
    }

    private function dump_hits_on_activities() {
        global $DB;

        echo "<h3>Dump of hits on course activities during the current capture period :</h3>";

        $dataprovider = \report_hybridmeter\classes\data_provider::get_instance();
        $configurator = \report_hybridmeter\classes\configurator::get_instance();
        $begintimestamp = $configurator->get_begin_timestamp();
        $endtimestamp = $configurator->get_end_timestamp();

        $sql = "SELECT logs.*, role.shortname, role.archetype,
                       role.description, u.username,
                       u.firstname, u.lastname
                  FROM ".$DB->get_prefix()."logstore_standard_log logs
             LEFT JOIN ".$DB->get_prefix()."role_assignments assignments ON (logs.userid = assignments.userid AND logs.contextid = assignments.contextid)
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
}
