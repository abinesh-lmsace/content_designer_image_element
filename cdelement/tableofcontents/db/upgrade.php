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
 * @package    cdelement_tableofcontents
 * @copyright  2024 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Handles the upgrade process for the cdelement_tableofcontents plugin in Moodle.
 *
 * @param int $oldversion The version of the plugin before the upgrade.
 * @param bool $rename whether to rename the existing table or not.
 * @return bool True on successful upgrade.
 */
function xmldb_cdelement_tableofcontents_upgrade($oldversion, $rename=false) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($rename) {

        // Rename the table element_tableofcontents to cdelement_tableofcontents.
        $table = new xmldb_table('element_tableofcontents');
        $cdelementtable = new xmldb_table('cdelement_tableofcontents');

        if ($dbman->table_exists($table)) {

            if (!$dbman->table_exists($cdelementtable)) {
                // Rename the existing table.
                $dbman->rename_table($table, 'cdelement_tableofcontents');
            } else {
                // Drop the existing table.
                $dbman->drop_table($cdelementtable);
                // Rename the existing table.
                $dbman->rename_table($table, 'cdelement_tableofcontents');
            }
        }

        // Rename admin configuration settings.
        mod_contentdesigner\plugininfo\cdelement::update_plugins_config('element_tableofcontents', 'cdelement_tableofcontents', [
            'intro']);
    }

    return true;
}
