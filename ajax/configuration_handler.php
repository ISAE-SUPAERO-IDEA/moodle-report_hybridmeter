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
 * Endpoint to update or retrieve HybridMeter config.
 *
 * @author Nassim Bennouar
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2020  ISAE-SUPAERO (https://www.isae-supaero.fr/)
 * @package report_hybridmeter
 */

require_once(dirname(__FILE__)."/../../../config.php");

use report_hybridmeter\configurator as configurator;
use report_hybridmeter\ouput\config_output;
use report_hybridmeter\task\scheduler;

header('Content-Type: text/json');

// Checking authorizations (admin role required).
require_login();

$context = \context_system::instance();
$PAGE->set_context($context);
has_capability('report/hybridmeter:all', $context) || die();

$configurator = configurator::get_instance();
$scheduler = scheduler::get_instance();

$output = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $action = optional_param('action', 'nothing', PARAM_ALPHAEXT);
    $debug = optional_param('debug', null, PARAM_BOOL);

    if ($action == "measurement_period") {
        $begindate = required_param('begin_date', PARAM_INT);
        $enddate = required_param('end_date', PARAM_INT);

        $config = $configurator->get_config();
        $config->update_period($begindate, $enddate);
    } else if ($action == "schedule") {
        $scheduledtimestamp = required_param('scheduled_timestamp', PARAM_INT);
        $scheduler->schedule_calculation($scheduledtimestamp, $configurator->get_config());
    } else if ($action == "unschedule") {
        $scheduler->unschedule_calculation($configurator->get_config());
        $configurator->get_config()->set_debug($debug);
    } else if ($action == "additional_config") {
        $studentarchetype = required_param('student_archetype', PARAM_ALPHAEXT);
        $config = $configurator->get_config();
        $config->update_additionnal_config($studentarchetype, $debug);
    }
} else if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $configoutput = new config_output($configurator->get_config());


    $task  = optional_param('task', 'nothing', PARAM_ALPHAEXT);

    if ($task == "get_usage_coeffs") {
        $output = $configoutput->get_coeffs_grid("usage_coeffs");
    } else if ($task == "get_digitalisation_coeffs") {
        $output = $configoutput->get_coeffs_grid("digitalisation_coeffs");
    } else if ($task == "get_all_coeffs") {
        $output = $configoutput->get_all_modulenames_rows();
    } else if ($task == "get_seuils") {
        $output = $configoutput->get_treshold_grid();
    } else if ($task == "get_tresholds") {
        $output = $configoutput->get_tresholds_rows();
    } else {
        $output = $configurator->get_config();
    }
} else {
    $output = [
        "error" => true,
        "message" => "GET method not supported, please retry with a POST request",
    ];
}

echo json_encode($output);
