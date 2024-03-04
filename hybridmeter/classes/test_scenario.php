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
 * @author Nassim Bennouar
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2020  ISAE-SUPAERO (https://www.isae-supaero.fr/)
 */
namespace report_hybridmeter\classes;

defined('MOODLE_INTERNAL') || die();

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
        echo "<h2>Inclusion of libraries</h2>";
        $this->inclusion();
        echo "<p>Libraries have been included</p>";
        echo "<h2>Launching the tests</h2>";
        $this->dump_config();
        $this->common_tests();
        $this->specific_tests();
    }
    abstract public function inclusion();
    abstract public function common_tests();
    abstract public function specific_tests();

    protected function dump_config() {
        echo "<h3>Dump of the config.json file</h3>";

        global $CFG;

        $path=$CFG->dataroot."/hybridmeter/config.json";

        if (!file_exists($path))
            echo "<p>It seems that there is no config.json file</p>";
        else {
            $data = file_get_contents($path);
            echo "<p>Here is the raw file</p>";
            var_dump($data);
            echo "<p>Here is the decoded file</p>";
            print_r(json_decode($data, true));
        }
    }

    protected function test_student_archetype() {
        global $DB;
        
        echo "<h3>Verification of the correct configuration of the roles</h3>";

        echo "<p>This is the student archetype defined in the settings:</p>";

        var_dump(\report_hybridmeter\classes\configurator::get_instance()->get_student_archetype());

        echo "<p>The following roles are available on the moodle server: </p>";

        echo utils::objects_array_to_html($DB->get_records("role"));
    }
}
