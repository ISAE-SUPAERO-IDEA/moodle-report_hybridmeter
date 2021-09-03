<?php

defined('MOODLE_INTERNAL') || die();

/**
 * Post-install script
 */
function xmldb_report_hybridmetrics_install() {
    global $CFG, $DB;

    $DB->delete_records('report_hybridmetrics_coeff');

    $modules = array_map(function($m){
        $module = array();
        $module["id_module"] = $m->id;
        $module["coefficient"] = (
            array_key_exists($m->name, COEFF_STATIQUES) ?
            COEFF_STATIQUES[$m->name] : 1
        );
        return $module;
    },
    $DB->get_records_sql("select id, name from ".$DB->get_prefix()."modules")
    );

    $DB->insert_records("report_hybridmetrics_coeff", $modules);
}
