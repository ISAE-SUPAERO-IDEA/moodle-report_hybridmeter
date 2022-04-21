<?php

namespace report_hybridmeter\classes\tests\course_count;

require_once(__DIR__."/../course_count_scenario_abstract.php");

use \report_hybridmeter\classes\utils as utils;

class nb_inscrits_incoherent_scenario extends \report_hybridmeter\classes\tests\course_count_scenario_abstract {
    public function __construct($course_id) {
        parent::__construct("Nombre d'étudiants inscrits incohérent", $course_id);
    }

    public function dump_registered_students() {
        $this->test_count_registered_students_of_course();
        $this->dump_registered_students();
    }
}