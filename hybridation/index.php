<?php

require(dirname(__FILE__).'/../../config.php');
require_once(dirname(__FILE__).'/classes/exporter.php');
require_once(dirname(__FILE__).'/classes/data.php');
require_once(dirname(__FILE__).'/classes/formatter.php');
require_once(dirname(__FILE__).'/classes/hybridation_form.php');
require_once(dirname(__FILE__).'/output/index_page.php');
require_once(dirname(__FILE__).'/output/renderer.php');
require_once($CFG->libdir.'/adminlib.php');

admin_externalpage_setup('report_hybridation');

define("SEUIL_ACTIF", 5);
const COEFF_STATIQUES=[
	MODULE_ASSIGN => 0.2,
	MODULE_ASSIGNMENT => 0.2,
	MODULE_BOOK => 0.2,
	MODULE_CHAT => 0.2,
	MODULE_CHOICE => 0.2,
	MODULE_DATA => 0.2,
	MODULE_FEEDBACK => 0.5,
	MODULE_FOLDER => 0,
	MODULE_FORUM => 0.8,
	MODULE_GLOSSARY => 0.2,
	MODULE_H5P => 0.8,
	MODULE_IMSCP => 0.2,
	MODULE_LABEL => 0.2,
	MODULE_LESSON => 0.2, 
	MODULE_LTI => 0.2,
	MODULE_PAGE => 0.2,
	MODULE_QUIZ => 0.5,
	MODULE_RESOURCE => 0.2,
	MODULE_SCORM => 0.2,
	MODULE_SURVEY => 0.2,
	MODULE_URL => 0.5,
	MODULE_WIKI => 0.5,
	MODULE_WORKSHOP => 0.8,
];

const COEFF_DYNAMIQUES=[
	MODULE_ASSIGN => 0.2,
	MODULE_ASSIGNMENT => 0.2,
	MODULE_BOOK => 0.2,
	MODULE_CHAT => 0.2,
	MODULE_CHOICE => 0.2,
	MODULE_DATA => 0.2,
	MODULE_FEEDBACK => 0.5,
	MODULE_FOLDER => 0,
	MODULE_FORUM => 0.8,
	MODULE_GLOSSARY => 0.2,
	MODULE_H5P => 0.8,
	MODULE_IMSCP => 0.2,
	MODULE_LABEL => 0.2,
	MODULE_LESSON => 0.2, 
	MODULE_LTI => 0.2,
	MODULE_PAGE => 0.2,
	MODULE_QUIZ => 0.5,
	MODULE_RESOURCE => 0.2,
	MODULE_SCORM => 0.2,
	MODULE_SURVEY => 0.2,
	MODULE_URL => 0.5,
	MODULE_WIKI => 0.5,
	MODULE_WORKSHOP => 0.8,
];

//Fonction lambda utilisée pour calculer les indicateurs statiques
function hybridation_statique($object,$data,$parameters){
	$count=$data->count_modules_types_id($object['id']);
	$indicator=0;
	foreach (COEFF_STATIQUES as $key => $value){
		$indicator+=$value*$count[$key];
	}
	return ($indicator/$parameters['nb_cours']);
}


//Fonction lambda utilisée pour calculer les indicateurs dynamiques
function hybridation_dynamique($object,$data,$parameters){
	$active=$data->count_active_single_users($object['id']);
	$indicator=0;
	if($active==0) return 0;
	foreach (COEFF_DYNAMIQUES as $key => $value){
		$count=$data->count_hits_by_module_type($object['id'],$key);
		$indicator+=$value*($count/$active);
	}
	return ($indicator/$parameters['nb_cours']);
}

//Fonction lambda utilisée pour définir si le cours est actif
function is_course_active_last_month($object, $data, $parameters){
	$count=$data->count_single_users_course_viewed($object['id'],strtotime("-1 month"),strtotime("now"));
	if ($count >= SEUIL_ACTIF)
		return 1;
	else
		return 0;
}

//On crée un objet blacklist
$blacklist=array();

//TODO : lire le JSON pour récupérer la blacklist

//On crée l'objet formatter, la fonction lambda indique ce qu'on souhaite récupérer comme données (les vrais cours visibles en excluant la blacklist)
$formatter=new \report_hybridation\classes\formatter($blacklist, function ($data, $blacklist){return $data->get_courses_sanitized($blacklist);});

/*$actionform = new hybridation_action_form();
if ($data = $actionform->get_data()){
	print_r($data);
	echo "wsh";
	$hybactions = $actionform->get_actions();
	if (array_key_exists($data->action, $hybactions)) {
		redirect($hybactions[$data->action]->url);
	}
}*/

$hybridation_form = new hybridation_form(null, array("blacklist" => $formatter->get_blacklist(), "courses" => $formatter->get_array()));

//On récupère les paramètres passés (en POST dans notre cas, mais si on passe en GET ça détectera aussi) pour coder les commandes
$download = optional_param('download', 'lol', PARAM_TEXT);
$addsel = optional_param('addsel', 'lol', PARAM_TEXT);
$removesel = optional_param('removesel', 'lol', PARAM_TEXT);
$acourses = optional_param();

//TODO : créer et gérer les permissions https://docs.moodle.org/dev/Access_API

if ($download!='lol'){
	$formatter->calculate_new_indicator("hybridation_statique", 'statique', array("nb_cours"=>$formatter->get_length_array()));
	$formatter->calculate_new_indicator("hybridation_dynamique", 'dynamique', array("nb_cours"=>$formatter->get_length_array()));
	$formatter->calculate_new_indicator("is_course_active_last_month", 'cours_actif');
	$formatter->calculate_new_indicator(function ($object, $data) { return $data->count_single_users_course_viewed($object['id'],strtotime("-1 month"),strtotime("now")); }, 'nb_utilisateurs_actifs');
	$formatter->calculate_new_indicator(function ($object, $data) { return $data->count_registered_users($object['id']); }, 'nb_inscrits');

	$data=$formatter->get_array();

	$exporter=new \report_hybridation\classes\exporter(array('id','fullname','dynamique', 'statique','cours_actif', 'nb_utilisateurs_actifs', 'nb_inscrits'));
	$exporter->set_data($data);
	$exporter->create_csv("lol");
    $exporter->download_file();
}

//TODO : gérer l'ajout et le retrait de cours en blacklist

//$courseviewurl = new moodle_url('/course/view.php', ['download' => $usernameparam]);

$title = get_string('pluginname', 'report_hybridation');
$pagetitle = $title;
$url = new \moodle_url("$CFG->wwwroot/report/hybridation/index.php");
$PAGE->set_url($url);
//$PAGE->set_context($context);
$PAGE->set_title($title);
$PAGE->set_heading($title);

$output = $PAGE->get_renderer('report_hybridation');

echo $output->header();
echo $output->heading($pagetitle);

$hybridation_form->display();
/*$renderable = new \report_hybridation\output\index_page('Download CSV',"index.php?download");

echo $output->render($renderable);*/

echo $output->footer();