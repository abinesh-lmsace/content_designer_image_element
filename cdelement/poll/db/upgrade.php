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
 * @package    cdelement_poll
 * @copyright  2024 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Handles the upgrade process for the cdelement_poll plugin in Moodle.
 *
 * @param int $oldversion The version of the plugin before the upgrade.
 * @param bool $rename whether to rename the existing table or not.
 * @return bool True on successful upgrade.
 */
function xmldb_cdelement_poll_upgrade($oldversion, $rename=false) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($rename) {

        // Rename the table element_poll to cdelement_poll.
        $table = new xmldb_table('element_poll');
        $cdelementtable = new xmldb_table('cdelement_poll');

        if ($dbman->table_exists($table)) {

            if (!$dbman->table_exists($cdelementtable)) {
                // Rename the existing table.
                $dbman->rename_table($table, 'cdelement_poll');
            } else {
                // Drop the existing table.
                $dbman->drop_table($cdelementtable);
                // Rename the existing table.
                $dbman->rename_table($table, 'cdelement_poll');
            }
        }

        $table = new xmldb_table('element_poll_options');
        $cdelementtable = new xmldb_table('cdelement_poll_options');

        if ($dbman->table_exists($table)) {

            if (!$dbman->table_exists($cdelementtable)) {
                // Rename the existing table.
                $dbman->rename_table($table, 'cdelement_poll_options');
            } else {
                // Drop the existing table.
                $dbman->drop_table($cdelementtable);
                // Rename the existing table.
                $dbman->rename_table($table, 'cdelement_poll_options');
            }
        }

        $table = new xmldb_table('element_poll_answers');
        $cdelementtable = new xmldb_table('cdelement_poll_answers');

        if ($dbman->table_exists($table)) {

            if (!$dbman->table_exists($cdelementtable)) {
                // Rename the existing table.
                $dbman->rename_table($table, 'cdelement_poll_answers');
            } else {
                // Drop the existing table.
                $dbman->drop_table($cdelementtable);
                // Rename the existing table.
                $dbman->rename_table($table, 'cdelement_poll_answers');
            }
        }

        // Rename admin configuration settings.
        mod_contentdesigner\plugininfo\cdelement::update_plugins_config('element_poll', 'cdelement_poll');

    }

    return true;
}
