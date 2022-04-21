<?php

require(dirname(__FILE__).'/../../config.php');
require(dirname(__FILE__).'/classes/test_context.php');
require(dirname(__FILE__).'/classes/tests/NU/nu_incoherent_scenario.php');
require(dirname(__FILE__).'/classes/tests/ND/nd_incoherent_scenario.php');
require(dirname(__FILE__).'/classes/tests/course_count/nb_actifs_inscrits_incoherents_scenario.php');
require(dirname(__FILE__).'/classes/tests/blacklist_scenario.php');

require_login();
$context = context_system::instance();
$PAGE->set_context($context);
has_capability('report/hybridmeter:all', $context) || die();


$retour_management = "<p><a href=\"management.php\">Retour à l'écran de configuration</a></p>";
echo $retour_management;

$tests_set = array();

$task = required_param('task', PARAM_TEXT);

switch($task) {
  case "blacklist" :
    array_push($tests_set, new \report_hybridmeter\classes\tests\blacklist_scenario());
    break;
  case "course" :
    $id = required_param('id', PARAM_INT);

    global $DB;

    $course_info = $DB->get_record('course', array("id" => $id));

    echo "<h1>Cours n°".$id." : ".$course_info->fullname."</h1>";

    array_push($tests_set, new \report_hybridmeter\classes\tests\ND\nd_incoherent_scenario($id));
    array_push($tests_set, new \report_hybridmeter\classes\tests\NU\nu_incoherent_scenario($id));
    array_push($tests_set, new \report_hybridmeter\classes\tests\course_count\nb_actifs_inscrits_incoherents_scenario($id));

    break;
  default :
    echo "<p>Paramètre task incorrect</p>";
    break;
}

\report_hybridmeter\classes\test_context::launch_batch($tests_set);

echo $retour_management;