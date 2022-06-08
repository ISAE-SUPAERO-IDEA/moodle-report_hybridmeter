<?php

require_once(dirname(__FILE__)."/../../config.php");

defined('MOODLE_INTERNAL') || die;

$ADMIN->add('reports', new admin_externalpage('report_hybridmeter', get_string('pluginname', 'report_hybridmeter'), "$CFG->wwwroot/report/hybridmeter/index.php"));

if ($hassiteconfig) {
    $settings->add(new admin_setting_heading(
        'hybridmeter',
        get_string('hybridmeter_settings', 'report_hybridmeter'),
        get_string('hybridmeter_settings_help', 'report_hybridmeter')
    ));
    $url = new moodle_url("/report/hybridmeter/index.php");
}
