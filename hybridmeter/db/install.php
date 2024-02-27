<?php
/*
 * Hybrid Meter
 * Copyright (C) 2020 - 2024  ISAE-SUPAERO
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

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
