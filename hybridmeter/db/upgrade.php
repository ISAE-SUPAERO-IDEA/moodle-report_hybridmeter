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

// This file is part of Moodle - http://moodle.org
//
//  Moodle is free software: you can redistribute it and/or modify
//  it under the terms of the GNU General Public License as published by
//  the Free Software Foundation, either version 3 of the License, or
//  (at your option) any later version.
//
//  Moodle is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU General Public License for more details.
//
//  You should have received a copy of the GNU General Public License
//  along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/install.php');
require_once(dirname(__FILE__).'/../classes/configurator.php');

use \report_hybridmeter\classes\configurator as configurator;

function xmldb_report_hybridmeter_upgrade($oldversion) {
    make_dirs();

    if($oldversion < 2022020103) {
        $configurator = configurator::get_instance();
        $configurator->unset_key("digitalisation_coeffs");
        $configurator->unset_key("usage_coeffs");
        
        $configurator->update_coeffs("usage_coeffs", REPORT_HYBRIDMETER_USAGE_COEFFS);
        $configurator->update_coeffs("digitalisation_coeffs", REPORT_HYBRIDMETER_DIGITALISATION_COEFFS);

        upgrade_plugin_savepoint(true, 2022020103, 'report', 'hybridmeter');
    }

    if($oldversion < 2022021108) {
        rm_dir("/hybridmetrics");
        upgrade_plugin_savepoint(true, 2022021108, 'report', 'hybridmeter');
    }

    if($oldversion < 2022092303) {
        $configurator = configurator::get_instance();
        $configurator->update_blacklisted_data();
        upgrade_plugin_savepoint(true, 2022092303, 'report', 'hybridmeter');
    }

    return true;
}

    
?>