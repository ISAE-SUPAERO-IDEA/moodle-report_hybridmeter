<?php

namespace report_hybridmetrics\output;

defined('MOODLE_INTERNAL') || die;
 
require_once(dirname(__FILE__).'/../constants.php');

use plugin_renderer_base;
use html_writer;
use moodle_url;

class renderer extends plugin_renderer_base {
    public function index_links() {

        $html ="";

       $html .= html_writer::start_div('container-fluid');
        $url = new moodle_url('/report/hybridmetrics/index.php', array("task" => "download"));
        $html .= html_writer::link($url, get_string('download_csv', 'report_hybridmetrics'), array('class' => 'row m-1 btn btn-secondary'));
        $html .= html_writer::end_div();

        $html .= html_writer::tag("hr","");

        $html .= html_writer::start_div('container-fluid');
        $url = new moodle_url('/report/hybridmetrics/management.php');
        $html .= html_writer::link($url, get_string('blacklistmanagement', 'report_hybridmetrics'), array('class' => 'row m-1 mb-1'));
        $html .= html_writer::end_div();

        $html .= html_writer::tag("hr","");
        
        $html .= html_writer::start_div('container-fluid');
        $html .= html_writer::end_div();

        
        return $html;
    }

    public function is_task_planned(int $count_pending, int $is_running){
        $html = html_writer::start_div('container-fluid');
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
        $html = html_writer::start_div('container-fluid');

        $content = sprintf(get_string('last_updated', 'report_hybridmetrics'), $date);

        $html .= html_writer::span($content);
        $url = new moodle_url('/report/hybridmetrics/index.php', array("task" => "calculate"));
        $html .= html_writer::link($url, get_string('recalculate', 'report_hybridmetrics'), array('class' => 'row m-1 btn btn-secondary'));

        $html .= html_writer::end_div();

        $html .= html_writer::tag("hr","");

        return $html;
    }

    public function general_indicators($data_available, $generaldata){
        html_writer::tag('h3', 'blabla');
        $html = html_writer::start_tag('ul');
        $nb_cours_hybrides_statiques = ($data_available) ? $generaldata['nb_cours_hybrides_statiques'] : NA;
        $html .= html_writer::tag('li', get_string('nb_cours_hybrides_statiques', 'report_hybridmetrics').$nb_cours_hybrides_statiques);
        $nb_cours_hybrides_dynamiques = ($data_available) ? $generaldata['nb_cours_hybrides_dynamiques'] : NA;
        $html .= html_writer::tag('li', get_string('nb_cours_hybrides_dynamiques', 'report_hybridmetrics').$nb_cours_hybrides_dynamiques);
        $html .= html_writer::end_tag('ul');
        $html .= html_writer::tag("hr","");


        return $html;
    }
}
