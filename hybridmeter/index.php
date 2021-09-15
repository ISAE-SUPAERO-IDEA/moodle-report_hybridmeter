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

require_login();

$context = context_system::instance();
$PAGE->set_context($context);
has_capability('report/hybridmeter:all', $context) || die();

admin_externalpage_setup('report_hybridmeter');

//On récupère les paramètres passés (en POST dans notre cas, mais si on passe en GET ça détectera aussi) pour coder les commandes
$task = optional_param('task', array(), PARAM_TEXT);

// TODO: gestion erreur si pas encore de données sérialisées
// TODO: Déplacer dans une classe gérant le fichier sérialisé
$path_serialized_data = $CFG->dataroot."/hybridmeter/records/serialized_data";
if(file_exists($path_serialized_data)){
	$data_available = true;
	$data_unserialized = unserialize(file_get_contents($path_serialized_data));
	$generaldata = $data_unserialized['generaldata'];
	$date_record = new \DateTime();
	$date_record->setTimestamp($data_unserialized['timestamp']);
	$date_format = $date_record->format('Y-m-d H:i:s');
}
else{
	$data_available = false;
	$date_format = NA;
	$generaldata = null;
}

if ($task=='download'){
	$exporter= new \report_hybridmeter\classes\exporter(array('id','fullname','dynamique', 'statique','cours_actif', 'nb_utilisateurs_actifs', 'nb_inscrits'));
	$exporter->set_data($data_unserialized['data']);
	$exporter->create_csv($SITE->fullname."-".$date_format);
	$exporter->download_file();
}

$data = new \report_hybridmeter\classes\data();

if ($task=='calculate'){
	$traitement = new \report_hybridmeter\task\traitement();
	\core\task\manager::queue_adhoc_task($traitement);
}

$title = get_string('pluginname', 'report_hybridmeter');
$pagetitle = $title;
$url = new \moodle_url("$CFG->wwwroot/report/hybridmeter/index.php");
$PAGE->set_url($url);
$PAGE->set_title($title);
$PAGE->set_heading($title);

$output = $PAGE->get_renderer('report_hybridmeter');

echo $output->header();
echo $output->heading($pagetitle);
echo $output->general_indicators($data_available, $generaldata);
echo $output->index_links();
//echo $output->is_task_planned($date_format, $data->count_adhoc_tasks(), $data->is_task_running());
//echo $output->last_calculation($data_available, $date_format);

echo $output->footer();
