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
        $directory = opendir($path);
        while(false !== ( $file = readdir($directory)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                $full = $path . '/' . $file;
                if ( is_dir($full) ) {
                    rm_dir($full);
                }
                else {
                    unlink($full);
                }
            }
        }
        closedir($directory);
        rmdir($path);
    }
}
/**
 * Post-install script
 */
function xmldb_report_hybridmeter_install() {
    make_dirs();
}
