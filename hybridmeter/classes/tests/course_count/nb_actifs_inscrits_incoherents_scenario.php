<?php

namespace report_hybridmeter\classes\tests\course_count;

require_once(__DIR__."/../course_count_scenario_abstract.php");

use \report_hybridmeter\classes\utils as utils;

class nb_actifs_inscrits_incoherents_scenario extends \report_hybridmeter\classes\tests\course_count_scenario_abstract {
    public function __construct($course_id) {
        parent::__construct("Nombre d'étudiants actifs incohérent par rapport au nombre d'inscrits", $course_id);
    }

    public function specific_tests() {
        $this->test_count_student_single_visitors_on_course();
        $this->dump_active_logs();
        $this->test_count_registered_students_of_course();
        $this->dump_registered_students();
    }
}