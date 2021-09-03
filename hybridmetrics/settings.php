<?php

defined('MOODLE_INTERNAL') || die;

$ADMIN->add('reports', new admin_externalpage('report_hybridmetrics', get_string('pluginname', 'report_hybridmetrics'), "$CFG->wwwroot/report/hybridmetrics/index.php"));