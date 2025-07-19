<?php
// This file is part of Moodle - http://moodle.org/
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
 * Upgrade script for Moodle.
 *
 * @package    cdelement_h5p
 * @copyright  2024 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Handles the upgrade process for the cdelement_h5p plugin in Moodle.
 *
 * This function checks the current version of the plugin and performs
 * necessary database schema updates if the version is less than the specified
 * upgrade version. It creates the 'cdelement_h5p_xapistates' table with the
 * required fields, keys, and indexes if it does not already exist.
 *
 * @param int $oldversion The version of the plugin before the upgrade.
 * @param bool $rename Due to changing the plugin name, we need to rename the existing table.
 * @return bool True on successful upgrade.
 */
function xmldb_cdelement_h5p_upgrade($oldversion, $rename=false) {
    global $DB, $SESSION;

    $dbman = $DB->get_manager();

    // Check if the plugin is renamed.
    // If the plugin is renamed, we need to rename the existing table without upgrade the save point.
    $runwithoutupgradpoint = $rename;

    // Verify the plugin is upgraded from a free version.
    // Check if the contentdesigner_completion table exists.
    // If it does not exist, it means the plugin is upgraded from a free version.
    if ($rename || (property_exists($SESSION, 'contentdesigner_upgradepro') && $SESSION->contentdesigner_upgradepro)) {
        $runwithoutupgradpoint = true;
    }

    if ($runwithoutupgradpoint) {
        // Rename the table element_h5p to cdelement_h5p.
        $h5ptable = new xmldb_table('element_h5p');
        $cdh5ptable = new xmldb_table('cdelement_h5p');

        if ($dbman->table_exists($h5ptable)) {

            if (!$dbman->table_exists($cdh5ptable)) {
                // Rename the existing table.
                $dbman->rename_table($h5ptable, 'cdelement_h5p');
            } else {
                // Drop the existing table.
                $dbman->drop_table($cdh5ptable);
                // Rename the existing table.
                $dbman->rename_table($h5ptable, 'cdelement_h5p');
            }
        }

        // Rename the table element_h5p_completion to cdelement_h5p_completion.
        $h5pcompletiontable = new xmldb_table('element_h5p_completion');
        $cdh5pcompletiontable = new xmldb_table('cdelement_h5p_completion');

        if ($dbman->table_exists($h5pcompletiontable)) {

            if (!$dbman->table_exists($cdh5pcompletiontable)) {
                // Rename the existing table.
                $dbman->rename_table($h5pcompletiontable, 'cdelement_h5p_completion');
            } else {
                // Drop the existing table.
                $dbman->drop_table($cdh5pcompletiontable);
                // Rename the existing table.
                $dbman->rename_table($h5pcompletiontable, 'cdelement_h5p_completion');
            }
        }

        // Rename the table element_h5p_xapistates to cdelement_h5p_xapistates.
        $h5pstatestable = new xmldb_table('element_h5p_xapistates');
        $cdh5pstatestable = new xmldb_table('cdelement_h5p_xapistates');

        if ($dbman->table_exists($h5pstatestable)) {

            if (!$dbman->table_exists($cdh5pstatestable)) {
                // Rename the existing table.
                $dbman->rename_table($h5pstatestable, 'cdelement_h5p_xapistates');
            } else {
                // Drop the existing table.
                $dbman->drop_table($cdh5pstatestable);
                // Rename the existing table.
                $dbman->rename_table($h5pstatestable, 'cdelement_h5p_xapistates');
            }
        }
        // Rename admin configuration settings.
        mod_contentdesigner\plugininfo\cdelement::update_plugins_config('element_h5p', 'cdelement_h5p', ['package']);
    }

    // Check if the version is less than the version where the table was added/modified.
    if ($oldversion < 2024110801 || $runwithoutupgradpoint) {

        // Define table cdelement_h5p_xapistates to be created.
        $table = new xmldb_table('cdelement_h5p_xapistates');

        // Adding fields to table cdelement_h5p_xapistates.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '18', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('component', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '18', null, null, null, null);
        $table->add_field('itemid', XMLDB_TYPE_INTEGER, '18', null, XMLDB_NOTNULL, null, null);
        $table->add_field('stateid', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('statedata', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('registration', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '18', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '18', null, null, null, null);

        // Adding keys to table cdelement_h5p_xapistates.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Adding indexes to table cdelement_h5p_xapistates.
        $table->add_index('mdl_xapistat_comite_ix', XMLDB_INDEX_NOTUNIQUE, ['component', 'itemid']);
        $table->add_index('mdl_xapistat_use_ix', XMLDB_INDEX_NOTUNIQUE, ['userid']);
        $table->add_index('mdl_xapistat_tim_ix', XMLDB_INDEX_NOTUNIQUE, ['timemodified']);

        // Conditionally launch create table for cdelement_h5p_xapistates.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        if (!$runwithoutupgradpoint) {
            // H5p savepoint reached.
            upgrade_plugin_savepoint(true, 2024110801, 'cdelement', 'h5p');
        }
    }

    if ($oldversion < 2025021306 || $runwithoutupgradpoint) {

        // Contentdesigner table.
        $table = new xmldb_table('cdelement_h5p_completion');
        $field = new xmldb_field('cdattemptid', XMLDB_TYPE_INTEGER, '18', null, XMLDB_NOTNULL, null, '0', 'userid');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Contentdesigner table.
        $table = new xmldb_table('cdelement_h5p');
        $field = new xmldb_field('maxscore', XMLDB_TYPE_CHAR, '255',  null, null, null, 0, 'mandatory');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Element question attempts table.
        $h5pcompletiontable = new xmldb_table('cdelement_h5p_completion');
        $field = new xmldb_field('cdattemptid', XMLDB_TYPE_INTEGER, '18', null, XMLDB_NOTNULL, null, '0', 'userid');

        if ($dbman->field_exists($h5pcompletiontable, $field)) {

            // Ensure existing NULL values are replaced with the default value.
            $DB->execute("UPDATE {cdelement_h5p_completion} SET cdattemptid = '0' WHERE cdattemptid IS NULL");
            // Change the field to NOT NULL.
            $dbman->change_field_notnull($h5pcompletiontable, $field);
        }

        if (!$runwithoutupgradpoint) {
            upgrade_plugin_savepoint(true, 2025021306, 'cdelement', 'h5p');
        }
    }

    return true;
}
