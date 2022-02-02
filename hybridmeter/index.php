<?php

require(dirname(__FILE__).'/../../config.php');
require_once(dirname(__FILE__).'/classes/task/traitement.php');
require_once(dirname(__FILE__).'/output/renderer.php');
require_once(dirname(__FILE__).'/indicators.php');
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

$context = context_system::instance();
$PAGE->set_context($context);
has_capability('report/hybridmeter:all', $context) || die();

admin_externalpage_setup('report_hybridmeter');

// TODO: gestion erreur si pas encore de données sérialisées
// TODO: Déplacer dans une classe gérant le fichier sérialisé
$path_serialized_data = $CFG->dataroot."/hybridmeter/records/serialized_data";
if(file_exists($path_serialized_data)){
	$data_available = true;
	$data_unserialized = unserialize(file_get_contents($path_serialized_data));

	$time = $data_unserialized['time'];

	$generaldata = $data_unserialized['generaldata'];
	
	$date_record = new \DateTime();
	$date_record->setTimestamp($time['timestamp_debut']);


	function modulo_fixed($x,$n){
		$r = $x % $n;
		if ($r < 0)
		{
		    $r += abs($n);
		}
		return $r;
	}

	$t=$data_unserialized['time']['diff'];

	if($t<60){
		$intervalle_format = sprintf('%02d secondes', $t);
	}
	else if ($t<3600){
		$intervalle_format = sprintf('%02d minutes %02d secondes', ($t/60), modulo_fixed($t,60));
	}
	else{
    	$intervalle_format = sprintf('%02d heures %02d minutes %02d secondes', ($t/3600), modulo_fixed(($t/60),60), modulo_fixed($t,60));
    }
    
	$date_format = $date_record->format('d/m/Y à H:i:s');
}
else{
	$data_available = false;
	$date_format = NA;
	$generaldata = null;
	$time = null;
}

$task = optional_param('task', array(), PARAM_TEXT);

if ($task=='download'){
	$exporter = new exporter(FIELDS, ALIAS, FIELDS_TYPE);
	$exporter->set_data($data_unserialized['data']);
	$exporter->create_csv($SITE->fullname."-".$date_format);
	$exporter->download_file();
}

$configurator = configurator::getInstance();

if ($task=='calculate'){
	data_provider::getInstance()->clear_adhoc_tasks();
	$traitement = new \report_hybridmeter\task\traitement();
	\core\task\manager::queue_adhoc_task($traitement);
}
else if ($task == "cleartasks"){
	data_provider::getInstance()->clear_adhoc_tasks();
}

$title = get_string('pluginname', 'report_hybridmeter');
$pagetitle = $title;
$url = new \moodle_url("$CFG->wwwroot/report/hybridmeter/index.php");
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
	$date_format,
	$intervalle_format
);

echo $output->next_schedule(
	$configurator->has_scheduled_calculation(),
	$configurator->get_scheduled_date(),
	$is_unscheduling
);
echo $output->index_links($data_available);

if($debug != 0){
	$count_adhoc = data_provider::getInstance()->count_adhoc_tasks();
	$is_running=$configurator->get_running();
	echo $output->is_task_planned($count_adhoc, $is_running);
	echo $output->last_calculation($data_available, $date_format, $intervalle_format);
}

echo $output->footer();
