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
        $html = html_writer::tag("hr","");
        $html .= html_writer::start_div('container-fluid');

        $url = new moodle_url('/report/hybridmeter/index.php',
            array(
                "task" => "cleartasks",
                "debug" => 1
            )
        );

        $html .= html_writer::link($url,
            "Clear tasks",
            array(
                'class' => 'row m-1 btn btn-secondary',
                "style" => 'margin-right : 10px;'
            )
        );

        if($is_running != NON_RUNNING){
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

    public function last_calculation($data_available, $date, $interval){

        $html = html_writer::start_div('container-fluid');

        $url = new moodle_url('/report/hybridmeter/index.php',
            array(
                "task" => "calculate",
                "debug" => 1
            ));

        $html .= html_writer::link($url,
            get_string('recalculate', 'report_hybridmeter'),
            array(
                'class' => 'row m-1 btn btn-secondary',
                "style" => 'margin-right : 10px;'
            )
        );

        $date = ($data_available && isset($date)) ? $date : NA;
        $content = sprintf(get_string('last_updated', 'report_hybridmeter'), $date, $interval);

        $html .= html_writer::span($content);

        $html .= html_writer::end_div();

        $html .= html_writer::tag("hr","");

        return $html;
    }

    public function general_indicators($data_available, $generaldata, $timestamp_begin, $timestamp_end, $fin_traitement, $duree_traitement){
        global $OUTPUT;

        $nb_cours_hybrides_statiques = ($data_available && isset($generaldata['nb_cours_hybrides_statiques'])) ? $generaldata['nb_cours_hybrides_statiques'] : NA;

        $nb_cours_hybrides_dynamiques = ($data_available && isset($generaldata['nb_cours_hybrides_dynamiques'])) ? $generaldata['nb_cours_hybrides_dynamiques'] : NA;

        $nb_etudiants_concernes_statiques = ($data_available && isset($generaldata['nb_etudiants_concernes_statiques'])) ? $generaldata['nb_etudiants_concernes_statiques'] : NA;

        $nb_etudiants_concernes_statiques_actifs = ($data_available && isset($generaldata['nb_etudiants_concernes_statiques_actifs'])) ? $generaldata['nb_etudiants_concernes_statiques_actifs'] : NA;

        $nb_etudiants_concernes_dynamiques = ($data_available && isset($generaldata['nb_etudiants_concernes_dynamiques'])) ? $generaldata['nb_etudiants_concernes_dynamiques'] : NA;

        $nb_etudiants_concernes_dynamiques_actifs = ($data_available && isset($generaldata['nb_etudiants_concernes_dynamiques_actifs'])) ? $generaldata['nb_etudiants_concernes_dynamiques_actifs'] : NA;

        
        if($data_available && isset($timestamp_begin) && isset($timestamp_end)) {
            $datetime_begin = new \DateTime();
            $datetime_end = new \DateTime();

            $datetime_begin->setTimestamp($timestamp_begin);
            $datetime_end->setTimestamp($timestamp_end);

            $format = "d/m/Y";

            $string_periode_mesure = sprintf(
                get_string('periode_mesure','report_hybridmeter'),
                $datetime_begin->format($format),
                $datetime_end->format($format)
            );
        }
        else{
            $string_periode_mesure = NA;
        }

        if($data_available && isset($fin_traitement)){
            $string_fin_traitement = sprintf(
                get_string('fin_traitement','report_hybridmeter'),
                $fin_traitement
            );
        }
        else {
            $string_fin_traitement = NA;
        }

        if($data_available && isset($duree_traitement)){
            $string_duree_traitement = sprintf(
                get_string('duree_traitement','report_hybridmeter'),
                $duree_traitement
            );
        }
        else {
            $string_duree_traitement = NA;
        }

        $params=array(
            "title" => "Résultats du dernier traitement",
            "periode_mesure" => $string_periode_mesure,
            "fin_traitement" => $string_fin_traitement,
            "duree_traitement" => $string_duree_traitement,
            "namecolumnname" => "Nom de l'indicateur",
            "valuecolumnname" => "Nombre",
            "namecoursstatique" => get_string('nb_cours_hybrides_statiques', 'report_hybridmeter'),
            "valuecoursstatique" => $nb_cours_hybrides_statiques,
            "namecoursdynamique" => get_string('nb_cours_hybrides_dynamiques', 'report_hybridmeter'),
            "valuecoursdynamique" => $nb_cours_hybrides_dynamiques,
            "nameetudiantsinscritsstatiques" => get_string('nb_etudiants_concernes_statiques', 'report_hybridmeter'),
            "valueetudiantsinscritsstatiques" => $nb_etudiants_concernes_statiques,
            "nameetudiantsactifsstatiques" => get_string('nb_etudiants_concernes_statiques_actifs', 'report_hybridmeter'),
            "valueetudiantsactifsstatiques" => $nb_etudiants_concernes_statiques_actifs,
            "nameetudiantsinscritsdynamiques" => get_string('nb_etudiants_concernes_dynamiques', 'report_hybridmeter'),
            "valueetudiantsinscritsdynamiques" => $nb_etudiants_concernes_dynamiques,
            "nameetudiantsactifsdynamiques" => get_string('nb_etudiants_concernes_dynamiques_actifs', 'report_hybridmeter'),
            "valueetudiantsactifsdynamiques" => $nb_etudiants_concernes_dynamiques_actifs
        );

        $html = $OUTPUT->render_from_template("report_hybridmeter/tableau_indicateurs", $params);

        $html .= html_writer::tag("hr","");

        return $html;
    }
}
