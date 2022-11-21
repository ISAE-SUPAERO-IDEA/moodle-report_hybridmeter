<?php

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