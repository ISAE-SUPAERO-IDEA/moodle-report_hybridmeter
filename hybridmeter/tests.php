<?php
/*
 * Hybryd Meter
 * Copyright (C) 2020 - 2024  ISAE-Supaéro
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require(dirname(__FILE__).'/../../config.php');
require(dirname(__FILE__).'/classes/test_context.php');
require(dirname(__FILE__).'/classes/tests/NU/inconsistent_nu.php');
require(dirname(__FILE__).'/classes/tests/ND/inconsistent_nd.php');
require(dirname(__FILE__).'/classes/tests/course_count/inconsistent_registered_active_students.php');
require(dirname(__FILE__).'/classes/tests/blacklist_scenario.php');

use \report_hybridmeter\classes\tests\ND\inconsistent_nd as inconsistent_nd;
use \report_hybridmeter\classes\tests\NU\inconsistent_nu as inconsistent_nu;
use \report_hybridmeter\classes\tests\course_count\inconsistent_registered_active_students as inconsistent_registered_active_students;
use \report_hybridmeter\classes\tests\blacklist_scenario as blacklist_scenario;

require_login();
$context = context_system::instance();
$PAGE->set_context($context);
has_capability('report/hybridmeter:all', $context) || die();


$retour_management = "<p><a href=\"management.php\">Retour à l'écran de configuration</a></p>";
echo $retour_management;

$tests_set = array();

$task = required_param('task', PARAM_TEXT);

switch($task) {
    case "blacklist" :
        array_push($tests_set, new blacklist_scenario());
        break;

    case "course" :
        $id = required_param('id', PARAM_INT);

        global $DB;

        $course_info = $DB->get_record('course', array("id" => $id));

        echo "<h1>Cours n°".$id." : ".$course_info->fullname."</h1>";

        array_push($tests_set, new inconsistent_nd($id));
        array_push($tests_set, new inconsistent_nu($id));
        array_push($tests_set, new inconsistent_registered_active_students($id));

        break;

    default :
        echo "<p>Paramètre task incorrect</p>";
        break;
}

\report_hybridmeter\classes\test_context::launch_batch($tests_set);

echo $retour_management;
