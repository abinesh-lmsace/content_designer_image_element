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
 * Installation script that inserts the element in the elements list.
 *
 * @package   cdelement_videotime
 * @copyright  2024 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Install script, runs during the plugin installtion.
 *
 * @return bool
 */
function xmldb_cdelement_videotime_install() {
    global $DB;

    $shortname = \cdelement_videotime\element::SHORTNAME;
    $result = \mod_contentdesigner\elements::insertelement($shortname);

    // Drop the table if it exists, rename the existing table.
    $dbman = $DB->get_manager();
    $table = new xmldb_table('element_videotime');

    if ($dbman->table_exists($table)) {
        require_once(__DIR__ . '/upgrade.php');
        xmldb_cdelement_videotime_upgrade(0, true);
    }

    return $result ? true : false;
}
