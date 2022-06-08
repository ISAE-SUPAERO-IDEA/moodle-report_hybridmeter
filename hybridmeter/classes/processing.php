<?php

namespace report_hybridmeter\classes;

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/../../../config.php');
require_once(__DIR__.'/../indicators.php');
require_once(__DIR__.'/../constants.php');
require_once(__DIR__."/configurator.php");
require_once(__DIR__."/data_provider.php");
require_once(__DIR__."/exporter.php");
require_once(__DIR__."/formatter.php");
require_once(__DIR__."/logger.php");

use \report_hybridmeter\classes\data_provider as data_provider;
use \report_hybridmeter\classes\configurator as configurator;
use \report_hybridmeter\classes\exporter as exporter;
use \report_hybridmeter\classes\formatter as formatter;
use \report_hybridmeter\classes\logger as logger;
use DateTime;

class processing {

    protected $formatter;
    protected $exporter;
    protected $begin_date;
    protected $end_date;

    function __construct(){
        $timestamp = strtotime('REPORT_HYBRIDMETER_NOW');

        $data_provider = data_provider::get_instance();
        $configurator = configurator::get_instance();

        $whitelist_ids = $data_provider->get_whitelisted_courses_ids();

        $filtered = $data_provider->filter_living_courses_on_period($whitelist_ids, $configurator->get_begin_timestamp(), $configurator->get_end_timestamp());

        $this->formatter=new formatter($filtered);
        
        $this->begin_date = new DateTime();
        $this->begin_date->setTimestamp($timestamp);

        $this->end_date = new DateTime();
    }

    function launch() {
        global $CFG;
        global $SITE;

        $configurator = configurator::get_instance();
        $data_provider = data_provider::get_instance();

        $configurator->set_as_running($this->begin_date);

        // Calculation of detailed indicators

        $this->formatter->calculate_new_indicator(
            function($object, $parameters){
                return $object['id'];
            },
            REPORT_HYBRIDMETER_FIELD_ID_MOODLE
        );

        $this->formatter->calculate_new_indicator(
            "get_category_path",
            REPORT_HYBRIDMETER_FIELD_CATEGORY_PATH
        );


        $this->formatter->calculate_new_indicator(
            function($object, $parameters){
                return $object['idnumber'];
            },
            REPORT_HYBRIDMETER_FIELD_ID_NUMBER
        );


        $this->formatter->calculate_new_indicator(
            function($object, $parameters){
                return $parameters["www_root"]."/course/view.php?id=".$object['id'];
            },
            REPORT_HYBRIDMETER_FIELD_URL,
            array(
                "www_root" => $CFG->wwwroot,
            )
        );

        $this->formatter->calculate_new_indicator(
            "digitalisation_level",
            REPORT_HYBRIDMETER_FIELD_DIGITALISATION_LEVEL,
            array(
                "nb_cours" => $this->formatter->get_length_array(),
            )
        );

        $this->formatter->calculate_new_indicator(
            "usage_level",
            REPORT_HYBRIDMETER_FIELD_USAGE_LEVEL,
            array(
                "nb_cours" => $this->formatter->get_length_array(),
            )
        );


        $this->formatter->calculate_new_indicator(
            "is_course_active_last_month",
            REPORT_HYBRIDMETER_FIELD_ACTIVE_COURSE
        );

        $this->formatter->calculate_new_indicator(
            "active_students",
            REPORT_HYBRIDMETER_FIELD_NB_ACTIVE_USERS
        );

        $this->formatter->calculate_new_indicator(
            "nb_registered_students",
            REPORT_HYBRIDMETER_FIELD_NB_REGISTERED_STUDENTS
        );

        $begin_date = new DateTime();
        $begin_date->setTimestamp($configurator->get_begin_timestamp());
        $begin_date = $begin_date->format('d/m/Y');


        $this->formatter->calculate_new_indicator(
            function ($object, $parameters) {
                return $parameters['begin_date'];
            },
            REPORT_HYBRIDMETER_FIELD_BEGIN_DATE,
            array(
                "begin_date" => $begin_date,
            )
        );

        $end_date = new DateTime();
        $end_date->setTimestamp($configurator->get_end_timestamp());
        $end_date = $end_date->format('d/m/Y');

        $this->formatter->calculate_new_indicator(
            function ($object, $parameters) {
                return $parameters['end_date'];
            },
            REPORT_HYBRIDMETER_FIELD_END_DATE,
            array(
                "end_date" => $end_date,
            )
        );

        $this->formatter->calculate_new_indicator(
            'raw_data',
            'raw_data'
        );

        $data_out = $this->formatter->get_array();

        // Calculation of general indicators

        $generaldata=array();

        $generaldata[REPORT_HYBRIDMETER_GENERAL_DIGITALISED_COURSES] = array_values(
            array_filter($data_out,
                function($cours){
                    return $cours[REPORT_HYBRIDMETER_FIELD_DIGITALISATION_LEVEL] >= configurator::get_instance()->get_data()["digitalisation_treshold"];
                }
            )
        );

        $generaldata[REPORT_HYBRIDMETER_GENERAL_USED_COURSES]=array_values(
            array_filter($data_out,
                function($cours){
                    return $cours[REPORT_HYBRIDMETER_FIELD_USAGE_LEVEL] >= configurator::get_instance()->get_data()["usage_treshold"];
                }
            )
        );

        $generaldata[REPORT_HYBRIDMETER_GENERAL_IDS_DIGITALISED_COURSES]=array_map(function($cours){
                return intval($cours["id"]);
            }
        , $generaldata[REPORT_HYBRIDMETER_GENERAL_DIGITALISED_COURSES]);

        $generaldata[REPORT_HYBRIDMETER_GENERAL_IDS_DIGITALISED_COURSES]=array_map(function($cours){
                return intval($cours["id"]);
            }
        , $generaldata[REPORT_HYBRIDMETER_GENERAL_USED_COURSES]);

        $generaldata[REPORT_HYBRIDMETER_GENERAL_NB_DIGITALISED_COURSES]=count($generaldata[REPORT_HYBRIDMETER_GENERAL_DIGITALISED_COURSES]);
        $generaldata[REPORT_HYBRIDMETER_GENERAL_NB_USED_COURSES]=count($generaldata[REPORT_HYBRIDMETER_GENERAL_USED_COURSES]);

        $generaldata[REPORT_HYBRIDMETER_GENERAL_NB_STUDENTS_CONCERNED_DIGITALISED] = $data_provider->count_distinct_registered_students_of_courses(
            $generaldata[REPORT_HYBRIDMETER_GENERAL_IDS_DIGITALISED_COURSES]
        );

        $generaldata[REPORT_HYBRIDMETER_GENERAL_NB_STUDENTS_CONCERNED_DIGITALISED_ACTIVE]=$data_provider->count_student_single_visitors_on_courses(
            $generaldata[REPORT_HYBRIDMETER_GENERAL_IDS_DIGITALISED_COURSES],
            $configurator->get_begin_timestamp(),
            $configurator->get_end_timestamp()
        );
        
        

        $generaldata[REPORT_HYBRIDMETER_GENERAL_NB_STUDENTS_CONCERNED_USED]=$data_provider->count_distinct_registered_students_of_courses(
            $generaldata[REPORT_HYBRIDMETER_GENERAL_IDS_DIGITALISED_COURSES]
        );
        
        $generaldata[REPORT_HYBRIDMETER_GENERAL_NB_STUDENTS_CONCERNED_USED_ACTIVE]=$data_provider->count_student_single_visitors_on_courses(
            $generaldata[REPORT_HYBRIDMETER_GENERAL_IDS_DIGITALISED_COURSES],
            $configurator->get_begin_timestamp(),
            $configurator->get_end_timestamp()
        );

        $generaldata[REPORT_HYBRIDMETER_GENERAL_BEGIN_CAPTURE_TIMESTAMP] = $configurator->get_begin_timestamp();
        $generaldata[REPORT_HYBRIDMETER_GENERAL_END_CAPTURE_DATE] = $configurator->get_end_timestamp();

        $generaldata[REPORT_HYBRIDMETER_GENERAL_NB_ANALYSED_COURSES] = $this->formatter->get_length_array();

        // Data exportation
        
        $this->end_date->setTimestamp(strtotime('REPORT_HYBRIDMETER_NOW'));

        $interval = $this->end_date->getTimestamp()-$this->begin_date->getTimestamp();

        $time = array(
            "begin_timestamp" => $this->begin_date->getTimestamp(),
            "end_timestamp" => $this->end_date->getTimestamp(),
            "diff" => $interval,
        );

        if (!file_exists($CFG->dataroot."/hybridmeter/records")) {
            mkdir($CFG->dataroot."/hybridmeter/records", 0700, true);
        }

        $file_exporter = fopen($CFG->dataroot."/hybridmeter/records/serialized_data","w");
        $s = serialize(array(
            "time" => $time,
            "data" => $data_out,
            "generaldata" => $generaldata,
        ));
        fwrite($file_exporter, $s);
        fclose($file_exporter);

        /* We have deactivated CSV logging for RGPD reasons (we need to renegotiate the conditions with the DPO to include them)
         * $formatted_date = $this->begin_date->format('Y-m-d H:i:s');
         * $filename = $CFG->dataroot."/hybridmeter/records/backup/record_".$formatted_date.".csv";
         * $backup=fopen($filename,"w");
         * fwrite($backup, $this->exporter->print_csv_data(true));
         */

        // Log and task management

        $configurator->unset_as_running();

        return $data_out;
    }
}
