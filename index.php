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
 * @package report_hybridmeter
 */

require(dirname(__FILE__).'/../../config.php');
require_once(dirname(__FILE__).'/output/renderer.php');
require_once(dirname(__FILE__).'/constants.php');
require_once($CFG->libdir.'/adminlib.php');

require_login();

use report_hybridmeter\utils as utils;
use report_hybridmeter\exporter as exporter;
use report_hybridmeter\configurator as configurator;
use report_hybridmeter\data_provider as data_provider;
use report_hybridmeter\task\processing as processing;

$context = context_system::instance();
$PAGE->set_context($context);
has_capability('report/hybridmeter:all', $context) || die();

admin_externalpage_setup('report_hybridmeter');

$pathserializeddata = $CFG->dataroot . "/hybridmeter/records/serialized_data";

if (file_exists($pathserializeddata)) {
    $dataavailable = true;
    $dataunserialized = unserialize(file_get_contents($pathserializeddata));
    $time = $dataunserialized['time'];

    $generaldata = $dataunserialized['generaldata'];

    $daterecord = new DateTime();
    $daterecord->setTimestamp($time["begin_timestamp"]);

    $t = $dataunserialized['time']['diff'];

    if ($t < 60) {
        $intervalformat = sprintf(get_string('template_seconds', 'report_hybridmeter'), $t);
    } else {
        if ($t < 3600) {
            $intervalformat = sprintf(
                get_string('template_minutes_seconds', 'report_hybridmeter'),
                ($t / 60),
                utils::modulo_fixed($t, 60)
            );
        } else {
            $intervalformat = sprintf(
                get_string('template_hours_minutes_seconds', 'report_hybridmeter'),
                ($t / 3600),
                utils::modulo_fixed(($t / 60), 60),
                utils::modulo_fixed($t, 60)
            );
        }
    }

    $formatteddate = $daterecord->format('d/m/Y à H:i:s');
} else {
    $dataavailable = false;
    $formatteddate = REPORT_HYBRIDMETER_NA;
    $generaldata = null;
    $time = null;
    $intervalformat = null;
}

$task = optional_param('task', [], PARAM_TEXT);

if ($task == 'download') {
    $exporter = new exporter(FIELDS, ALIAS, FIELDS_TYPE);
    $exporter->set_data($dataunserialized['data']);
    $exporter->create_csv($SITE->fullname . "-" . $formatteddate);
    $exporter->download_file();
}

$configurator = configurator::get_instance();

if ($task == 'calculate') {
    data_provider::get_instance()->clear_adhoc_tasks();
    $processing = new processing();
    \core\task\manager::queue_adhoc_task($processing);
} else if ($task == "cleartasks") {
    data_provider::get_instance()->clear_adhoc_tasks();
}

$title = get_string('pluginname', 'report_hybridmeter');
$pagetitle = $title;
$url = new moodle_url("$CFG->wwwroot/report/hybridmeter/index.php");
$PAGE->set_url($url);
$PAGE->set_title($title);
$PAGE->set_heading($title);

$output = $PAGE->get_renderer('report_hybridmeter');

$debug = optional_param('debug', 0, PARAM_INTEGER);
$unschedule = optional_param('unschedule', 0, PARAM_INTEGER);

$isunscheduling = 0;

if ($unschedule == 1) {
    $configurator->unschedule_calculation();
    $isunscheduling = 1;
}

echo $output->header();
echo $output->heading($pagetitle);
echo $output->general_indicators(
    $dataavailable,
    $generaldata,
    $configurator->get_begin_timestamp(),
    $configurator->get_end_timestamp(),
    $formatteddate,
    $intervalformat
);

echo $output->next_schedule(
    $configurator->has_scheduled_calculation(),
    $configurator->get_scheduled_date(),
    $isunscheduling
);
echo $output->index_links($dataavailable);

if ($debug != 0) {
    $countadhoc = data_provider::get_instance()->count_adhoc_tasks();
    $isrunning = $configurator->get_running();
    echo $output->is_task_planned($countadhoc, $isrunning);
    echo $output->last_calculation($dataavailable, $formatteddate, $intervalformat);
}

echo $output->footer();
