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
    make_dir("/hybridmetrics");
    make_dir("/hybridmetrics/records");
}
/**
 * Post-install script
 */
function xmldb_report_hybridmetrics_install() {
    make_dirs();
}
