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

// This file is part of Moodle - http://moodle.org
//
//  Moodle is free software: you can redistribute it and/or modify
//  it under the terms of the GNU General Public License as published by
//  the Free Software Foundation, either version 3 of the License, or
//  (at your option) any later version.
//
//  Moodle is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU General Public License for more details.
//
//  You should have received a copy of the GNU General Public License
//  along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace report_hybridmeter\classes\tests\ND;

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__)."/../../../../../config.php");
require_once(__DIR__."/../indicator_abstract.php");
require_once(__DIR__."/../../utils.php");

use \report_hybridmeter\classes\utils as utils;

class inconsistent_nd extends \report_hybridmeter\classes\tests\indicator_abstract {
    public function __construct(int $course_id) {
        parent::__construct("nd", get_string('inconsistent_nd', 'report_hybridmeter'), $course_id);
    }

    public function specific_tests() {
        $this->test_count_activities_per_type_of_course();
        $this->test_coeffs();
        $this->test_hybridation_calculus();
        $this->dump_course_modules();
    }

    private function test_count_activities_per_type_of_course() {
        echo "<h3>Verification of data retrieved from the database :</h3>";

        $data_provider = \report_hybridmeter\classes\data_provider::get_instance();
        
        echo "<p>The function count_activities_per_type_of_course returns :</p>";
        echo utils::data_grouped_by_to_html($data_provider->count_activities_per_type_of_course($this->course_id));
    }

    private function dump_course_modules() {
        global $DB;

        echo "<h3>Dump of course activities</h3>";
        
        $sql = "SELECT cm.id, mo.name, cm.*  FROM mdl_course_modules AS cm 
                  JOIN mdl_modules mo ON cm.module = mo.id
                 WHERE course = ?";

        $records = $DB->get_records_sql($sql, array($this->course_id));
        
        echo "<p>Here it is : </p>";
        echo utils::objects_array_to_html($records);
    }
}