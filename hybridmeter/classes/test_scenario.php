<?php

namespace report_hybridmeter\classes;

require_once(__DIR__."/formatter.php");
require_once(__DIR__."/utils.php");

use \report_hybridmeter\classes\utils as utils;

abstract class test_scenario {

    public $name;

    public function __construct($name) {
        $this->name = $name;
    }

    public function test() {
        echo "<h1>Test : ".$this->name."</h1>";
        echo "<h2>Inclusion des librairies</h2>";
        $this->inclusion();
        echo "<p>Les librairies ont bien été incluses</p>";
        echo "<h2>Lancement des tests</h2>";
        $this->dump_config();
        $this->common_tests();
        $this->specific_tests();
    }
    abstract public function inclusion();
    abstract public function common_tests();
    abstract public function specific_tests();

    protected function dump_config() {
        echo "<h3>Dump du fichier config.json</h3>";

        global $CFG;

		$path=$CFG->dataroot."/hybridmeter/config.json";

        if (!file_exists($path))
            echo "<p>Il semble qu'il n'y ait pas de fichier config.json</p>";
        else {
            $data = file_get_contents($path);
            echo "<p>Voici le fichier brut</p>";
            var_dump($data);
            echo "<p>Voici le fichier décodé</p>";
			print_r(json_decode($data, true));
        }
    }

    protected function test_student_role() {
        echo "<h3>Verification de la bonne configuration des rôles</h3>";

        echo "<p>Voici le rôle étudiant défini dans les paramètres :</p>";

        var_dump(\report_hybridmeter\classes\configurator::getInstance()->get_student_role());

        echo "<p>Voici les rôles disponibles sur le serveur moodle : </p>";

        global $DB;

        echo utils::objects_array_to_html($DB->get_records("role"));
    }
}