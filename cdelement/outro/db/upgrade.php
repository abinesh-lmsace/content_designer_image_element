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
 * Upgrade script for the outro element.
 *
 * @package    cdelement_outro
 * @copyright  2024 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrede outro element.
 *
 * @param string $oldversion the version we are upgrading from.
 * @param bool $rename whether to rename the existing table or not.
 * @return bool
 */
function xmldb_cdelement_outro_upgrade($oldversion, $rename=false) {
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

        // Rename the table element_outro to cdelement_outro.
        $table = new xmldb_table('element_outro');
        $cdelementtable = new xmldb_table('cdelement_outro');

        if ($dbman->table_exists($table)) {

            if (!$dbman->table_exists($cdelementtable)) {
                // Rename the existing table.
                $dbman->rename_table($table, 'cdelement_outro');
            } else {
                // Drop the existing table.
                $dbman->drop_table($cdelementtable);
                // Rename the existing table.
                $dbman->rename_table($table, 'cdelement_outro');
            }
        }

        // Rename admin configuration settings.
        mod_contentdesigner\plugininfo\cdelement::update_plugins_config('element_outro', 'cdelement_outro', [
            'outroimage', 'outrocontent']);
    }

    if ($oldversion < 2024110801 || $runwithoutupgradpoint) {

        // Element outro table.
        $table = new xmldb_table('cdelement_outro');

        // Outrocontent.
        $outrocontent = new xmldb_field('outrocontent', XMLDB_TYPE_TEXT, null, null, null, null, null, 'secondaryurl');
        // Outrocontent format.
        $outrocontentformat = new xmldb_field('outrocontentformat', XMLDB_TYPE_INTEGER, '2', null, null, null, null,
            'outrocontent');
        // Primary button.
        $primarybutton = new xmldb_field('primarybutton', XMLDB_TYPE_INTEGER, '9', null, XMLDB_NOTNULL, null,
            '0', 'outrocontentformat');
        // Secondary button.
        $secondarybutton = new xmldb_field('secondarybutton', XMLDB_TYPE_INTEGER, '9', null, XMLDB_NOTNULL, null,
            '0', 'primarybutton');

        if (!$dbman->field_exists($table, $outrocontent)) {
            $dbman->add_field($table, $outrocontent);
        }

        if (!$dbman->field_exists($table, $outrocontentformat)) {
            $dbman->add_field($table, $outrocontentformat);
        }

        if (!$dbman->field_exists($table, $primarybutton)) {
            $dbman->add_field($table, $primarybutton);
        }

        if (!$dbman->field_exists($table, $secondarybutton)) {
            $dbman->add_field($table, $secondarybutton);
        }

        if (!$runwithoutupgradpoint) {
            upgrade_plugin_savepoint(true, 2024110801, 'cdelement', 'outro');
        }
    }

    if ($oldversion < 2025021304 || $runwithoutupgradpoint) {

        // Element outro table.
        $table = new xmldb_table('cdelement_outro');

        // Outrocontent format.
        $outrocontentformat = new xmldb_field('outrocontentformat', XMLDB_TYPE_INTEGER, '4', null, null, null, '0',
            'outrocontent');

        // Launch change of default for field outrocontentformat.
        if ($dbman->field_exists($table, $outrocontentformat)) {
            $dbman->change_field_default($table, $outrocontentformat);
            $dbman->change_field_precision($table, $outrocontentformat);
        }

        // Primary button.
        $primarybutton = new xmldb_field('primarybutton', XMLDB_TYPE_INTEGER, '9', null, XMLDB_NOTNULL, null,
            '0', 'outrocontentformat');

        if ($dbman->field_exists($table, $primarybutton)) {

            // Ensure existing NULL values are replaced with the default value.
            $DB->execute("UPDATE {cdelement_outro} SET primarybutton = '0' WHERE primarybutton IS NULL");

            // Change the field to NOT NULL.
            $dbman->change_field_notnull($table, $primarybutton);
        }

        // Secondary button.
        $secondarybutton = new xmldb_field('secondarybutton', XMLDB_TYPE_INTEGER, '9', null, XMLDB_NOTNULL, null,
            '0', 'primarybutton');

        if ($dbman->field_exists($table, $secondarybutton)) {

            // Ensure existing NULL values are replaced with the default value.
            $DB->execute("UPDATE {cdelement_outro} SET secondarybutton = '0' WHERE secondarybutton IS NULL");

            // Change the field to NOT NULL.
            $dbman->change_field_notnull($table, $secondarybutton);
        }

        if (!$runwithoutupgradpoint) {
            upgrade_plugin_savepoint(true, 2025021304, 'cdelement', 'outro');
        }
    }

    return true;
}
