<?php

require_once(dirname(__FILE__).'/install.php');
require_once(dirname(__FILE__).'/../classes/configurator.php');

use \report_hybridmeter\classes\configurator as configurator;

function xmldb_report_hybridmeter_upgrade($oldversion) {
    make_dirs();

    if($oldversion < 2022020103) {
        $configurator = configurator::getInstance();
        $configurator->unset_key("static_coeffs");
        $configurator->unset_key("dynamic_coeffs");
        
        $configurator->update_coeffs("dynamic_coeffs", COEFF_DYNAMIQUES);
		$configurator->update_coeffs("static_coeffs", COEFF_STATIQUES);

        upgrade_plugin_savepoint(true, 2022020103, 'report', 'hybridmeter');
    }

    if($oldversion < 2022021101) {
        rm_dir("/hybridmetrics");
        upgrade_plugin_savepoint(true, 2022021101, 'report', 'hybridmeter');
    }

    return true;
}

    
?>