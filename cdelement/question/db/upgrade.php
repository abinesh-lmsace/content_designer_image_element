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
 * Upgrade script for the question element.
 *
 * @package    cdelement_question
 * @copyright  2024 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrede question element.
 *
 * @param string $oldversion the version we are upgrading from.
 * @param bool $rename whether to rename the existing table or not.
 * @return bool
 */
function xmldb_cdelement_question_upgrade($oldversion, $rename=false) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($rename) {

        // Rename the table element_question to cdelement_question.
        $table = new xmldb_table('element_question');
        $cdelementtable = new xmldb_table('cdelement_question');

        if ($dbman->table_exists($table)) {

            if (!$dbman->table_exists($cdelementtable)) {
                // Rename the existing table.
                $dbman->rename_table($table, 'cdelement_question');
            } else {
                // Drop the existing table.
                $dbman->drop_table($cdelementtable);
                // Rename the existing table.
                $dbman->rename_table($table, 'cdelement_question');
            }
        }

        // Rename the table element_question to cdelement_question_attempts.
        $table = new xmldb_table('element_question_attempts');
        $cdelementtable = new xmldb_table('cdelement_question_attempts');

        if ($dbman->table_exists($table)) {

            if (!$dbman->table_exists($cdelementtable)) {
                // Rename the existing table.
                $dbman->rename_table($table, 'cdelement_question_attempts');
            } else {
                // Drop the existing table.
                $dbman->drop_table($cdelementtable);
                // Rename the existing table.
                $dbman->rename_table($table, 'cdelement_question_attempts');
            }
        }

        // Rename the table element_question to cdelement_question_slots.
        $table = new xmldb_table('element_question_slots');
        $cdelementtable = new xmldb_table('cdelement_question_slots');

        if ($dbman->table_exists($table)) {

            if (!$dbman->table_exists($cdelementtable)) {
                // Rename the existing table.
                $dbman->rename_table($table, 'cdelement_question_slots');
            } else {
                // Drop the existing table.
                $dbman->drop_table($cdelementtable);
                // Rename the existing table.
                $dbman->rename_table($table, 'cdelement_question_slots');
            }
        }

        // Rename admin configuration settings.
        mod_contentdesigner\plugininfo\cdelement::update_plugins_config('element_question', 'cdelement_question');

        // Update the component name of the existing question usages.
        $DB->set_field('question_usages', 'component', 'cdelement_question', ['component' => 'element_question']);
    }

    if ($oldversion < 2025022807 || $rename) {

        $table = new xmldb_table('cdelement_question_slots');
        $field = new xmldb_field('cdattemptid', XMLDB_TYPE_INTEGER, '18', null, XMLDB_NOTNULL, null, '0', 'userid');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field mark to be added to cdelement_question_attempts.
        $table = new xmldb_table('cdelement_question_attempts');
        $field = new xmldb_field('mark', XMLDB_TYPE_NUMBER, '10, 5', null, null, null, null, 'status');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define the index to be dropped.
        $index = new xmldb_index('contentdesignerid_2', XMLDB_INDEX_UNIQUE, ['contentdesignerid', 'userid']);

        // Check if the index exists before attempting to drop it.
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }

        // Contentdesigner table.
        $table = new xmldb_table('cdelement_question_attempts');
        $field = new xmldb_field('cdattemptid', XMLDB_TYPE_INTEGER, '18', null, null, null, '0', 'userid');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Element question table.
        $questiontable = new xmldb_table('cdelement_question');

        $preferredbehaviour = new xmldb_field('preferredbehaviour', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL,
            null, null, 'questionversion');

        if ($dbman->field_exists($questiontable, $preferredbehaviour)) {

            // Ensure existing NULL values are replaced with the default value.
            $DB->execute("UPDATE {cdelement_question} SET preferredbehaviour = '' WHERE preferredbehaviour IS NULL");

            // Change the field to NOT NULL.
            $dbman->change_field_notnull($questiontable, $preferredbehaviour);
        }

        // Element question attempts table.
        $questionattemptstable = new xmldb_table('cdelement_question_attempts');
        $field = new xmldb_field('cdattemptid', XMLDB_TYPE_INTEGER, '18', null, XMLDB_NOTNULL, null, '0', 'userid');

        if ($dbman->field_exists($questionattemptstable, $field)) {

            // Ensure existing NULL values are replaced with the default value.
            $DB->execute("UPDATE {cdelement_question_attempts} SET cdattemptid = '0' WHERE cdattemptid IS NULL");
            // Change the field to NOT NULL.
            $dbman->change_field_notnull($questionattemptstable, $field);
        }

        // Element question slots table.
        $questionslotstable = new xmldb_table('cdelement_question_slots');
        $cdattemptid = new xmldb_field('cdattemptid', XMLDB_TYPE_INTEGER, '18', null, XMLDB_NOTNULL, null, '0', 'userid');

        if ($dbman->field_exists($questionslotstable, $cdattemptid)) {
            // Ensure existing NULL values are replaced with the default value.
            $DB->execute("UPDATE {cdelement_question_slots} SET cdattemptid = '0' WHERE cdattemptid IS NULL");
            // Change the field to NOT NULL.
            $dbman->change_field_notnull($questionslotstable, $cdattemptid);
        }

        if (!$rename) {
            upgrade_plugin_savepoint(true, 2025022807, 'cdelement', 'question');
        }

    }

    return true;
}
