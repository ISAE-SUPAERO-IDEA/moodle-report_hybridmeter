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

$PAGE->requires->js_call_amd('report_hybridmeter/management', 'init', [
    'www_root' => $CFG->wwwroot,
    'ajax_url' => "{$CFG->wwwroot}/report/hybridmeter/ajax",
    'plugin_frankenstyle' => "report_hybridmeter",
]);

echo $OUTPUT->header();

echo <<<'EOT'
<div id="app">
</div>
EOT;

echo html_writer::link(
    $url,
    get_string('back_to_plugin', 'report_hybridmeter'),
    array(
        'class' => 'row btn btn-primary',
        'style' => 'margin-left: 5px; margin-top: 20px;',
    )
);

echo $OUTPUT->footer();