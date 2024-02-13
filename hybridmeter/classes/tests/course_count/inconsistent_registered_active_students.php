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

namespace report_hybridmeter\classes\tests\course_count;

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__)."/../../../../../config.php");
require_once(__DIR__."/../course_count_abstract.php");
require_once(__DIR__."/../../utils.php");

use \report_hybridmeter\classes\utils as utils;

class inconsistent_registered_active_students extends \report_hybridmeter\classes\tests\course_count_abstract {
    public function __construct(int $course_id) {
        parent::__construct(get_string('inconsistent_registered_active_students', 'report_hybridmeter'), $course_id);
    }

    public function specific_tests() {
        $this->test_count_student_single_visitors_on_course();
        $this->dump_logs();
        $this->dump_active_logs();
        $this->dump_module_activity();
        $this->test_count_registered_students_of_course();
        $this->dump_registered_students();
    }
}