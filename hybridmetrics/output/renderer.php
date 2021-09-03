<?php

namespace report_hybridmetrics\output;

defined('MOODLE_INTERNAL') || die;
 
use plugin_renderer_base;
use html_writer;
use moodle_url;

class renderer extends plugin_renderer_base {
    public function index_links() {
        $html = html_writer::start_div(array('class' => 'container-fluid'));
        $url = new moodle_url('/report/hybridmetrics/management.php');
        $html .= html_writer::link($url, get_string('blacklistmanagement', 'report_hybridmetrics'), array('class' => 'row m-1 mb-1'));
        $html .= html_writer::end_div();

        $html .= html_writer::start_div(array('class' => 'container-fluid'));
        $url = new moodle_url('/report/hybridmetrics/management.php#date');
        $html .= html_writer::link($url, get_string('periodmanagement', 'report_hybridmetrics'), array('class' => 'row m-1 mt-1'));
        $html .= html_writer::end_div();

        $html .= html_writer::start_div(array('class' => 'container-fluid'));
        $url = new moodle_url('/report/hybridmetrics/index.php', array("download" => "go"));
        $html .= html_writer::link($url, get_string('download_csv', 'report_hybridmetrics'), array('class' => 'row m-1 btn btn-secondary'));
        $html .= html_writer::end_div();

        $html .= html_writer::start_div(array('class' => 'container-fluid'));
        $url = new moodle_url('/report/hybridmetrics/index.php', array("calculate" => "go"));
        $html .= html_writer::link($url, get_string('recalculate', 'report_hybridmetrics'), array('class' => 'row m-1 btn btn-secondary'));
        $html .= html_writer::end_div();
        
        return $html;
    }

    public function is_task_planned(int $count_pending, int $is_running){
        $html = html_writer::start_div(array('class' => 'container-fluid'));
        if($is_running==1){
            $html .= html_writer::span(get_string('task_running', 'report_hybridmetrics'), array('class' => 'row m-1 btn btn-secondary'));
        }
        else if($count_pending > 0){
            $html .= html_writer::span(get_string('task_pending', 'report_hybridmetrics'), array('class' => 'row m-1 btn btn-secondary'));
        }
        else{
            $html .= html_writer::span(get_string('no_task_pending', 'report_hybridmetrics'), array('class' => 'row m-1 btn btn-secondary'));
        }
        $html .= html_writer::end_div();

        return $html;
    }

    public function last_calculation($date){
        $html = html_writer::start_div(array('class' => 'container-fluid'));

        $content = sprintf(get_string('last_updated', 'report_hybridmetrics'), $date);

        $html .= html_writer::span($content, array('class' => 'row m-1 btn btn-secondary'));

        $html .= html_writer::end_div();

        return $html;
    }
}