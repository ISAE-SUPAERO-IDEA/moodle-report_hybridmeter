<?php

namespace report_hybridmeter\classes\tests\NU;

require_once(__DIR__."/../indicateurs_scenario_abstract.php");

use \report_hybridmeter\classes\utils as utils;

class nu_incoherent_scenario extends \report_hybridmeter\classes\tests\indicateurs_scenario_abstract {
    function __construct($course_id) {
        parent::__construct("nu", "NU incohérent", $course_id);
    }

    public function specific_tests() {
        //$this->test_nd();
        $this->test_coeffs();
        $this->test_hybridation_calculus();
        $this->test_timestamps();
        $this->test_student_role();
        $this->test_count_hits_on_activities_per_type();
        $this->dump_hits_on_activities();
    }

    private function test_nd() {
        echo "<h3>vérification de la non-nullité du niveau de digitalisation</h3>";

        if ( $data_unserialized['data'][$this->course_id]['niveau_de_digitalisation'] == 0 ) {
            echo "<h3>Le niveau de digitalisation semble nul, lancement d'un jeu de test</h3>";
            echo "<div class='soustest'>";
            $nd_nul = (new nd_nul_scenario($this->course_id))->test();
            echo "</div>";

            if($nd_nul)
                echo "<p>Le niveau de digitalisation semble réellement nul, il est normal dans ces conditions que le niveau d'utilisation soit lui aussi nul.</p>";
            else
                echo "<p>Le niveau de digitalisation pose lui aussi problème, il est nécessaire d'analyser les résultats des tests.</p>";
        }
    }

    private function test_count_hits_on_activities_per_type() {
        echo "<h3>Vérification des requêtes faites en base de données</h3>";

        $data_provider = \report_hybridmeter\classes\data_provider::getInstance();
        $configurator = \report_hybridmeter\classes\configurator::getInstance();
        $begin_timestamp = $configurator->get_begin_timestamp();
        $end_timestamp = $configurator->get_end_timestamp();
        
        echo "<p>Les timestamp begin et end sont ".$begin_timestamp." et ".$end_timestamp."</p>";

        echo "<p>La fonction count_hits_on_activities_per_type nous retourne :</p>";
        echo utils::data_grouped_by_to_html($data_provider->count_hits_on_activities_per_type(
            $this->course_id, 
		    $begin_timestamp,
		    $end_timestamp
        ));
    }

    private function dump_hits_on_activities() {
        echo "<h3>Dump des actions sur les activités du cours durant la période de capture corante :</h3>";

        global $DB;
        
        $data_provider = \report_hybridmeter\classes\data_provider::getInstance();
        $configurator = \report_hybridmeter\classes\configurator::getInstance();
        $begin_timestamp = $configurator->get_begin_timestamp();
        $end_timestamp = $configurator->get_end_timestamp();

        $params=array($this->course_id, $begin_timestamp, $end_timestamp);

        $records = $DB->get_records_sql(
            "select * from ".$DB->get_prefix()."logstore_standard_log log
            inner join ".$DB->get_prefix()."role_assignments ass on log.userid=ass.userid
            inner join ".$DB->get_prefix()."role role on ass.roleid=role.id
            where log.courseid=?
            and log.target='course_module'
            and log.timecreated between ? and ?
            group by log.objecttable limit 1000",
            $params
        );

        echo "<p>Voici : </p>";

        echo utils::objects_array_to_html($records);
    }
}