<?php

require('../../config.php');
require_once($CFG->libdir.'/adminlib.php');

require_login();
$context = context_system::instance();
$PAGE->set_context($context);
has_capability('report/hybridmeter:all', $context) || die();

admin_externalpage_setup('report_hybridmeter');


$title = get_string('pluginname', 'report_hybridmeter');
$pagetitle = get_string('config', 'report_hybridmeter');
$url = new moodle_url("$CFG->wwwroot/report/hybridmeter/index.php");
$PAGE->set_url($url);
$PAGE->set_title($title);
$PAGE->set_heading($title);

$PAGE->requires->css('/report/hybridmeter/output/management.css');
$PAGE->requires->js_call_amd('report_hybridmeter/test', 'init', [
    'www_root' => $CFG->wwwroot,
    'ajax_url' => "{$CFG->wwwroot}/report/hybridmeter/ajax",
    'plugin_frankenstyle' => "report_hybridmeter",
]);

echo $OUTPUT->header();

echo <<<'EOT'
<div id="app">
</div>
EOT;

echo $OUTPUT->footer();