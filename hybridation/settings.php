<?php

defined('MOODLE_INTERNAL') || die;

$ADMIN->add('reports', new admin_externalpage('report_hybridation', get_string('pluginname', 'report_hybridation'), "$CFG->wwwroot/report/hybridation/index.php"));
//$settings->add(new admin_setting_configtext('mod_lesson/mediawidth', get_string('mediawidth', 'lesson'),
//    get_string('configmediawidth', 'lesson'), 640, PARAM_INT));
