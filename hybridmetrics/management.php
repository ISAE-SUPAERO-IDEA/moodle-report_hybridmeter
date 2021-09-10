<?php

require(dirname(__FILE__).'/../../config.php');
require_once(dirname(__FILE__).'/classes/data.php');
//require_once(dirname(__FILE__).'/classes/management_form.php');
require_once(dirname(__FILE__).'/output/management/management_renderer.php');
require_once(dirname(__FILE__).'/constants.php');
require_once($CFG->libdir.'/adminlib.php');

require_login();
$context = context_system::instance();
$PAGE->set_context($context);
has_capability('report/hybridmetrics:all', $context) || die();

admin_externalpage_setup('report_hybridmetrics');

$PAGE->requires->css('/report/hybridmetrics/output/management.css');

$categoryid = optional_param('categoryid', null, PARAM_INT);
$selectedcategoryid = optional_param('selectedcategoryid', null, PARAM_INT);
$courseid = optional_param('courseid', null, PARAM_INT);
$action = optional_param('action', false, PARAM_ALPHA);

$search = optional_param('search', '', PARAM_RAW);

$systemcontext = $context = context_system::instance();

$data = new \report_hybridmetrics\classes\data();

$title = get_string('pluginname', 'report_hybridmetrics');
$pagetitle = get_string('config', 'report_hybridmetrics');
$url = new \moodle_url("$CFG->wwwroot/report/hybridmetrics/index.php");
$PAGE->set_url($url);
$PAGE->set_title($title);
$PAGE->set_heading($title);

$renderer = $PAGE->get_renderer('report_hybridmetrics', 'management');
$renderer->enhance_management_interface();

echo $renderer->header();
echo $renderer->heading($pagetitle);

echo $renderer->include_vue();

echo $renderer->footer();