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

require_once(__DIR__.'/../constants.php');

use DateTime;
use report_hybridmeter\configurator as configurator;
use report_hybridmeter\data_provider as data_provider;
use report_hybridmeter\report_data as general_data;
use report_hybridmeter\logger as logger;

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
        $startcomputationdate = new DateTime();
        $startcomputationdate->setTimestamp(strtotime("now"));

        $dataprovider = data_provider::get_instance();
        $configurator = configurator::get_instance();

        $whitelistids = $dataprovider->get_whitelisted_courses_ids();
        logger::log("Whitelisted course ids: ".implode(", ", $whitelistids));

        $courses = $dataprovider->filter_living_courses_on_period(
            $whitelistids,
            $configurator->get_begin_timestamp(),
            $configurator->get_end_timestamp()
        );
        $courseids = array_map(
            function ($course) {
                return $course->id;
            },
            $courses
        );
        logger::log("Active course ids: ".implode(", ", $courseids));
        logger::log("# Processing: blacklist computation");

        $configurator = configurator::get_instance();
        $configurator->set_as_running($startcomputationdate);
        $configurator->update_blacklisted_data();

        // Calculation of detailed indicators.
        logger::log("# Processing: course indicators computation");

        $categoriespathcache = [];
        $processeddata = array_map(
            function ($course) use ($CFG, &$categoriespathcache) {
                return new course_data($course, $CFG->wwwroot, $categoriespathcache);
            },
            $courses
        );

        $begindate = new DateTime();
        $begindate->setTimestamp($configurator->get_begin_timestamp());
        $begindate = $begindate->format('d/m/Y');

        $enddate = new DateTime();
        $enddate->setTimestamp($configurator->get_end_timestamp());
        $enddate = $enddate->format('d/m/Y');

        foreach ($processeddata as $coursedata) {
            $coursedata->set_begindate($begindate);
            $coursedata->set_enddate($enddate);

        }

        $dataout = array_map(
            function ($coursedata) {
                return $coursedata->to_map();
            },
            $processeddata
        );

        // Calculation of general indicators.
        logger::log("# Processing: global indicators computation");

        $generaldata = new report_data($dataout);

        // Data exportation.
        logger::log("# Processing: serializing results");

        $endcomputationdate = new DateTime();
        $endcomputationdate->setTimestamp(strtotime("now"));

        $interval = $endcomputationdate->getTimestamp() - $startcomputationdate->getTimestamp();

        $time = [
            "begin_timestamp" => $startcomputationdate->getTimestamp(),
            "end_timestamp" => $endcomputationdate->getTimestamp(),
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
