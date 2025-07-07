<?php
defined('MOODLE_INTERNAL') || die();

function xmldb_aicodeassignment_upgrade($oldversion)
{
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2025070201) { // use a new version number

        // Define table
        $table = new xmldb_table('aicodeassignment');

        // Define fields to add
        $field1 = new xmldb_field('roleid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'someexistingfield');
        $field2 = new xmldb_field('allowedlanguages', XMLDB_TYPE_TEXT, null, null, null, null, null, 'roleid');

        // Add fields if they don't exist
        if (!$dbman->field_exists($table, $field1)) {
            $dbman->add_field($table, $field1);
        }

        if (!$dbman->field_exists($table, $field2)) {
            $dbman->add_field($table, $field2);
        }

        // Savepoint reached
        upgrade_mod_savepoint(true, 2025070201, 'aicodeassignment');
    }

    if ($oldversion < 2025070707) {
        $table = new xmldb_table('aicodeassignment');

        $field = new xmldb_field('solutioncode', XMLDB_TYPE_TEXT, null, null, null, null, null, 'aigeneratedjson');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2025070707, 'aicodeassignment');
    }
    if ($oldversion < 2025070708) {
        $table = new xmldb_table('aicodeassignment');

        $timestart = new xmldb_field('timestart', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'grade');
        if (!$dbman->field_exists($table, $timestart)) {
            $dbman->add_field($table, $timestart);
        }

        $timeend = new xmldb_field('timeend', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'timestart');
        if (!$dbman->field_exists($table, $timeend)) {
            $dbman->add_field($table, $timeend);
        }

        upgrade_mod_savepoint(true, 2025070708, 'aicodeassignment');
    }
    return true;
}
