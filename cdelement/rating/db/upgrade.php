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
 * Element rating upgrade defined.
 *
 * @package   cdelement_rating
 * @copyright  2025 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrede rating element.
 *
 * @param string $oldversion the version we are upgrading from.
 * @return bool
 */
function xmldb_cdelement_rating_upgrade($oldversion) {

    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2025022502) {

        // Element rating table.
        $ratingtable = new xmldb_table('cdelement_rating');

        // Content designer id.
        $contentdesignerid = new xmldb_field('contentdesignerid', XMLDB_TYPE_INTEGER, '18', null, XMLDB_NOTNULL, null, null, 'id');
        $title = new xmldb_field('title', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'contentdesignerid');
        $visible = new xmldb_field('visible', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '1', 'title');
        $numericcount = new xmldb_field('numericcount', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '1', 'scale');
        $mandatory = new xmldb_field('mandatory', XMLDB_TYPE_INTEGER, '9', null, XMLDB_NOTNULL, null, '0', 'resulttype');

        if (!$dbman->field_exists($ratingtable, $contentdesignerid)) {
            $dbman->add_field($ratingtable, $contentdesignerid);
        }

        if (!$dbman->field_exists($ratingtable, $title)) {
            $dbman->add_field($ratingtable, $title);
        }

        if (!$dbman->field_exists($ratingtable, $visible)) {
            $dbman->add_field($ratingtable, $visible);
        }

        if (!$dbman->field_exists($ratingtable, $numericcount)) {
            $dbman->add_field($ratingtable, $numericcount);
        }

        if (!$dbman->field_exists($ratingtable, $mandatory)) {
            $dbman->add_field($ratingtable, $mandatory);
        }

        // Rating variables.
        $variables = new xmldb_table('cdelement_rating_variables');
        $timearchived = new xmldb_field('timearchived', XMLDB_TYPE_INTEGER, '18', null, null, '0');

        if (!$dbman->field_exists($variables, $timearchived)) {
            $dbman->add_field($variables, $timearchived);
        }

        // Element rating responses table.
        $table = new xmldb_table('cdelement_rating_responses');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null);
        $table->add_field('ratingid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'id');
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'ratingid');
        $table->add_field('response', XMLDB_TYPE_FLOAT, '10', null, XMLDB_NOTNULL, null, '0', 'userid');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'response');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('ratingid', XMLDB_KEY_FOREIGN, ['ratingid'], 'cdelement_rating', ['id']);
        $table->add_index('userid', XMLDB_INDEX_NOTUNIQUE, ['userid']);

        // Conditionally launch create table for cdelement_rating_responses .
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2025022502, 'cdelement', 'rating');

    }

    if ($oldversion < 2025022512) {

        // Element rating responses table.
        $table = new xmldb_table('cdelement_rating_responses');

        // Time modified.
        $timemodified = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'response');

        if ($dbman->field_exists($table, $timemodified)) {

            // Ensure existing NULL values are replaced with the default value.
            $DB->execute("UPDATE {cdelement_rating_responses} SET timemodified = '0' WHERE timemodified IS NULL");

            $dbman->change_field_default($table, $timemodified);

            // Change the field to NOT NULL.
            $dbman->change_field_notnull($table, $timemodified);
        }

        // Define the unique index to be dropped.
        $oldindex = new xmldb_index('userid', XMLDB_INDEX_UNIQUE, ['userid']);

        // Drop the unique index if it exists.
        if ($dbman->index_exists($table, $oldindex)) {
            $dbman->drop_index($table, $oldindex);
        }

        // Define the new non-unique index.
        $newindex = new xmldb_index('cdelratiresp_use_ix', XMLDB_INDEX_NOTUNIQUE, ['userid']);

        // Add the non-unique index if it does not exist.
        if (!$dbman->index_exists($table, $newindex)) {
            $dbman->add_index($table, $newindex);
        }

        $vartable = new xmldb_table('cdelement_rating_variables');
        $timearchived = new xmldb_field('timearchived', XMLDB_TYPE_INTEGER, '18', null, null, '0');
        // Verify field exists.
        if ($dbman->field_exists($vartable, $timearchived)) {
            // Change the field.
            $dbman->change_field_precision($vartable, $timearchived);
        }

        // Element rating table.
        $ratingtable = new xmldb_table('cdelement_rating');
        $title = new xmldb_field('title', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'contentdesignerid');

        // Conditionally launch change of nullability.
        if ($dbman->field_exists($ratingtable, $title)) {
            $dbman->change_field_type($ratingtable, $title);
        }

        upgrade_plugin_savepoint(true, 2025022512, 'cdelement', 'rating');

    }

    if ($oldversion < 2025022515) {

        $table = new xmldb_table('cdelement_rating_responses');

        // Add timecreated.
        $timecreated = new xmldb_field('timecreated', XMLDB_TYPE_INTEGER, '18', null, null, null, '0', 'timemodified');
        if (!$dbman->field_exists($table, $timecreated)) {
            $dbman->add_field($table, $timecreated);
        }

        upgrade_plugin_savepoint(true, 2025022515, 'cdelement', 'rating');

    }

    if ($oldversion < 2025022516) {

        $table = new xmldb_table('cdelement_rating');

        $description = new xmldb_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null, 'numericcount');
        $content = new xmldb_field('content', XMLDB_TYPE_TEXT, null, null, null, null, null, 'numericcount');
        // If the description field exists, rename it to content.
        if ($dbman->field_exists($table, $description) && !$dbman->field_exists($table, $content)) {
            $dbman->rename_field($table, $description, 'content');
        }

        upgrade_plugin_savepoint(true, 2025022516, 'cdelement', 'rating');

    }

    return true;
}
