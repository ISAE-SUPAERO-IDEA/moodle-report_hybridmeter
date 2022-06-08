<?php

namespace report_hybridmeter\classes\tests;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__."/../test_scenario_course.php");
require_once(__DIR__."/../utils.php");

use \report_hybridmeter\classes\utils as utils;
use Exception;

abstract class indicator_abstract extends \report_hybridmeter\classes\test_scenario_course {

    protected $indicator;

    public function __construct($indicator, $name, $course_id) {
        parent::__construct($name, $course_id);
        $this->indicator = $indicator;
        define(INDICATOR_ERROR, "Incorrect parameter, \$indicator must be \"nu\" or \"nd\"");
    }

    public function inclusion() {
        include_once(__DIR__."/../data_provider.php");
        include_once(__DIR__."/../processing.php");
        include_once(__DIR__."/../configurator.php");
        include_once(__DIR__."/../exporter.php");
        include_once(__DIR__."/../indicators.php");
        include_once(__DIR__."/../../constants.php");
    }

    public function common_tests() {
        $this->verifier_serialized($this->indicator);
    }

    abstract public function specific_tests();

    protected function verifier_serialized(string $indicator): bool {
        echo "<h3>Verification of the consistency of the serialized data with the data in the CSV provided</h3>";

        switch($this->indicator) {
            case "nu" :
                $indicator_name = "usage level";
                $indicator_index = REPORT_HYBRIDMETER_FIELD_USAGE_LEVEL;
                break;

            case "nd" :
                $indicator_name = "digitalisation level";
                $indicator_index = REPORT_HYBRIDMETER_FIELD_DIGITALISATION_LEVEL;
                break;

            default :
                throw new Exception(INDICATOR_ERROR);
                break;
        }

        global $CFG;
        $path_serialized_data = $CFG->dataroot."/hybridmeter/records/serialized_data";

        $data_unserialized = unserialize(file_get_contents($path_serialized_data));

        if(data_unserialized === false) {
            echo "<p>Unable to unserialize the results of the last calculation, could you restart the calculations for this course?</p>";
            return false;
        }
        
        if (!isset($data_unserialized['data'][$this->course_id])){
            echo "<p>The course (id = ".$this->course_id.") cannot be found in the serialized data, can you restart the calculations for this course?</p>";
            return false;
        }
        
        if (!isset($data_unserialized['data'][$this->course_id][$indicator_index])) {
            echo "<p>Can't find the ".$indicator_name." for this course (id = ".$this->course_id."), can you run the calculations again for this course?</p>";
            return false;
        }

        echo "<p>The ".$indicator_name." that was actually calculated for the course n°".$this->course_id.
            " is ".$data_unserialized['data'][$this->course_id][$indicator_index]."</p>";

        return true;
    }

    protected function test_coeffs() {
        echo "<h3>Coefficients checking</h3>";

        switch($this->indicator) {
            case "nu" :
                $coeffs = \report_hybridmeter\classes\configurator::get_instance()->get_coeffs_grid("usage_coeffs");
                break;

            case "nd" :
                $coeffs = \report_hybridmeter\classes\configurator::get_instance()->get_coeffs_grid("digitalisation_coeffs");
                break;

            default :
                throw new Exception(INDICATOR_ERROR);
                break;
        }

        echo "<p>Voici les coefficients</p>";
        echo utils::columns_rows_array_to_html($coeffs);
    }

    protected function test_hybridation_calculus() {
        echo "<h3>Verification of the hybridation calculation function</h3>"; 

        $test_dataset = array(
            "assign" => "4",
            "chat" => "1",
            "forum" => "1",
            "quiz" => "1",
            "survey" => "1",
            "assignment" => "7",
            "book" => "5",
            "choice" => "4",
            "data" => "5",
            "feedback" => "1",
            "folder" => "3",
            "glossary" => "0",
            "h5pactivity" => "2",
            "imscp" => "0",
            "label" => "1",
            "lesson" => "0",
            "lti" => "0",
            "page" => "0",
            "resource" => "0",
            "scorm" => "0",
            "url" => "4",
            "wiki" => "1",
            "workshop" => "1",
        );

        echo "<p>Here are the parameters that will be used for the calculation of the function: </p>";

        echo utils::data_grouped_by_to_html($test_dataset);

        switch($this->indicator) {
            case "nu" :
                $mode = "usage_coeffs";
                break;

            case "nd" :
                $coeffs = "digitalisation_coeffs";
                break;

            default :
                throw new Exception(INDICATOR_ERROR);
                break
        }

        $test = \report_hybridmeter\classes\indicators::hybridation_calculus($mode, $test_dataset);

        echo "<p>The hybridation_calculus function returned the following result: ".$test."</p>";
    }
}
    