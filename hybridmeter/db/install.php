<?php

defined('MOODLE_INTERNAL') || die();

function make_dir($dir) {
    global $CFG;
    $path = $CFG->dataroot.$dir;
    if (!file_exists($path)) {
        mkdir($path);
    }
}
function make_dirs() {
    make_dir("/hybridmeter");
    make_dir("/hybridmeter/records");
    make_dir("/hybridmeter/records/backup");
}
/**
 * Post-install script
 */
function xmldb_report_hybridmeter_install() {
    make_dirs();
}
