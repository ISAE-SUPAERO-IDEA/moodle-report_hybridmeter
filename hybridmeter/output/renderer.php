<?php

namespace report_hybridmeter\output;

defined('MOODLE_INTERNAL') || die;

require_once(dirname(__FILE__)."/../../../config.php");
require_once(dirname(__FILE__).'/../constants.php');

use plugin_renderer_base;
use html_writer;
use moodle_url;
use DateTime;

class renderer extends plugin_renderer_base {

    public function next_schedule($is_scheduled, $timestamp_scheduled, $unscheduled_action) {
        $html = "";

        $date_scheduled = new DateTime();
        $date_scheduled->setTimestamp($timestamp_scheduled);

        if($unscheduled_action == 1){
            $html .= html_writer::start_div('container-fluid');
            
            $html .= html_writer::span(get_string('successfully_unscheduled', 'report_hybridmeter'));

            $html .= html_writer::end_div();
        }

        $html .= html_writer::start_div('container-fluid');

        if($is_scheduled == 1){
            $schedule_message = sprintf(
                get_string('next_schedule', 'report_hybridmeter'),
                $date_scheduled->format('d/m/Y'),
                $date_scheduled->format('H:i')
            );
            $html .= html_writer::span($schedule_message, '');
        }
        else{
            $html .= html_writer::span(get_string('no_schedule', 'report_hybridmeter'));
        }

        $html .= html_writer::end_div();

        $html .= html_writer::start_div(
            'container-fluid',
            array(
                "style" => "margin-top : 5px;",
            )
        );
        $html .= html_writer::start_span('');

        $url_schedule = new moodle_url('/report/hybridmeter/management.php#schedule');
        $url_unschedule = new moodle_url('/report/hybridmeter/index.php?unschedule=1');

        if($is_scheduled == 1){
            $html .= html_writer::start_span('');

            $html .= html_writer::link(
                $url_schedule,
                get_string('reschedule', 'report_hybridmeter'),
                array(
                    'class' => 'row m-1 btn btn-secondary',
                    "style" => 'margin-right : 20px;',
                )
            );

            $html .= html_writer::end_span('');

            $html .= html_writer::start_span('');

            $html .= html_writer::link(
                $url_unschedule,
                get_string('unschedule', 'report_hybridmeter'),
                array(
                    'class' => 'row m-1 btn btn-secondary',
                    "style" => 'margin-right : 10px;',
                )
            );

            $html .= html_writer::end_span('');
        }
        else{
            $html .= html_writer::link(
                $url_schedule,
                get_string('schedule', 'report_hybridmeter'),
                array(
                    'class' => 'row m-1 btn btn-secondary',
                    "style" => 'margin-right : 0px;',
                )
            );
        }

        $html .= html_writer::end_span();
        $html .= html_writer::end_div();

        $html .= html_writer::tag("hr","");

        return $html;
    }

    public function index_links($is_data_available) {

        // Download button
        $html ="";

        $html .= html_writer::start_div('container-fluid');
        $disabled = ($is_data_available) ? "" : "disabled";
        $url = new moodle_url('/report/hybridmeter/index.php', array("task" => "download",));
        $html .= html_writer::link(
            $url,
            get_string('download_csv', 'report_hybridmeter'),
            array('class' => 'row m-1 btn btn-primary '.$disabled),
        );
        $html .= html_writer::end_div();

        // Configuration button

        $html .= html_writer::start_div('container-fluid');
        $url = new moodle_url('/report/hybridmeter/management.php');
        $html .= html_writer::link($url,
            get_string('blacklistmanagement', 'report_hybridmeter'),
            array(
                'class' => 'row m-1 mb-1 btn btn-secondary',
                'style' => 'margin-bottom : 10px; margin-top : 10px',
            ));
        $html .= html_writer::end_div();


        // Documentation link
        $html .= html_writer::tag("hr","");

        $url = 'https://doc.clickup.com/d/h/2f5v0-8317/29996805f942cfc';
        $html .= html_writer::link($url, get_string('documentation', 'report_hybridmeter'),
            array('target' => 'blank',));

        $html .= html_writer::tag("br","");
            
        // Changelog link
        $url = 'https://doc.clickup.com/d/h/2f5v0-8568/7b507d8c7c54778';
        $html .= html_writer::link($url, get_string('changelog', 'report_hybridmeter'),
            array('target' => 'blank',));
            

        
        return $html;
    }

    public function is_task_planned(int $count_pending, int $is_running){
        $html = html_writer::tag("hr","");
        $html .= html_writer::start_div('container-fluid');

        $url = new moodle_url('/report/hybridmeter/index.php',
            array(
                "task" => "cleartasks",
                "debug" => 1,
            )
        );

        $html .= html_writer::link($url,
            "Clear tasks",
            array(
                'class' => 'row m-1 btn btn-secondary',
                "style" => 'margin-right : 10px;',
            )
        );

        if($is_running != REPORT_HYBRIDMETER_NON_RUNNING){
            $html .= html_writer::span(get_string('task_running', 'report_hybridmeter'), '');
        }
        else if($count_pending > 0){
            $html .= html_writer::span(get_string('task_pending', 'report_hybridmeter'), '');
        }
        else{
            $html .= html_writer::span(get_string('no_task_pending', 'report_hybridmeter'), '');
        }
        $html .= html_writer::end_div();

        return $html;
    }

    public function last_calculation($is_data_available, $date, $interval){

        $html = html_writer::start_div('container-fluid');

        $url = new moodle_url('/report/hybridmeter/index.php',
            array(
                "task" => "calculate",
                "debug" => 1,
            ));

        $html .= html_writer::link($url,
            get_string('recalculate', 'report_hybridmeter'),
            array(
                'class' => 'row m-1 btn btn-secondary',
                "style" => 'margin-right : 10px;',
            )
        );

        $date = ($is_data_available && isset($date)) ? $date : REPORT_HYBRIDMETER_NA;
        $content = sprintf(get_string('last_updated', 'report_hybridmeter'), $date, $interval);

        $html .= html_writer::span($content);

        $html .= html_writer::end_div();

        $html .= html_writer::tag("hr","");

        return $html;
    }

    public function general_indicators($is_data_available, $generaldata, $timestamp_begin, $timestamp_end, $end_processing, $processing_duration){
        global $OUTPUT;

        $does_data_exist = ($is_data_available && isset($generaldata[REPORT_HYBRIDMETER_GENERAL_NB_DIGITALISED_COURSES]));
        $nb_cours_hybrides_statiques = $does_data_exist ? $generaldata[REPORT_HYBRIDMETER_GENERAL_NB_DIGITALISED_COURSES] : REPORT_HYBRIDMETER_NA;

        $does_data_exist = ($is_data_available && isset($generaldata[REPORT_HYBRIDMETER_GENERAL_NB_USED_COURSES]));
        $nb_cours_hybrides_dynamiques = $does_data_exist ? $generaldata[REPORT_HYBRIDMETER_GENERAL_NB_USED_COURSES] : REPORT_HYBRIDMETER_NA;

        $does_data_exist = ($is_data_available && isset($generaldata[REPORT_HYBRIDMETER_GENERAL_NB_STUDENTS_CONCERNED_DIGITALISED]));
        $nb_etudiants_concernes_statiques = $does_data_exist ? $generaldata[REPORT_HYBRIDMETER_GENERAL_NB_STUDENTS_CONCERNED_DIGITALISED] : REPORT_HYBRIDMETER_NA;

        $does_data_exist = ($is_data_available && isset($generaldata[REPORT_HYBRIDMETER_GENERAL_NB_STUDENTS_CONCERNED_DIGITALISED_ACTIVE]));
        $nb_etudiants_concernes_statiques_actifs = $does_data_exist ? $generaldata[REPORT_HYBRIDMETER_GENERAL_NB_STUDENTS_CONCERNED_DIGITALISED_ACTIVE] : REPORT_HYBRIDMETER_NA;

        $does_data_exist = ($is_data_available && isset($generaldata[REPORT_HYBRIDMETER_GENERAL_NB_STUDENTS_CONCERNED_USED]));
        $nb_etudiants_concernes_dynamiques = $does_data_exist ? $generaldata[REPORT_HYBRIDMETER_GENERAL_NB_STUDENTS_CONCERNED_USED] : REPORT_HYBRIDMETER_NA;

        $does_data_exist = ($is_data_available && isset($generaldata[REPORT_HYBRIDMETER_GENERAL_NB_STUDENTS_CONCERNED_USED_ACTIVE]));
        $nb_etudiants_concernes_dynamiques_actifs = $does_data_exist ? $generaldata[REPORT_HYBRIDMETER_GENERAL_NB_STUDENTS_CONCERNED_USED_ACTIVE] : REPORT_HYBRIDMETER_NA;

        $does_data_exist = ($is_data_available && isset($generaldata[REPORT_HYBRIDMETER_GENERAL_NB_ANALYSED_COURSES]));
        $nb_cours_analyses = $does_data_exist ? $generaldata[REPORT_HYBRIDMETER_GENERAL_NB_ANALYSED_COURSES] : REPORT_HYBRIDMETER_NA;
        
        if($is_data_available && isset($timestamp_begin) && isset($timestamp_end)) {
            $datetime_begin = new DateTime();
            $datetime_end = new DateTime();

            $datetime_begin->setTimestamp($timestamp_begin);
            $datetime_end->setTimestamp($timestamp_end);

            $format = "d/m/Y";

            $string_measurement_period = sprintf(
                get_string('measurement_period','report_hybridmeter'),
                $datetime_begin->format($format),
                $datetime_end->format($format)
            );
        }
        else{
            $string_measurement_period = REPORT_HYBRIDMETER_NA;
        }

        if($is_data_available && isset($end_processing)){
            $string_end_processing = sprintf(
                get_string('end_processing','report_hybridmeter'),
                $end_processing
            );
        }
        else {
            $string_end_processing = REPORT_HYBRIDMETER_NA;
        }

        if($is_data_available && isset($processing_duration)){
            $string_processing_duration = sprintf(
                get_string('processing_duration','report_hybridmeter'),
                $processing_duration
            );
        }
        else {
            $string_processing_duration = REPORT_HYBRIDMETER_NA;
        }

        $params=array(
            "title" => get_string('last_processing_results', 'report_hybridmeter'),
            "measurement_period" => $string_measurement_period,
            "end_processing" => $string_end_processing,
            "processing_duration" => $string_processing_duration,
            "name_columnname" => get_string('indicator_name', 'report_hybridmeter'),
            "value_columnname" => get_string('number', 'report_hybridmeter'),
            "name_digitalised_course" => get_string(REPORT_HYBRIDMETER_GENERAL_NB_DIGITALISED_COURSES, 'report_hybridmeter'),
            "value_digitalised_course" => $nb_cours_hybrides_statiques,
            "name_used_course" => get_string(REPORT_HYBRIDMETER_GENERAL_NB_USED_COURSES, 'report_hybridmeter'),
            "value_used_course" => $nb_cours_hybrides_dynamiques,
            "name_digitalisation_registered_students" => get_string(REPORT_HYBRIDMETER_GENERAL_NB_STUDENTS_CONCERNED_DIGITALISED, 'report_hybridmeter'),
            "value_digitalisation_registered_students" => $nb_etudiants_concernes_statiques,
            "name_digitalisation_active_students" => get_string(REPORT_HYBRIDMETER_GENERAL_NB_STUDENTS_CONCERNED_DIGITALISED_ACTIVE, 'report_hybridmeter'),
            "value_digitalisation_active_students" => $nb_etudiants_concernes_statiques_actifs,
            "name_usage_registered_students" => get_string(REPORT_HYBRIDMETER_GENERAL_NB_STUDENTS_CONCERNED_USED, 'report_hybridmeter'),
            "value_usage_registered_students" => $nb_etudiants_concernes_dynamiques,
            "name_usage_active_students" => get_string(REPORT_HYBRIDMETER_GENERAL_NB_STUDENTS_CONCERNED_USED_ACTIVE, 'report_hybridmeter'),
            "value_usage_active_students" => $nb_etudiants_concernes_dynamiques_actifs,
            "name_nb_analysed_courses" => get_string(REPORT_HYBRIDMETER_GENERAL_NB_ANALYSED_COURSES, 'report_hybridmeter'),
            "value_nb_analysed_courses" => $nb_cours_analyses,
        );

        $html = $OUTPUT->render_from_template("report_hybridmeter/indicators_table", $params);

        $html .= html_writer::tag("hr","");

        return $html;
    }
}
