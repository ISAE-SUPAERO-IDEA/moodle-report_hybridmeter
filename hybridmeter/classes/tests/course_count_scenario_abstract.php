<?php

namespace report_hybridmeter\classes\tests;

require_once(__DIR__."/../test_scenario_course.php");

use \report_hybridmeter\classes\utils as utils;

abstract class course_count_scenario_abstract extends \report_hybridmeter\classes\test_scenario_course {
    
    protected function __construct($name, $course_id) {
        parent::__construct($name, $course_id);
    }

    public function inclusion() {
        include_once(__DIR__."/../../indicators.php");
        include_once(__DIR__."/../data_provider.php");
        include_once(__DIR__."/../configurator.php");
    }

    public function common_tests() {
        $this->test_timestamps();
        $this->test_student_role();
    }

    abstract public function specific_tests();

    protected function test_count_student_single_visitors_on_course() {
        $configurator = \report_hybridmeter\classes\configurator::getInstance();
        $data_provider = \report_hybridmeter\classes\data_provider::getInstance();

        $begin_timestamp = $configurator->get_begin_timestamp();
        $end_timestamp = $configurator->get_end_timestamp();

        echo "<h3>Test de la fonction count_student_single_visitors_on_course</h3>";
        
        echo "<p>Les timestamp begin et end sont pour rappel ".$begin_timestamp."et ".$end_timestamp."</p>";

        echo "<p>La fonction nous retourne : </p>";

        var_dump($data_provider->count_student_single_visitors_on_courses(
            array($this->course_id),
		    $begin_timestamp,
		    $end_timestamp
        ));
    }

    protected function test_count_registered_students_of_course() {
        $data_provider = \report_hybridmeter\classes\data_provider::getInstance();

        echo "<h3>Test de la fonction count_registered_students_of_course</h3>";

        echo "<p>La fonction nous retourne : </p>";

        var_dump($data_provider->count_registered_students_of_course($this->course_id));
    }

    protected function dump_registered_students() {
        global $DB;

        echo "<h3>Dump des mille premiers inscrits du cours (peu importe leur rôle)</h3>";

        echo "<p>Voici le résultat de la requête </p>";

        $records=$DB->get_records_sql(
            "select assign.*, cont.contextlevel, cont.instanceid, cont.path, cont.depth, cont.locked from ".$DB->get_prefix()."role_assignments as assign
            inner join ".$DB->get_prefix()."context as cont on assign.contextid=cont.id
            where cont.instanceid=? limit 1000",
            array($this->course_id)
        );

        echo utils::objects_array_to_html($records);
    }

    protected function dump_active_logs() {
        global $DB;
        
        echo "<h3>Dump des milles premières entrées dans les logs du cours durant la période d'activité</h3>";

        $configurator = \report_hybridmeter\classes\configurator::getInstance();

        $begin_timestamp = $configurator->get_begin_timestamp();
        $end_timestamp = $configurator->get_end_timestamp();

        $records=$DB->get_records_sql(
            "select * from ".$DB->get_prefix()."logstore_standard_log as logs
            where courseid=?
            and timecreated between ? and ? limit 1000",
            array($this->course_id, $begin_timestamp, $end_timestamp)
        );

        echo "<p>Voici : </p>";

        echo utils::objects_array_to_html($records);
    }
}

