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
 * Display indicators for a specific course.
 *
 * @author Nassim Bennouar, John Tranier
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2020  ISAE-SUPAERO (https://www.isae-supaero.fr/)
 * @package report_hybridmeter
 */

require(dirname(__FILE__) . '/../../config.php');

use report_hybridmeter\course_indicators;
use report_hybridmeter\indicators;
use report_hybridmeter\data_provider as data_provider;

require_login();
$context = context_system::instance();
$PAGE->set_context($context);
require_capability('report/hybridmeter:all', $context);

$courseid = required_param('id', PARAM_INT);

global $DB;

$courseinfo = $DB->get_record('course', ["id" => $courseid]);
$dataprovider = data_provider::get_instance();

$config = \report_hybridmeter\config::get_instance();
$begin = $config->get_begin_date();
$end = $config->get_end_date();


$output = $PAGE->get_renderer('report_hybridmeter');
$url = new moodle_url("$CFG->wwwroot/report/hybridmeter/course?id=".$courseid.".php");
$PAGE->set_url($url);
echo $output->header();
echo $output->course_indicators(
    new course_indicators(
        $courseinfo->fullname,
        $begin,
        $end,
        indicators::digitalisation_level($courseid),
        indicators::usage_level($courseid),
        indicators::nb_registered_students($courseid),
        indicators::active_students($courseid),
        indicators::is_course_active_on_period($courseid),
        $dataprovider->count_activities_per_type_of_course($courseid),
        $dataprovider->count_hits_on_activities_per_type(
            $courseid,
            $begin,
            $end
        )
    )
);



echo "<p><a href=\"management.php\">Back to config</a></p>";
echo $output->footer();
