<?php

require_once(dirname(__FILE__).'/install.php');

defined('MOODLE_INTERNAL') || die();

function xmldb_report_hybridmeter_uninstall() {
    rm_dir("/hybridmeter");
    rm_dir("/hybridmetrics");
}