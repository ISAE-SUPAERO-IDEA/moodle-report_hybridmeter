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

if ($task=='download'){
	$exporter= new \report_hybridmeter\classes\exporter(array('id_moodle', 'idnumber', 'fullname', 'url', 'niveau_de_digitalisation', 'niveau_d_utilisation', 'cours_actif', 'nb_utilisateurs_actifs', 'nb_inscrits', 'date_debut_capture', 'date_fin_capture'));
	$exporter->set_data($data_unserialized['data']);
	$exporter->create_csv($SITE->fullname."-".$date_format);
	$exporter->download_file();
}

$data = new \report_hybridmeter\classes\data();
$configurator = new \report_hybridmeter\classes\configurator($data);

if ($task=='calculate'){
	$data->clear_adhoc_tasks();
	$traitement = new \report_hybridmeter\task\traitement();
	\core\task\manager::queue_adhoc_task($traitement);
}
else if ($task == "cleartasks"){
	$data->clear_adhoc_tasks();
}

$title = get_string('pluginname', 'report_hybridmeter');
$pagetitle = $title;
$url = new \moodle_url("$CFG->wwwroot/report/hybridmeter/index.php");
$PAGE->set_url($url);
$PAGE->set_title($title);
$PAGE->set_heading($title);

$output = $PAGE->get_renderer('report_hybridmeter');

$debug = optional_param('debug', 0, PARAM_INTEGER);

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
echo $output->index_links($data_available);

if($debug != 0){
	$count_adhoc = $data->count_adhoc_tasks();
	$is_running=$configurator->get_running();
	echo $output->is_task_planned($count_adhoc, $is_running);
	echo $output->last_calculation($data_available, $date_format, $intervalle_format);
}

echo $output->footer();
