<?php

namespace report_hybridmeter\classes;

require_once(__DIR__."/test_scenario.php");
require_once(__DIR__."/logger.php");

abstract class test_scenario_course extends test_scenario {
    public $course_id;

    protected function __construct($name, $course_id) {
        parent::__construct($name);
        $this->course_id = $course_id;
    }

    abstract public function inclusion();
    abstract public function common_tests();
    abstract public function specific_tests();

    protected function test_timestamps() {
        $configurator = \report_hybridmeter\classes\configurator::getInstance();
        $begin_timestamp = $configurator->get_begin_timestamp();
        $end_timestamp = $configurator->get_end_timestamp();

        echo "<h3>Vérification de la cohérence des timestamps</h3>";

        echo "<p>Les timestamp begin et end sont ".$begin_timestamp." et ".$end_timestamp."<br/>
        Soit ".utils::timestamp_to_datetime($begin_timestamp)." et ".utils::timestamp_to_datetime($end_timestamp)."</p>";
    }
}