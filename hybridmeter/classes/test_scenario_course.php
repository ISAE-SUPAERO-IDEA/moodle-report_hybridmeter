<?php
/*
 * Hybrid Meter
 * Copyright (C) 2020 - 2024  ISAE-SUPAERO
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace report_hybridmeter\classes;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__."/test_scenario.php");
require_once(__DIR__."/logger.php");

abstract class test_scenario_course extends test_scenario {
    public $course_id;

    protected function __construct(string $name, int $course_id) {
        parent::__construct($name);
        $this->course_id = $course_id;
    }

    abstract public function inclusion();
    abstract public function common_tests();
    abstract public function specific_tests();

    protected function test_timestamps() {
        $configurator = \report_hybridmeter\classes\configurator::get_instance();
        $begin_timestamp = $configurator->get_begin_timestamp();
        $end_timestamp = $configurator->get_end_timestamp();

        echo "<h3>Checking the consistency of timestamps</h3>";

        echo "<p>Begin and end timestamps are " . $begin_timestamp . " and " . $end_timestamp . "<br/>
        That is, " . utils::timestamp_to_datetime($begin_timestamp) . " and " . utils::timestamp_to_datetime($end_timestamp) . "</p>";
    }
}
