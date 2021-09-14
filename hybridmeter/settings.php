<?php

defined('MOODLE_INTERNAL') || die;

$ADMIN->add('reports', new admin_externalpage('report_hybridmeter', get_string('pluginname', 'report_hybridmeter'), "$CFG->wwwroot/report/hybridmeter/index.php"));