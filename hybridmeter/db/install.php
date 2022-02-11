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

function rm_dir($dir) {
    global $CFG;
    $path = $CFG->dataroot.$dir;
    
    if (is_dir($path)) {
        rm_dir_rec($path);
    }
}

function rm_dir_rec($path) {
    $files = array_diff(scandir($path), array('.','..'));
    foreach ($files as $file) {
        (is_dir("$path/$file")) ? rm_dir_rec("$path/$file") : unlink("$path/$file");
    }
    return rmdir($path); 
}
/**
 * Post-install script
 */
function xmldb_report_hybridmeter_install() {
    make_dirs();
}
