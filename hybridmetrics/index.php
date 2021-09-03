<?php

require(dirname(__FILE__).'/../../config.php');
require_once(dirname(__FILE__).'/classes/task/traitement.php');
require_once(dirname(__FILE__).'/classes/exporter.php');
require_once(dirname(__FILE__).'/classes/data.php');
require_once(dirname(__FILE__).'/classes/formatter.php');
require_once(dirname(__FILE__).'/output/renderer.php');
require_once(dirname(__FILE__).'/indicators.php');
require_once(dirname(__FILE__).'/constants.php');
require_once(dirname(__FILE__).'/classes/utils.php');
require_once($CFG->libdir.'/adminlib.php');

admin_externalpage_setup('report_hybridmetrics');


//On récupère les paramètres passés (en POST dans notre cas, mais si on passe en GET ça détectera aussi) pour coder les commandes
$download = optional_param('download', 'nocalc', PARAM_TEXT);
$calculate = optional_param('calculate', 'nocalc', PARAM_TEXT);

$data_unserialized = unserialize(file_get_contents(dirname(__FILE__).'/records/serialized_data'));
$date_record = new \DateTime();
$date_record->setTimestamp($data_unserialized['timestamp']);
$date_format = $date_record->format('Y-m-d\_H:i:s');

if ($download!='nocalc'){
	$exporter= new \report_hybridmetrics\classes\exporter(array('id','fullname','dynamique', 'statique','cours_actif', 'nb_utilisateurs_actifs', 'nb_inscrits'));
	$exporter->set_data($data_unserialized['data']);
	$exporter->create_csv($SITE->fullname."-".$date_format);
	$exporter->download_file();
}

$data = new \report_hybridmetrics\classes\data();

if ($calculate!='nocalc'){
	$data->clear_adhoc_tasks();
	$traitement = new \report_hybridmetrics\task\traitement();
	\core\task\manager::queue_adhoc_task($traitement);
}

$title = get_string('pluginname', 'report_hybridmetrics');
$pagetitle = $title;
$url = new \moodle_url("$CFG->wwwroot/report/hybridmetrics/index.php");
$PAGE->set_url($url);
//$PAGE->set_context($context);
$PAGE->set_title($title);
$PAGE->set_heading($title);

$output = $PAGE->get_renderer('report_hybridmetrics');

echo $output->header();
echo $output->heading($pagetitle);
echo $output->index_links();
echo $output->is_task_planned($data->count_adhoc_tasks(), $data->is_task_running());
echo $output->last_calculation($date_format);

echo $output->footer();
