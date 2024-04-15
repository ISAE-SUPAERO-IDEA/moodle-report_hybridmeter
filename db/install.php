<?php
// This file is part of Moodle - http://moodle.org
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @author Nassim Bennouar
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2020  ISAE-SUPAERO (https://www.isae-supaero.fr/)
 * @package
 */
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
    $files = array_diff(scandir($path), ['.', '..']);
    foreach ($files as $file) {
        (is_dir("$path/$file")) ? rm_dir_rec("$path/$file") : unlink("$path/$file");
    }
    return rmdir($path);
}
/**
 * Post-install script
 * @package
 */
function xmldb_report_hybridmeter_install() {
    make_dirs();
}