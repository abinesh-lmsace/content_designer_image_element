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
 * Upgrade script for the chapter element.
 *
 * @package    cdelement_chapter
 * @copyright  2024 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrede chapter element.
 *
 * @param string $oldversion the version we are upgrading from.
 * @param bool $rename Due to changing the plugin name, we need to rename the existing table.
 * @return bool
 */
function xmldb_cdelement_chapter_upgrade($oldversion, $rename=false) {
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
        // Rename the table local_pulsepro_credits to pulseaddon_credits.
        $table = new xmldb_table('element_chapter');
        $cdchaptertable = new xmldb_table('cdelement_chapter');

        if ($dbman->table_exists($table)) {

            if (!$dbman->table_exists($cdchaptertable)) {
                // Rename the existing table.
                $dbman->rename_table($table, 'cdelement_chapter');
            } else {
                // Drop the existing table.
                $dbman->drop_table($cdchaptertable);
                // Rename the existing table.
                $dbman->rename_table($table, 'cdelement_chapter');
            }
        }

        // Rename the table local_pulsepro_credits to pulseaddon_credits.
        $completiontable = new xmldb_table('element_chapter_completion');
        $cdcompletiontable = new xmldb_table('cdelement_chapter_completion');

        if ($dbman->table_exists($completiontable)) {

            if (!$dbman->table_exists($cdcompletiontable)) {
                // Rename the existing table.
                $dbman->rename_table($completiontable, 'cdelement_chapter_completion');
            } else {
                // Drop the existing table.
                $dbman->drop_table($cdcompletiontable);
                // Rename the existing table.
                $dbman->rename_table($completiontable, 'cdelement_chapter_completion');
            }
        }

        // Rename admin configuration settings.
        mod_contentdesigner\plugininfo\cdelement::update_plugins_config('element_chapter', 'cdelement_chapter');
    }

    if ($oldversion < 2024110801 || $runwithoutupgradpoint) {

        // Element chapter table.
        $table = new xmldb_table('cdelement_chapter');

        // Title status.
        $titlestatus = new xmldb_field('titlestatus', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0', 'position');
        if (!$dbman->field_exists($table, $titlestatus)) {
            $dbman->add_field($table, $titlestatus);
        }

        // Chapter completion.
        $completionmode = new xmldb_field('completionmode', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0', 'titlestatus');
        if (!$dbman->field_exists($table, $completionmode)) {
            $dbman->add_field($table, $completionmode);
        }

        // Don't run the upgrade savepoint if the plugin is renamed.
        if (!$runwithoutupgradpoint) {
            upgrade_plugin_savepoint(true, 2024110801, 'cdelement', 'chapter');
        }
    }

    if ($oldversion < 2025021303 || $runwithoutupgradpoint) {

        // Element chapter table.
        $table = new xmldb_table('cdelement_chapter');

        // Title status.
        $titlestatus = new xmldb_field('titlestatus', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0', 'position');

        if ($dbman->field_exists($table, $titlestatus)) {
            // Ensure existing NULL values are replaced with the default value.
            $DB->execute("UPDATE {cdelement_chapter} SET titlestatus = '0' WHERE titlestatus IS NULL");
            // Change the field to NOT NULL.
            $dbman->change_field_notnull($table, $titlestatus);
        }

        // Chapter completion mode.
        $completionmode = new xmldb_field('completionmode', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0', 'titlestatus');
        if ($dbman->field_exists($table, $completionmode)) {
            // Ensure existing NULL values are replaced with the default value.
            $DB->execute("UPDATE {cdelement_chapter} SET completionmode = '0' WHERE completionmode IS NULL");
            // Change the field to NOT NULL.
            $dbman->change_field_notnull($table, $completionmode);
        }

        // Don't run the upgrade savepoint if the plugin is renamed.
        if (!$runwithoutupgradpoint) {
            upgrade_plugin_savepoint(true, 2025021303, 'cdelement', 'chapter');
        }
    }

    if ($oldversion < 2025050601 || $runwithoutupgradpoint) {
        // Define field learningtools to be added to cdelement_chapter.
        $table = new xmldb_table('cdelement_chapter');
        $field = new xmldb_field('learningtools', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'visible');
        // Add field if it doesn't already exist.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        // Set the default value for existing records.
        $DB->set_field('cdelement_chapter', 'learningtools', 0);
        // Don't run the upgrade savepoint if the plugin is renamed.
        if (!$runwithoutupgradpoint) {
            // Save new version.
            upgrade_plugin_savepoint(true, 2025050601, 'cdelement', 'chapter');
        }
    }

    return true;
}
