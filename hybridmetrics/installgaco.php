<?php

defined('MOODLE_INTERNAL') || die();

/**
 * Post-install script
 */
function xmldb_report_hybridmetrics_install() {
    global $DB;

    $modules = $DB->get_records_sql("select id as id_module from mdl_modules");

    foreach($modules as &$module){
        $module["coefficient"] = (
            array_key_exists($module["id_module"], COEFF_STATIQUES) ?
            COEFF_STATIQUES[$module["id_module"]] : 1
        );
    }

    $DB->insert_records("mdl_modules", $modules);
}
