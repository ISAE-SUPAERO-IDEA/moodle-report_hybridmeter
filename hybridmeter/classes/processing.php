<?php
// This file is part of Moodle - http://moodle.org
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @author Nassim Bennouar, Bruno Ilponse
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2020  ISAE-SUPAERO (https://www.isae-supaero.fr/)
 */
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
require_once(__DIR__."/data/general_data.php");

use \report_hybridmeter\classes\data\general_data as general_data;
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
        logger::log("# Processing: initializing");
        $timestamp = REPORT_HYBRIDMETER_NOW;

        $data_provider = data_provider::get_instance();
        $configurator = configurator::get_instance();

        $whitelist_ids = $data_provider->get_whitelisted_courses_ids();
        logger::log("Whitelisted course ids: ".implode(", ", $whitelist_ids));

        $filtered = $data_provider->filter_living_courses_on_period($whitelist_ids, $configurator->get_begin_timestamp(), $configurator->get_end_timestamp());
        $filtered_ids = array_map(function ($a) { return $a->id; }, $filtered);
        logger::log("Active course ids: ".implode(", ", $filtered_ids));

        $this->formatter=new formatter($filtered);
        
        $this->begin_date = new DateTime();
        $this->begin_date->setTimestamp($timestamp);

        $this->end_date = new DateTime();
    }

    function launch() {
        global $CFG;
        global $SITE;
        logger::log("# Processing: blacklist computation");

        $configurator = configurator::get_instance();
        $data_provider = data_provider::get_instance();

        $configurator->set_as_running($this->begin_date);
        
        $configurator->update_blacklisted_data();

        // Calculation of detailed indicators

        logger::log("# Processing: course indicators computation");

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
        logger::log("# Processing: global indicators computation");

        $general_data = new general_data($data_out,  $configurator);

        // Data exportation
        logger::log("# Processing: serializing results");
        
        $this->end_date->setTimestamp(strtotime("now"));

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
            "generaldata" => $general_data->toMap(),
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
        logger::log("# Processing: done");

        return $data_out;
    }
}
