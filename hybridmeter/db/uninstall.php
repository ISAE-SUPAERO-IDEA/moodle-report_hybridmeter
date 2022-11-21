<?php

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/install.php');

function xmldb_report_hybridmeter_uninstall() {
    rm_dir("/hybridmeter");
    rm_dir("/hybridmetrics");
}