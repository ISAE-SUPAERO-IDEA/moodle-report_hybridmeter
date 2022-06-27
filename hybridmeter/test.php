<?php

require('../../config.php');

require_login();
$context = context_system::instance();
$PAGE->set_context($context);

$title = get_string('pluginname', 'report_hybridmeter');
$pagetitle = get_string('config', 'report_hybridmeter');
$url = new moodle_url("$CFG->wwwroot/report/hybridmeter/index.php");
$PAGE->set_url($url);
$PAGE->set_title($title);
$PAGE->set_heading($title);

$PAGE->requires->js_call_amd('report_hybridmeter/test', 'init');

echo $OUTPUT->header();

echo <<<'EOT'
<div id="app">
</div>
EOT;

echo $OUTPUT->footer();