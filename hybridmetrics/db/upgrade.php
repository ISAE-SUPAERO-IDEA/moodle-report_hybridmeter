<?php

require_once(dirname(__FILE__).'/../constants.php');

function xmldb_report_hybridmetrics_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    $result = TRUE;

    if ($oldversion < 2021200806) {

        // Define table report_hybridmetrics_coeff to be created.
        $table = new xmldb_table('report_hybridmetrics_coeff');

        // Adding fields to table report_hybridmetrics_coeff.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('id_module', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('coefficient', XMLDB_TYPE_INTEGER, '3', null, null, null, null);

        // Adding keys to table report_hybridmetrics_coeff.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('unique_id_module', XMLDB_KEY_UNIQUE, array('id_module'));

        // Conditionally launch create table for report_hybridmetrics_coeff.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table report_hybridmetrics_blcat to be created.
        $table = new xmldb_table('report_hybridmetrics_blcat');

        // Adding fields to table report_hybridmetrics_blcat.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('id_category', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('blacklisted', XMLDB_TYPE_BINARY, null, null, null, null, null);

        // Adding keys to table report_hybridmetrics_blcat.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('unique_id_category', XMLDB_KEY_UNIQUE, array('id_category'));

        // Conditionally launch create table for report_hybridmetrics_blcat.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table report_hybridmetrics_coeff to be created.
        $table = new xmldb_table('report_hybridmetrics_coeff');

        // Adding fields to table report_hybridmetrics_coeff.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('id_module', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('coefficient', XMLDB_TYPE_INTEGER, '3', null, null, null, null);

        // Adding keys to table report_hybridmetrics_coeff.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('unique_id_module', XMLDB_KEY_UNIQUE, array('id_module'));

        // Conditionally launch create table for report_hybridmetrics_coeff.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

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

        upgrade_plugin_savepoint(true, 2021200806, 'report', 'hybridmetrics');
    }

    if ($oldversion < 2021250800) {

        // Define table report_hybridmetrics_logs to be created.
        $table = new xmldb_table('report_hybridmetrics_logs');

        // Adding fields to table report_hybridmetrics_logs.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('timestamp', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('backup_path', XMLDB_TYPE_TEXT, null, null, null, null, null);

        // Adding keys to table report_hybridmetrics_logs.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for report_hybridmetrics_logs.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Hybridmetrics savepoint reached.
        upgrade_plugin_savepoint(true, 2021250800, 'report', 'hybridmetrics');
    }

    if ($oldversion < 2021250801) {

        // Define table report_hybridmetrics_running to be created.
        $table = new xmldb_table('report_hybridmetrics_running');

        // Adding fields to table report_hybridmetrics_running.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('timestamp', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

        // Adding keys to table report_hybridmetrics_running.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for report_hybridmetrics_running.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Hybridmetrics savepoint reached.
        upgrade_plugin_savepoint(true, 2021250801, 'report', 'hybridmetrics');
    }

    if($oldversion < 2021300800){
        $table = new xmldb_table('report_hybridmetrics_blcours');
        $key = new xmldb_key('unique_id_course', XMLDB_KEY_UNIQUE, array('id_course'));

        $dbman->drop_key($table, $key);

        $key = new xmldb_key('foreign_id_course', XMLDB_KEY_FOREIGN_UNIQUE, array('id_course'), 'course', array('id'));

        // Launch add key foreign_id_course.
        $dbman->add_key($table, $key);

        $table = new xmldb_table('report_hybridmetrics_blcat');
        $key = new xmldb_key('unique_id_category', XMLDB_KEY_UNIQUE, array('id_category'));

        $dbman->drop_key($table, $key);

        $key = new xmldb_key('foreign_id_category', XMLDB_KEY_FOREIGN_UNIQUE, array('id_category'), 'course_categories', array('id'));

        // Launch add key foreign_id_category.
        $dbman->add_key($table, $key);

        $table = new xmldb_table('report_hybridmetrics_coeff');
        $key = new xmldb_key('unique_id_module', XMLDB_KEY_UNIQUE, array('id_module'));

        $dbman->drop_key($table, $key);

        $key = new xmldb_key('foreign_id_module', XMLDB_KEY_FOREIGN_UNIQUE, array('id_module'), 'modules', array('id'));

        // Launch add key foreign_id_module.
        $dbman->add_key($table, $key);

        // Hybridmetrics savepoint reached.
        upgrade_plugin_savepoint(true, 2021300800, 'report', 'hybridmetrics');


    }

    return $result;
}
?>