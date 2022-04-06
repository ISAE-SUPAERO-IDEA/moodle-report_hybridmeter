<?php
require_once(__DIR__."/../diagnostic_component.php");

abstract class NU_test extends test {
    protected $course_id;

    public function __construct($id) {
        $this->course_id = $id;
    }

    public function include_all() {
        include_once(__DIR__."/../data_provider.php");
        include_once(__DIR__."/../traitement.php");
        include_once(__DIR__."/../configurator.php");
        include_once(__DIR__."/../exporter.php");
        include_once(__DIR__."/../../indicators.php");
    }

    abstract public function tests();
}
    