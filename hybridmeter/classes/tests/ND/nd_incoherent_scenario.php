<?php

namespace report_hybridmeter\classes\tests\ND;

require_once(__DIR__."/../indicateurs_scenario_abstract.php");

use \report_hybridmeter\classes\utils as utils;

class nd_incoherent_scenario extends \report_hybridmeter\classes\tests\indicateurs_scenario_abstract {
    public function __construct($course_id) {
        parent::__construct("nd", "ND incohérent", $course_id);
    }

    public function specific_tests() {
        $this->test_count_activities_per_type_of_course();
        $this->test_coeffs();
        $this->test_hybridation_calculus();
        $this->dump_course_modules();
    }

    private function test_count_activities_per_type_of_course() {
        echo "<h3>Vérification des données récupérées depuis la base de données :</h3>";

        $data_provider = \report_hybridmeter\classes\data_provider::getInstance();
        
        echo "<p>La fonction count_activities_per_type_of_course nous retourne :</p>";
        echo utils::data_grouped_by_to_html($data_provider->count_activities_per_type_of_course($this->course_id));
    }

    private function dump_course_modules() {
        echo "<h3>Dump des activités du cours</h3>";
        global $DB;
        $records = $DB->get_records_sql(
            "select * from mdl_course_modules as cm inner join mdl_modules as mo on cm.module = mo.id where course = ?",
            array($this->course_id)
        );
        
        echo "<p>Voici : </p>";
        echo utils::objects_array_to_html($records);
    }
}