<?php

namespace report_hybridmeter\classes\tests\course_count;

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__)."/../../../../../config.php");
require_once(__DIR__."/../course_count_abstract.php");
require_once(__DIR__."/../utils.php");

use \report_hybridmeter\classes\utils as utils;

class inconsistent_registered_students extends \report_hybridmeter\classes\tests\course_count_abstract {
    public function __construct(int $course_id) {
        parent::__construct(get_string('inconsistent_registered_students', 'report_hybridmeter'), $course_id);
    }

    public function dump_registered_students() {
        $this->test_count_registered_students_of_course();
        $this->dump_registered_students();
    }
}