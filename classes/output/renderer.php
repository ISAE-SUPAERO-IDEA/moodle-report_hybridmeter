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
 * Render HTML pages of the plugin.
 * @author Nassim Bennouar, Bruno Ilponse
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2020  ISAE-SUPAERO (https://www.isae-supaero.fr/)
 * @package report_hybridmeter
 */

namespace report_hybridmeter\output;

defined('MOODLE_INTERNAL') || die;

require_once(__DIR__.'/../../constants.php');
use DateTime;
use html_writer;
use moodle_url;
use plugin_renderer_base;
use report_hybridmeter\course_indicators;
use report_hybridmeter\utils as utils;

/**
 * Render HTML pages of the plugin.
 */
class renderer extends plugin_renderer_base {

    /**
     * Render the "Next scheduled report" section.
     * @param boolean $isscheduled
     * @param int $timestampscheduled
     * @param boolean $unscheduledaction
     * @return string
     */
    public function next_schedule($isscheduled, $timestampscheduled, $unscheduledaction) {
        $html = "";

        $datescheduled = new DateTime();
        $datescheduled->setTimestamp($timestampscheduled);

        if ($unscheduledaction == 1) {
            $html .= html_writer::start_div('container-fluid');

            $html .= html_writer::span(get_string('successfully_unscheduled', 'report_hybridmeter'));

            $html .= html_writer::end_div();
        }

        $html .= html_writer::start_div('container-fluid');

        if ($isscheduled == 1) {
            $schedulemessage = sprintf(
                get_string('next_schedule', 'report_hybridmeter'),
                $datescheduled->format('d/m/Y'),
                $datescheduled->format('H:i')
            );
            $html .= html_writer::span($schedulemessage, '');
        } else {
            $html .= html_writer::span(get_string('no_schedule', 'report_hybridmeter'));
        }

        $html .= html_writer::end_div();

        $html .= html_writer::start_div(
            'container-fluid',
            [
                "style" => "margin-top : 5px;",
            ]
        );
        $html .= html_writer::start_span('');

        $urlschedule = new moodle_url('/report/hybridmeter/management.php#schedule');
        $urlunschedule = new moodle_url('/report/hybridmeter/index.php?unschedule=1');

        if ($isscheduled == 1) {
            $html .= html_writer::start_span('');

            $html .= html_writer::link(
                $urlschedule,
                get_string('reschedule', 'report_hybridmeter'),
                [
                    'class' => 'row m-1 btn btn-secondary',
                    "style" => 'margin-right : 20px;',
                ]
            );

            $html .= html_writer::end_span('');

            $html .= html_writer::start_span('');

            $html .= html_writer::link(
                $urlunschedule,
                get_string('unschedule', 'report_hybridmeter'),
                [
                    'class' => 'row m-1 btn btn-secondary',
                    "style" => 'margin-right : 10px;',
                ]
            );

            $html .= html_writer::end_span('');
        } else {
            $html .= html_writer::link(
                $urlschedule,
                get_string('schedule', 'report_hybridmeter'),
                [
                    'class' => 'row m-1 btn btn-secondary',
                    "style" => 'margin-right : 0px;',
                ]
            );
        }

        $html .= html_writer::end_span();
        $html .= html_writer::end_div();

        $html .= html_writer::tag("hr", "");

        return $html;
    }

    /**
     * Render the actions "Download report" and "Configure".
     * @param bool $isdataavailable
     * @return string
     */
    public function index_links($isdataavailable) {

        // Download button.
        $html = "";

        $html .= html_writer::start_div('container-fluid');
        $disabled = ($isdataavailable) ? "" : "disabled";
        $url = new moodle_url('/report/hybridmeter/index.php', ["task" => "download"]);
        $html .= html_writer::link(
            $url,
            get_string('download_csv', 'report_hybridmeter'),
            ['class' => 'row m-1 btn btn-primary ' . $disabled],
        );
        $html .= html_writer::end_div();

        // Configuration button.

        $html .= html_writer::start_div('container-fluid');
        $url = new moodle_url('/report/hybridmeter/management.php');
        $html .= html_writer::link($url,
            get_string('blacklistmanagement', 'report_hybridmeter'),
            [
                'class' => 'row m-1 mb-1 btn btn-secondary',
                'style' => 'margin-bottom : 10px; margin-top : 10px',
            ]);
        $html .= html_writer::end_div();

        $html .= html_writer::tag("hr", "");
        $html .= html_writer::span("HybridMeter v." . utils::get_release_from_plugin()) . " :";

        $listitems = "";
        // Documentation link.

        $url = 'https://doc.clickup.com/d/h/2f5v0-8317/29996805f942cfc';
        $listitems .= html_writer::tag("li",
                html_writer::link($url, get_string('documentation', 'report_hybridmeter'), ['target' => 'blank']));

        // Changelog link.
        $url = 'https://github.com/ISAE-SUPAERO-IDEA/moodle-report_hybridmeter/releases';
        $listitems .= html_writer::tag("li",
                html_writer::link($url, get_string('changelog', 'report_hybridmeter'), ['target' => 'blank']));

        $html .= html_writer::tag("ul", $listitems);

        return $html;
    }

    /**
     * Render the "scheduled task" section.
     * @param int $countpending
     * @param int $isrunning
     * @return string
     */
    public function is_task_planned(int $countpending, int $isrunning) {
        $html = html_writer::tag("hr", "");
        $html .= html_writer::start_div('container-fluid');

        $url = new moodle_url('/report/hybridmeter/index.php',
            [
                "task" => "cleartasks",
                "debug" => 1,
            ]
        );

        $html .= html_writer::link(
            $url,
            "Clear tasks",
            [
                'class' => 'row m-1 btn btn-secondary',
                "style" => 'margin-right : 10px;',
            ]
        );

        if ($isrunning != REPORT_HYBRIDMETER_NON_RUNNING) {
            $html .= html_writer::span(get_string('task_running', 'report_hybridmeter'), '');
        } else if ($countpending > 0) {
            $html .= html_writer::span(get_string('task_pending', 'report_hybridmeter'), '');
        } else {
            $html .= html_writer::span(get_string('no_task_pending', 'report_hybridmeter'), '');
        }
        $html .= html_writer::end_div();

        return $html;
    }


    /**
     * Render the "last calculation" section in debug mode.
     * @param boolean $isdataavailable
     * @param int $date
     * @param int $interval
     * @return string
     */
    public function last_calculation($isdataavailable, $date, $interval) {

        $html = html_writer::start_div('container-fluid');

        $url = new moodle_url('/report/hybridmeter/index.php',
            [
                "task" => "calculate",
                "debug" => 1,
            ]);

        $html .= html_writer::link(
            $url,
            get_string('recalculate', 'report_hybridmeter'),
            [
                'class' => 'row m-1 btn btn-secondary',
                "style" => 'margin-right : 10px;',
            ]
        );

        $date = ($isdataavailable && isset($date)) ? $date : REPORT_HYBRIDMETER_NA;
        $content = sprintf(get_string('last_updated', 'report_hybridmeter'), $date, $interval);

        $html .= html_writer::span($content);

        $html .= html_writer::end_div();

        $html .= html_writer::tag("hr", "");

        return $html;
    }

    /**
     * Render the general indicators table.
     * @param boolean $isdataavailable
     * @param array $generaldata
     * @param int $timestampbegin
     * @param int $timestampend
     * @param int $endprocessing
     * @param int $processingduration
     * @return string
     */
    public function general_indicators($isdataavailable,
                                       $generaldata,
                                       $timestampbegin,
                                       $timestampend,
                                       $endprocessing,
                                       $processingduration) {
        $doesdataexist = ($isdataavailable && isset($generaldata[REPORT_HYBRIDMETER_GENERAL_NB_DIGITALISED_COURSES]));
        $nbcourshybridesstatiques = $doesdataexist ?
            $generaldata[REPORT_HYBRIDMETER_GENERAL_NB_DIGITALISED_COURSES] :
            REPORT_HYBRIDMETER_NA;

        $doesdataexist = ($isdataavailable && isset($generaldata[REPORT_HYBRIDMETER_GENERAL_NB_USED_COURSES]));
        $nbcourshybridesdynamiques = $doesdataexist ?
            $generaldata[REPORT_HYBRIDMETER_GENERAL_NB_USED_COURSES] :
            REPORT_HYBRIDMETER_NA;

        $doesdataexist = ($isdataavailable &&
            isset($generaldata[REPORT_HYBRIDMETER_GENERAL_NB_STUDENTS_CONCERNED_DIGITALISED]));

        $nbetudiantsconcernesstatiques = $doesdataexist ?
            $generaldata[REPORT_HYBRIDMETER_GENERAL_NB_STUDENTS_CONCERNED_DIGITALISED] :
            REPORT_HYBRIDMETER_NA;

        $doesdataexist = ($isdataavailable &&
            isset($generaldata[REPORT_HYBRIDMETER_GENERAL_NB_STUDENTS_CONCERNED_DIGITALISED_ACTIVE]));

        $nbetudiantsconcernesstatiquesactifs = $doesdataexist ?
            $generaldata[REPORT_HYBRIDMETER_GENERAL_NB_STUDENTS_CONCERNED_DIGITALISED_ACTIVE] :
            REPORT_HYBRIDMETER_NA;

        $doesdataexist = ($isdataavailable &&
            isset($generaldata[REPORT_HYBRIDMETER_GENERAL_NB_STUDENTS_CONCERNED_USED]));

        $nbetudiantsconcernesdynamiques = $doesdataexist ?
            $generaldata[REPORT_HYBRIDMETER_GENERAL_NB_STUDENTS_CONCERNED_USED] :
            REPORT_HYBRIDMETER_NA;

        $doesdataexist = ($isdataavailable &&
            isset($generaldata[REPORT_HYBRIDMETER_GENERAL_NB_STUDENTS_CONCERNED_USED_ACTIVE]));

        $nbetudiantsconcernesdynamiquesactifs = $doesdataexist ?
            $generaldata[REPORT_HYBRIDMETER_GENERAL_NB_STUDENTS_CONCERNED_USED_ACTIVE] :
            REPORT_HYBRIDMETER_NA;

        $doesdataexist = ($isdataavailable && isset($generaldata[REPORT_HYBRIDMETER_GENERAL_NB_ANALYSED_COURSES]));
        $nbcoursanalyses = $doesdataexist ?
            $generaldata[REPORT_HYBRIDMETER_GENERAL_NB_ANALYSED_COURSES] :
            REPORT_HYBRIDMETER_NA;

        if ($isdataavailable && isset($timestampbegin) && isset($timestampend)) {
            $datetimebegin = new DateTime();
            $datetimeend = new DateTime();

            $datetimebegin->setTimestamp($timestampbegin);
            $datetimeend->setTimestamp($timestampend);

            $format = "d/m/Y";

            $stringmeasurementperiod = sprintf(
                get_string('measurement_period', 'report_hybridmeter'),
                $datetimebegin->format($format),
                $datetimeend->format($format)
            );
        } else {
            $stringmeasurementperiod = REPORT_HYBRIDMETER_NA;
        }

        if ($isdataavailable && isset($endprocessing)) {
            $stringendprocessing = sprintf(
                get_string('end_processing', 'report_hybridmeter'),
                $endprocessing
            );
            $processingdate = date_parse_from_format('d/m/Y à H:i:s', $endprocessing);

            $processingdate = str_pad(
                    $processingdate["day"],
                    2,
                    "0",
                    STR_PAD_LEFT
                ) . "/" .
                str_pad(
                    $processingdate["month"],
                    2,
                    "0",
                    STR_PAD_LEFT
                ) . "/" . $processingdate["year"];
        } else {
            $stringendprocessing = REPORT_HYBRIDMETER_NA;
        }

        if ($isdataavailable && isset($processingduration)) {
            $stringprocessingduration = sprintf(
                get_string('processing_duration', 'report_hybridmeter'),
                $processingduration
            );
        } else {
            $stringprocessingduration = REPORT_HYBRIDMETER_NA;
        }

        $params = [
            "measurement_period" => $stringmeasurementperiod,
            "end_processing" => $stringendprocessing,
            "processing_date" => $processingdate,
            "processing_duration" => $stringprocessingduration,
            "value_digitalised_course" => $nbcourshybridesstatiques,
            "value_used_course" => $nbcourshybridesdynamiques,
            "value_digitalisation_registered_students" => $nbetudiantsconcernesstatiques,
            "value_digitalisation_active_students" => $nbetudiantsconcernesstatiquesactifs,
            "value_usage_registered_students" => $nbetudiantsconcernesdynamiques,
            "value_usage_active_students" => $nbetudiantsconcernesdynamiquesactifs,
            "value_nb_analysed_courses" => $nbcoursanalyses,
        ];

        $html = $this->render_from_template("report_hybridmeter/indicators_table", $params);

        $html .= html_writer::tag("hr", "");

        return $html;
    }

    /**
     * Render the indicators table for one specific course.
     * @param course_indicators $courseindicators
     * @return string
     */
    public function course_indicators(course_indicators $courseindicators)  {
        $datetimebegin = new DateTime();
        $datetimeend = new DateTime();

        $datetimebegin->setTimestamp($courseindicators->begindate);
        $datetimeend->setTimestamp($courseindicators->enddate);

        $format = "d/m/Y";

        $stringmeasurementperiod = sprintf(
            get_string('measurement_period', 'report_hybridmeter'),
            $datetimebegin->format($format),
            $datetimeend->format($format)
        );

        $activities = $courseindicators->countactivitiespertype;
        $hits = $courseindicators->counthitsonactivitiespertype;

        $keys = array_unique(
            array_merge(
                array_keys(
                    array_filter(
                        $activities,
                        function($count) { return $count != 0; }
                    )
                ),
                array_keys($hits)
            )
        );
        sort($keys);

        $activityrows = array_map(
            function ($activity) use ($activities, $hits) {
                return [
                    "activitytype" => $activity,
                    "nb" => $activities[$activity] ?? 0,
                    "hits" => $hits[$activity] ?? 0,
                    ];
            },
            $keys
        );

        $params = [
            "title" => $courseindicators->coursefullname,
            "measurement_period" => $stringmeasurementperiod,
            "value_digitalisation_level" => $courseindicators->digitalisationlevel,
            "value_usage_level" => $courseindicators->usagelevel,
            "value_nb_registered_students" => $courseindicators->nbregisteredstudents,
            "value_nb_active_students" => $courseindicators->nbactivestudents,
            "value_is_course_active_onperiod" => $courseindicators->active,
            "activityrows" => $activityrows,
        ];
        return  $this->render_from_template("report_hybridmeter/course_indicators_table", $params);
    }
}

