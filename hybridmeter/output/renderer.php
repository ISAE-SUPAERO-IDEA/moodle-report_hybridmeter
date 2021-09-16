<?php

namespace report_hybridmeter\output;

defined('MOODLE_INTERNAL') || die;
 
require_once(dirname(__FILE__).'/../constants.php');

use plugin_renderer_base;
use html_writer;
use moodle_url;

class renderer extends plugin_renderer_base {
    public function index_links($data_available) {

        $html ="";

        $html .= html_writer::start_div('container-fluid');
        $disabled = ($data_available) ? "" : "disabled";
        $url = new moodle_url('/report/hybridmeter/index.php', array("task" => "download"));
        $html .= html_writer::link(
            $url,
            get_string('download_csv', 'report_hybridmeter'),
            array('class' => 'row m-1 btn btn-primary '.$disabled)
        );
        $html .= html_writer::end_div();

        //$html .= html_writer::tag("hr","");

        $html .= html_writer::start_div('container-fluid');
        $url = new moodle_url('/report/hybridmeter/management.php');
        $html .= html_writer::link($url,
            get_string('blacklistmanagement', 'report_hybridmeter'),
            array(
                'class' => 'row m-1 mb-1 btn btn-secondary',
                'style' => 'margin-bottom : 10px; margin-top : 10px'
            ));
        $html .= html_writer::end_div();

        //$html .= html_writer::tag("hr","");
        
        $html .= html_writer::start_div('container-fluid');
        $html .= html_writer::end_div();

        //$html .= html_writer::tag("hr","");
        
        return $html;
    }

    public function is_task_planned(int $count_pending, int $is_running){
        $html = html_writer::start_div('container-fluid');
        if($is_running==1){
            $html .= html_writer::span(get_string('task_running', 'report_hybridmeter'), array('class' => 'row m-1 btn btn-secondary'));
        }
        else if($count_pending > 0){
            $html .= html_writer::span(get_string('task_pending', 'report_hybridmeter'), array('class' => 'row m-1 btn btn-secondary'));
        }
        else{
            $html .= html_writer::span(get_string('no_task_pending', 'report_hybridmeter'), array('class' => 'row m-1 btn btn-secondary'));
        }
        $html .= html_writer::end_div();

        return $html;
    }

    public function last_calculation($data_available, $date){
        $html = html_writer::start_div('container-fluid');

        $url = new moodle_url('/report/hybridmeter/index.php', array("task" => "calculate"));

        $html .= html_writer::link($url,
            get_string('recalculate', 'report_hybridmeter'),
            array(
                'class' => 'row m-1 btn btn-secondary',
                "style" => 'margin-right : 10px;'
            )
        );

        $date = ($data_available && isset($date)) ? $date : NA;
        $content = sprintf(get_string('last_updated', 'report_hybridmeter'), $date);

        $html .= html_writer::span($content);

        $html .= html_writer::end_div();

        $html .= html_writer::tag("hr","");

        return $html;
    }

    public function general_indicators($data_available, $generaldata){
        html_writer::tag('h3', 'blabla');
        $html = html_writer::start_tag('ul');

        $nb_cours_hybrides_statiques = ($data_available && isset($generaldata['nb_cours_hybrides_statiques'])) ? $generaldata['nb_cours_hybrides_statiques'] : NA;
        $html .= html_writer::tag('li', get_string('nb_cours_hybrides_statiques', 'report_hybridmeter').$nb_cours_hybrides_statiques);

        $nb_cours_hybrides_dynamiques = ($data_available && isset($generaldata['nb_cours_hybrides_dynamiques'])) ? $generaldata['nb_cours_hybrides_dynamiques'] : NA;
        $html .= html_writer::tag('li', get_string('nb_cours_hybrides_dynamiques', 'report_hybridmeter').$nb_cours_hybrides_dynamiques);

        $nb_etudiants_concernes_statiques = ($data_available && isset($generaldata['nb_etudiants_concernes_statiques'])) ? $generaldata['nb_etudiants_concernes_statiques'] : NA;
        $html .= html_writer::tag('li', get_string('nb_etudiants_concernes_statiques', 'report_hybridmeter').$nb_etudiants_concernes_statiques);

        $nb_etudiants_concernes_statiques_actifs = ($data_available && isset($generaldata['nb_etudiants_concernes_statiques_actifs'])) ? $generaldata['nb_etudiants_concernes_statiques_actifs'] : NA;
        $html .= html_writer::tag('li', get_string('nb_etudiants_concernes_statiques_actifs', 'report_hybridmeter').$nb_etudiants_concernes_statiques_actifs);

        $nb_etudiants_concernes_dynamiques = ($data_available && isset($generaldata['nb_etudiants_concernes_dynamiques'])) ? $generaldata['nb_etudiants_concernes_dynamiques'] : NA;
        $html .= html_writer::tag('li', get_string('nb_etudiants_concernes_dynamiques', 'report_hybridmeter').$nb_etudiants_concernes_dynamiques);

        $nb_etudiants_concernes_dynamiques_actifs = ($data_available && isset($generaldata['nb_etudiants_concernes_dynamiques_actifs'])) ? $generaldata['nb_etudiants_concernes_dynamiques_actifs'] : NA;
        $html .= html_writer::tag('li', get_string('nb_etudiants_concernes_dynamiques_actifs', 'report_hybridmeter').$nb_etudiants_concernes_dynamiques_actifs);

        $html .= html_writer::end_tag('ul');
        $html .= html_writer::tag("hr","");


        return $html;
    }
}
