<?php

namespace report_hybridmeter\classes\tests;

require_once(__DIR__."/../test_scenario_course.php");

use \report_hybridmeter\classes\utils as utils;

abstract class indicateurs_scenario_abstract extends \report_hybridmeter\classes\test_scenario_course {

    protected $indicateur;

    public function __construct($indicateur, $name, $course_id) {
        parent::__construct($name, $course_id);
        $this->indicateur = $indicateur;
        define(ERREUR_INDICATEUR, "Paramètre incorrect, \$indicateur doit valoir \"nu\" ou \"nd\"");
    }

    public function inclusion() {
        include_once(__DIR__."/../data_provider.php");
        include_once(__DIR__."/../traitement.php");
        include_once(__DIR__."/../configurator.php");
        include_once(__DIR__."/../exporter.php");
        include_once(__DIR__."/../../indicators.php");
        include_once(__DIR__."/../../constants.php");
    }

    public function common_tests() {
        $this->verifier_serialized($this->indicateur);
    }

    abstract public function specific_tests();

    protected function verifier_serialized($indicateur) {
        echo "<h3>Vérification de la cohérence des données sérialisées avec les données inscrites dans le CSV fourni</h3>";

        switch($this->indicateur) {
            case "nu" :
                $nom_indicateur = "niveau d'utilisation";
                $index_indicateur = "niveau_d_utilisation";
                break;
            case "nd" :
                $nom_indicateur = "niveau de digitalisation";
                $index_indicateur = "niveau_de_digitalisation";
                break;
            default :
                throw new \Error(ERREUR_INDICATEUR);
        }

        global $CFG;
        $path_serialized_data = $CFG->dataroot."/hybridmeter/records/serialized_data";

        \report_hybridmeter\classes\logger::log(array("frr",$path_serialized_data));

        $data_unserialized = unserialize(file_get_contents($path_serialized_data));

        \report_hybridmeter\classes\logger::log(array("ptn",$data_unserialized));

        if(data_unserialized === false) {
            echo "<p>Impossible de déserialiser les résultats du dernier calcul, pouvez-vous relancer les calculs pour ce cours ?</p>";
            return false;
        }
        
        if (!isset($data_unserialized['data'][$this->course_id])){
            echo "<p>Le cours (id = ".$this->course_id.") est introuvable dans les données sérialisées, pouvez-vous relancer les calculs pour ce cours ?</p>";
            return false;
        }
        
        if (!isset($data_unserialized['data'][$this->course_id][$index_indicateur])) {
            echo "<p>Impossible de trouver le ".$nom_indicateur." pour ce cours (id = ".$this->course_id."), pouvez-vous relancer les calculs pour ce cours ?</p>";
            return false;
        }

        echo "<p>Le ".$nom_indicateur." qui a effectivement été calculé pour le cours d'ID ".$this->course_id." est ".$data_unserialized['data'][$this->course_id][$index_indicateur]."</p>";
    }

    protected function test_coeffs() {
        echo "<h3>Vérification des coefficients</h3>";

        switch($this->indicateur) {
            case "nu" :
                $coeffs = \report_hybridmeter\classes\configurator::getInstance()->get_coeffs_grid("dynamic_coeffs");
                break;
            case "nd" :
                $coeffs = \report_hybridmeter\classes\configurator::getInstance()->get_coeffs_grid("static_coeffs");
                break;
            default :
                throw new \Error(ERREUR_INDICATEUR);
        }

        echo "<p>Voici les coefficients</p>";
        echo utils::columns_rows_array_to_html($coeffs);
    }

    protected function test_hybridation_calculus() {
        echo "<h3>Vérification de la fonction de calcul de l'hybridation</h3>"; 

        $test_dataset = array(
            "assign" => "4",
            "chat" => "1",
            "forum" => "1",
            "quiz" => "1",
            "survey" => "1",
            "assignment" => "7",
            "book" => "5",
            "choice" => "4",
            "data" => "5",
            "feedback" => "1",
            "folder" => "3",
            "glossary" => "0",
            "h5pactivity" => "2",
            "imscp" => "0",
            "label" => "1",
            "lesson" => "0",
            "lti" => "0",
            "page" => "0",
            "resource" => "0",
            "scorm" => "0",
            "url" => "4",
            "wiki" => "1",
            "workshop" => "1",
        );

        echo "<p>Voici les paramètres qui vont être utilisés pour le calcul de la fonction : </p>";

        echo utils::data_grouped_by_to_html($test_dataset);

        switch($this->indicateur) {
            case "nu" :
                $mode = "dynamic_coeffs";
                break;
            case "nd" :
                $coeffs = "static_coeffs";
                break;
            default :
                throw new \Error(ERREUR_INDICATEUR);
        }

        $test = hybridation_calculus(
            $mode,
            $test_dataset
        );

        echo "<p>La fonction hybridation_calculus a renvoyé le résultat suivant : ".$test."</p>";
    }
}
    