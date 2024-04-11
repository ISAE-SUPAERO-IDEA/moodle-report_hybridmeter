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

require(dirname(__FILE__) . '/../../config.php');

use report_hybridmeter\tests\ND\inconsistent_nd as inconsistent_nd;
use report_hybridmeter\tests\NU\inconsistent_nu as inconsistent_nu;
use report_hybridmeter\tests\course_count\inconsistent_registered_active_students
    as inconsistent_registered_active_students;
use report_hybridmeter\tests\blacklist_scenario as blacklist_scenario;

require_login();
$context = context_system::instance();
$PAGE->set_context($context);
has_capability('report/hybridmeter:all', $context) || die();


$backlink = "<p><a href=\"management.php\">Retour à l'écran de configuration</a></p>";
echo $backlink;

$testsset = [];

$task = required_param('task', PARAM_TEXT);

switch ($task) {
    case "blacklist":
        array_push($testsset, new blacklist_scenario());
        break;

    case "course":
        $id = required_param('id', PARAM_INT);

        global $DB;

        $courseinfo = $DB->get_record('course', ["id" => $id]);

        echo "<h1>Cours n°" . $id . " : " . $courseinfo->fullname . "</h1>";

        array_push($testsset, new inconsistent_nd($id));
        array_push($testsset, new inconsistent_nu($id));
        array_push($testsset, new inconsistent_registered_active_students($id));

        break;

    default:
        echo "<p>Paramètre task incorrect</p>";
        break;
}

\report_hybridmeter\test_context::launch_batch($testsset);

echo $backlink;
