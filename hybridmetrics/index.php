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
$task = optional_param('task', array(), PARAM_TEXT);

// TODO: gestion erreur si pas encore de données sérialisées
// TODO: Déplacer dans une classe gérant le fichier sérialisé
$data_unserialized = unserialize(file_get_contents($CFG->dataroot."/hybridmetrics/records/serialized_data"));
$date_record = new \DateTime();
$date_record->setTimestamp($data_unserialized['timestamp']);
$date_format = $date_record->format('Y-m-d\_H:i:s');

if ($task=='download'){
	$exporter= new \report_hybridmetrics\classes\exporter(array('id','fullname','dynamique', 'statique','cours_actif', 'nb_utilisateurs_actifs', 'nb_inscrits'));
	$exporter->set_data($data_unserialized['data']);
	$exporter->create_csv($SITE->fullname."-".$date_format);
	$exporter->download_file();
}

$data = new \report_hybridmetrics\classes\data();

if ($task!='calculate'){
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
//echo $output->is_task_planned($date_format, $data->count_adhoc_tasks(), $data->is_task_running());
echo $output->last_calculation($date_format);

echo $output->footer();
