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
 * Produce HybridMeter report.
 *
 * @author Nassim Bennouar, Bruno Ilponse
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2020  ISAE-SUPAERO (https://www.isae-supaero.fr/)
 * @package report_hybridmeter
 */
namespace report_hybridmeter;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__.'/../indicators.php');
require_once(__DIR__.'/../constants.php');

use report_hybridmeter\data\general_data as general_data;
use report_hybridmeter\data_provider as data_provider;
use report_hybridmeter\configurator as configurator;
use report_hybridmeter\formatter as formatter;
use report_hybridmeter\logger as logger;
use DateTime;

/**
 * Produce HybridMeter report.
 */
class processing {

    /**
     * Launch the computations to produce HybridMeter report.
     *
     * @return array
     */
    public static function launch() {
        global $CFG;

        logger::log("# Processing: initializing");
        $timestamp = REPORT_HYBRIDMETER_NOW;

        $dataprovider = data_provider::get_instance();
        $configurator = configurator::get_instance();

        $whitelistids = $dataprovider->get_whitelisted_courses_ids();
        logger::log("Whitelisted course ids: ".implode(", ", $whitelistids));

        $filtered = $dataprovider->filter_living_courses_on_period(
            $whitelistids,
            $configurator->get_begin_timestamp(),
            $configurator->get_end_timestamp()
        );
        $filteredids = array_map(
            function ($course) {
                return $course->id;
            },
            $filtered
        );
        logger::log("Active course ids: ".implode(", ", $filteredids));

        $formatter = new formatter($filtered);

        $begin_date = new DateTime();
        $begin_date->setTimestamp($timestamp);

        $end_date = new DateTime();

        logger::log("# Processing: blacklist computation");

        $configurator = configurator::get_instance();
        $configurator->set_as_running($begin_date);
        $configurator->update_blacklisted_data();

        // Calculation of detailed indicators.
        logger::log("# Processing: course indicators computation");

        $formatter->calculate_new_indicator(
            function($object) {
                return $object['id'];
            },
            REPORT_HYBRIDMETER_FIELD_ID_MOODLE
        );

        $formatter->calculate_new_indicator(
            "get_category_path",
            REPORT_HYBRIDMETER_FIELD_CATEGORY_PATH
        );

        $formatter->calculate_new_indicator(
            function($object) {
                return $object['idnumber'];
            },
            REPORT_HYBRIDMETER_FIELD_ID_NUMBER
        );

        $formatter->calculate_new_indicator(
            function($object, $parameters) {
                return $parameters["www_root"]."/course/view.php?id=".$object['id'];
            },
            REPORT_HYBRIDMETER_FIELD_URL,
            [
                "www_root" => $CFG->wwwroot,
            ]
        );

        $formatter->calculate_new_indicator(
            "digitalisation_level",
            REPORT_HYBRIDMETER_FIELD_DIGITALISATION_LEVEL,
            [
                "nb_cours" => $formatter->get_length_array(),
            ]
        );

        $formatter->calculate_new_indicator(
            "usage_level",
            REPORT_HYBRIDMETER_FIELD_USAGE_LEVEL,
            [
                "nb_cours" => $formatter->get_length_array(),
            ]
        );

        $formatter->calculate_new_indicator(
            "is_course_active_last_month",
            REPORT_HYBRIDMETER_FIELD_ACTIVE_COURSE
        );

        $formatter->calculate_new_indicator(
            "active_students",
            REPORT_HYBRIDMETER_FIELD_NB_ACTIVE_USERS
        );

        $formatter->calculate_new_indicator(
            "nb_registered_students",
            REPORT_HYBRIDMETER_FIELD_NB_REGISTERED_STUDENTS
        );

        $begindate = new DateTime();
        $begindate->setTimestamp($configurator->get_begin_timestamp());
        $begindate = $begindate->format('d/m/Y');

        $formatter->calculate_new_indicator(
            function ($object, $parameters) {
                return $parameters['begin_date'];
            },
            REPORT_HYBRIDMETER_FIELD_BEGIN_DATE,
            [
                "begin_date" => $begindate,
            ]
        );

        $enddate = new DateTime();
        $enddate->setTimestamp($configurator->get_end_timestamp());
        $enddate = $enddate->format('d/m/Y');

        $formatter->calculate_new_indicator(
            function ($object, $parameters) {
                return $parameters['end_date'];
            },
            REPORT_HYBRIDMETER_FIELD_END_DATE,
            [
                "end_date" => $enddate,
            ]
        );

        $formatter->calculate_new_indicator(
            'raw_data',
            'raw_data'
        );

        $dataout = $formatter->get_array();

        // Calculation of general indicators.
        logger::log("# Processing: global indicators computation");

        $generaldata = new general_data($dataout);

        // Data exportation.
        logger::log("# Processing: serializing results");

        $end_date->setTimestamp(strtotime("now"));

        $interval = $end_date->getTimestamp() - $begin_date->getTimestamp();

        $time = [
            "begin_timestamp" => $begin_date->getTimestamp(),
            "end_timestamp" => $end_date->getTimestamp(),
            "diff" => $interval,
        ];

        if (!file_exists($CFG->dataroot."/hybridmeter/records")) {
            mkdir($CFG->dataroot."/hybridmeter/records", 0700, true);
        }

        $fileexporter = fopen($CFG->dataroot."/hybridmeter/records/serialized_data", "w");

        $s = serialize([
            "time" => $time,
            "data" => $dataout,
            "generaldata" => $generaldata->toMap(),
        ]);
        fwrite($fileexporter, $s);
        fclose($fileexporter);

        /* We have deactivated CSV logging for RGPD reasons (we need to renegotiate the conditions with the DPO to include them)
         * $formatted_date = $begin_date->format('Y-m-d H:i:s');
         * $filename = $CFG->dataroot."/hybridmeter/records/backup/record_".$formatted_date.".csv";
         * $backup=fopen($filename,"w");
         * fwrite($backup, $exporter->print_csv_data(true));
         */

        // Log and task management.
        $configurator->unset_as_running();
        logger::log("# Processing: done");

        return $dataout;
    }
}
