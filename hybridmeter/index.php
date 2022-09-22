<?php

require(dirname(__FILE__).'/../../config.php');
require_once(dirname(__FILE__).'/classes/task/processing.php');
require_once(dirname(__FILE__).'/output/renderer.php');
require_once(dirname(__FILE__).'/constants.php');
require_once(dirname(__FILE__).'/classes/utils.php');
require_once(dirname(__FILE__).'/classes/formatter.php');
require_once(dirname(__FILE__).'/classes/exporter.php');
require_once(dirname(__FILE__).'/classes/configurator.php');
require_once(dirname(__FILE__).'/classes/data_provider.php');
require_once($CFG->libdir.'/adminlib.php');

require_login();

use \report_hybridmeter\classes\utils as utils;
use \report_hybridmeter\classes\formatter as formatter;
use \report_hybridmeter\classes\exporter as exporter;
use \report_hybridmeter\classes\configurator as configurator;
use \report_hybridmeter\classes\data_provider as data_provider;
use \report_hybridmeter\task\processing as processing;

$context = context_system::instance();
$PAGE->set_context($context);
has_capability('report/hybridmeter:all', $context) || die();

admin_externalpage_setup('report_hybridmeter');

// TODO: Move to a class managing the serialized file

$path_serialized_data = $CFG->dataroot."/hybridmeter/records/serialized_data";

if (file_exists($path_serialized_data)) {
    $data_available = true;
    $data_unserialized = unserialize(file_get_contents($path_serialized_data));
    error_log(print_r($data_unserialized, 1));
    $time = $data_unserialized['time'];

    $generaldata = $data_unserialized['generaldata'];
    
    $date_record = new DateTime();
    $date_record->setTimestamp($time["begin_timestamp"]);

    $t=$data_unserialized['time']['diff'];

    if($t<60){
        $intervalle_format = sprintf(get_string('template_seconds', 'report_hybridmeter'), $t);
    }
    else if ($t<3600){
        $intervalle_format = sprintf(get_string('template_minutes_seconds', 'report_hybridmeter'), ($t/60), utils::modulo_fixed($t,60));
    }
    else{
        $intervalle_format = sprintf(get_string('template_hours_minutes_seconds', 'report_hybridmeter'), ($t/3600), utils::modulo_fixed(($t/60),60), utils::modulo_fixed($t,60));
    }
    
    $formatted_date = $date_record->format('d/m/Y à H:i:s');
}
else{
    $data_available = false;
    $formatted_date = REPORT_HYBRIDMETER_NA;
    $generaldata = null;
    $time = null;
    $intervalle_format = null;
}

$task = optional_param('task', array(), PARAM_TEXT);

if ($task=='download'){
    $exporter = new exporter(FIELDS, ALIAS, FIELDS_TYPE);
    $exporter->set_data($data_unserialized['data']);
    $exporter->create_csv($SITE->fullname."-".$formatted_date);
    $exporter->download_file();
}

$configurator = configurator::get_instance();

if ($task=='calculate'){
    data_provider::get_instance()->clear_adhoc_tasks();
    $processing = new processing();
    \core\task\manager::queue_adhoc_task($processing);
}
else if ($task == "cleartasks"){
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

$is_unscheduling = 0;

if($unschedule == 1){
    $configurator->unschedule_calculation();
    $is_unscheduling = 1;
}

echo $output->header();
echo $output->heading($pagetitle);
echo $output->general_indicators(
    $data_available,
    $generaldata,
    $configurator->get_begin_timestamp(),
    $configurator->get_end_timestamp(),
    $formatted_date,
    $intervalle_format
);

echo $output->next_schedule(
    $configurator->has_scheduled_calculation(),
    $configurator->get_scheduled_date(),
    $is_unscheduling
);
echo $output->index_links($data_available);

if($debug != 0){
    $count_adhoc = data_provider::get_instance()->count_adhoc_tasks();
    $is_running=$configurator->get_running();
    echo $output->is_task_planned($count_adhoc, $is_running);
    echo $output->last_calculation($data_available, $formatted_date, $intervalle_format);
}

echo $output->footer();
