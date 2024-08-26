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
 * Upgrade plugin.
 * @author Nassim Bennouar
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2020  ISAE-SUPAERO (https://www.isae-supaero.fr/)
 * @package report_hybridmeter
 */
defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/install.php');

use report_hybridmeter\config;

/**
 * Upgrade plugin.
 * @param $oldversion
 * @return true
 */
function xmldb_report_hybridmeter_upgrade($oldversion) {
    make_dirs();

    if ($oldversion < 2022020103) {
        $config = config::get_instance();
        $config->update_coeffs(
            REPORT_HYBRIDMETER_USAGE_COEFFS,
            REPORT_HYBRIDMETER_DIGITALISATION_COEFFS
        );
        upgrade_plugin_savepoint(true, 2022020103, 'report', 'hybridmeter');
    }

    if ($oldversion < 2022021108) {
        rm_dir("/hybridmetrics");
        upgrade_plugin_savepoint(true, 2022021108, 'report', 'hybridmeter');
    }

    if ($oldversion < 2022092303) {
        config::get_instance()->update_excluded_data();
        upgrade_plugin_savepoint(true, 2022092303, 'report', 'hybridmeter');
    }

    return true;
}



