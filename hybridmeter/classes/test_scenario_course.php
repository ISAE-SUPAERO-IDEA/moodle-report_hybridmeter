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
 * @author Nassim Bennouar
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2020  ISAE-SUPAERO (https://www.isae-supaero.fr/)
 * @package
 */
namespace report_hybridmeter\classes;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__."/test_scenario.php");
require_once(__DIR__."/logger.php");

abstract class test_scenario_course extends test_scenario {
    public $courseid;

    protected function __construct(string $name, int $courseid) {
        parent::__construct($name);
        $this->course_id = $courseid;
    }

    abstract public function inclusion();
    abstract public function common_tests();
    abstract public function specific_tests();

    protected function test_timestamps() {
        $configurator = \report_hybridmeter\classes\configurator::get_instance();
        $begintimestamp = $configurator->get_begin_timestamp();
        $endtimestamp = $configurator->get_end_timestamp();

        echo "<h3>Checking the consistency of timestamps</h3>";

        echo "<p>Begin and end timestamps are " . $begintimestamp . " and " . $endtimestamp . "<br/>
        That is, " . utils::timestamp_to_datetime($begintimestamp) . " and " . utils::timestamp_to_datetime($endtimestamp) . "</p>";
    }
}
