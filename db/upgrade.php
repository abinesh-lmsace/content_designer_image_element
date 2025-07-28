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
 * Upgrade script for the contentdesigner.
 *
 * @package    mod_contentdesigner
 * @copyright  2022 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade contentdesigner module.
 *
 * @param string $oldversion the version we are upgrading from.
 */
function xmldb_contentdesigner_upgrade($oldversion) {
    global $DB, $CFG, $SESSION;

    $dbman = $DB->get_manager();

    // Verify the plugin is upgraded from a free version.
    $upgradepro = false;

    // Check if the contentdesigner_completion table exists.
    // If it does not exist, it means the plugin is upgraded from a free version.
    if (!$dbman->table_exists('contentdesigner_completion')) {
        // Upgrade from free version.
        $upgradepro = true;
        $SESSION->contentdesigner_upgradepro = true;
    }

    if ($oldversion < 2024092308 || $upgradepro) {

        // Contentdesigner table.
        $table = new xmldb_table('contentdesigner');

        // Completion endreach condition.
        $completionendreach = new xmldb_field('completionendreach', XMLDB_TYPE_INTEGER, '4', null, null, null, '0', 'introformat');
        if (!$dbman->field_exists($table, $completionendreach)) {
            $dbman->add_field($table, $completionendreach);
        }

        // Navigation Method.
        $navigation = new xmldb_field('navigation', XMLDB_TYPE_INTEGER, '4', null, null, null, '0', 'completionendreach');
        if (!$dbman->field_exists($table, $navigation)) {
            $dbman->add_field($table, $navigation);
        }

        // Completion mandatory condition.
        $completionmandatory = new xmldb_field('completionmandatory', XMLDB_TYPE_INTEGER, '4', null, null, null, '0', 'navigation');
        if (!$dbman->field_exists($table, $completionmandatory)) {
            $dbman->add_field($table, $completionmandatory);
        }

        // No need to run the upgrade savepoint if the plugin is upgraded from a free version.
        // It makes conflict with the pro version upgrade.
        if (!$upgradepro) {
            upgrade_mod_savepoint(true, 2024092308, 'contentdesigner');
        }
    }

    if ($oldversion < 2024110804 || $upgradepro) {

        // Contentdesigner table.
        $table = new xmldb_table('contentdesigner');

        // Added completion endreach condition.
        $completionendreachfield = new xmldb_field('completionendreach', XMLDB_TYPE_INTEGER, '4', null, null, null,
            '0', 'introformat');
        if (!$dbman->field_exists($table, $completionendreachfield)) {
            $dbman->add_field($table, $completionendreachfield);
        }

        // Add navigation Method.
        $navigationfield = new xmldb_field('navigation', XMLDB_TYPE_INTEGER, '4', null, null, null, '0', 'completionendreach');
        if (!$dbman->field_exists($table, $navigationfield)) {
            $dbman->add_field($table, $navigationfield);
        }

        // Add completion mandatory condition.
        $completionmandatoryfield = new xmldb_field('completionmandatory', XMLDB_TYPE_INTEGER, '4', null, null, null,
            '0', 'navigation');
        if (!$dbman->field_exists($table, $completionmandatoryfield)) {
            $dbman->add_field($table, $completionmandatoryfield);
        }

        // Define fields to be added to contentdesigner_completion.
        $table = new xmldb_table('contentdesigner_completion');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '18', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null);
        $table->add_field('contentdesignerid', XMLDB_TYPE_INTEGER, '18', null, null, null, null, 'id');
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '18', null, null, null, null, 'contentdesignerid');
        $table->add_field('completion', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0', 'userid');
        $table->add_field('mandatorycompletion', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0', 'completion');
        $table->add_field('starttime', XMLDB_TYPE_INTEGER, '18', null, XMLDB_NOTNULL, null, '0', 'mandatorycompletion');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '18', null, null, null, null, 'starttime');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table.
        if (!$dbman->table_exists('contentdesigner_completion')) {
            $dbman->create_table($table);
        }

        // Contentdesigner completion table.
        $table = new xmldb_table('contentdesigner_completion');

        // Changing the default of field completion on table contentdesigner completion to 0.
        $completion = new xmldb_field('completion', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0', 'userid');
        // Launch change of default for field completion.
        if (!$dbman->field_exists($table, $completion)) {
            $dbman->change_field_default($table, $completion);
        }

        // Completion mandatory elements.
        $mandatorycompletion = new xmldb_field('mandatorycompletion', XMLDB_TYPE_INTEGER, '2', null,
            XMLDB_NOTNULL, null, '0', 'completion');
        if (!$dbman->field_exists($table, $mandatorycompletion)) {
            $dbman->add_field($table, $mandatorycompletion);
        }

        // Start time.
        $starttime = new xmldb_field('starttime', XMLDB_TYPE_INTEGER, '18', null,
            XMLDB_NOTNULL, null, '0', 'mandatorycompletion');
        if (!$dbman->field_exists($table, $starttime)) {
            $dbman->add_field($table, $starttime);
        }

        // Don't run the upgrade savepoint if the plugin is upgraded from a free version.
        if (!$upgradepro) {
            upgrade_mod_savepoint(true, 2024110804, 'contentdesigner');
        }
    }

    if ($oldversion < 2024110813 || $upgradepro) {

        $optionstable = new xmldb_table('contentdesigner_options');
        $delay = new xmldb_field('delay', XMLDB_TYPE_CHAR, '10', null, XMLDB_NOTNULL, null, '0', 'duration');

        if ($dbman->field_exists($optionstable, $delay)) {
            $dbman->change_field_type($optionstable, $delay);
        }

        // Don't run the upgrade savepoint if the plugin is upgraded from a free version.
        if (!$upgradepro) {
            upgrade_mod_savepoint(true, 2024110813, 'contentdesigner');
        }
    }

    if ($oldversion < 2025011800 || $upgradepro) {

        // Contentdesigner table updates.
        $table = new xmldb_table('contentdesigner');

        // Define field grade to be added to contentdesigner.
        $field = new xmldb_field('grade', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '100', 'timemodified');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field grademethod to be added to contentdesigner.
        $field = new xmldb_field('grademethod', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '1', 'grade');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field attempts to be added to contentdesigner.
        $field = new xmldb_field('attemptsallowed', XMLDB_TYPE_INTEGER, '6', null, XMLDB_NOTNULL, null, '0', 'grademethod');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define table contentdesigner_attempts to be created.
        $table = new xmldb_table('contentdesigner_attempts');
        // Adding fields to table contentdesigner_attempts.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('contentdesignerid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('attempt', XMLDB_TYPE_INTEGER, '6', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('grade', XMLDB_TYPE_NUMBER, '10, 5', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timestart', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timefinish', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        // Adding keys to table contentdesigner_attempts.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('contentdesignerid', XMLDB_KEY_FOREIGN, ['contentdesignerid'], 'contentdesigner', ['id']);
        // Adding indexes to table contentdesigner_attempts.
        $table->add_index('userid', XMLDB_INDEX_NOTUNIQUE, ['userid']);
        // Conditionally launch create table for contentdesigner_attempts.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Don't run the upgrade savepoint if the plugin is upgraded from a free version.
        if (!$upgradepro) {
            // Contentdesigner savepoint reached.
            upgrade_mod_savepoint(true, 2025011800, 'contentdesigner');
        }
    }

    if ($oldversion < 2025013000 || $upgradepro) {
        // Contentdesigner table updates.
        $table = new xmldb_table('contentdesigner_options');

        // Define field grade to be added to contentdesigner.
        $field = new xmldb_field('excludeungraded', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0', 'hidemobile');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Don't run the upgrade savepoint if the plugin is upgraded from a free version.
        if (!$upgradepro) {
            // Contentdesigner savepoint reached.
            upgrade_mod_savepoint(true, 2025013000, 'contentdesigner');
        }
    }

    if ($oldversion < 2025021110 && !$upgradepro) {

        if (class_exists('\core\output\progress_trace\progress_trace_buffer')) {
            // Uninstall the plugin addon_report, this will remove the missing from the disk issue.
            $progress = new \core\output\progress_trace\progress_trace_buffer(
                new \core\output\progress_trace\text_progress_trace(), false);
        } else {
            require_once($CFG->libdir.'/weblib.php');
            $progress = new progress_trace_buffer(new text_progress_trace(), false);
        }
        // Uninstall the plugin addon_report, this will remove the missing from the disk issue.
        if (class_exists('\core\plugin_manager')) {
            \core\plugin_manager::instance()->uninstall_plugin('addon_report', $progress);
        } else {
            \core_plugin_manager::instance()->uninstall_plugin('addon_report', $progress);
        }

        // Contentdesigner savepoint reached.
        upgrade_mod_savepoint(true, 2025021110, 'contentdesigner');

    }

    if ($oldversion < 2025021301 || $upgradepro) {

        // Contentdesigner table updates.
        $table = new xmldb_table('contentdesigner');
        // Define field grade to be added to contentdesigner.
        $field = new xmldb_field('enablegrading', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0', 'timemodified');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('autostartattempts', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0', 'timemodified');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field attempts to be added to contentdesigner.
        $field = new xmldb_field('attempts', XMLDB_TYPE_INTEGER, '6', null, XMLDB_NOTNULL, null, '0', 'grademethod');
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'attemptsallowed');
        }

        // Don't run the upgrade savepoint if the plugin is upgraded from a free version.
        if (!$upgradepro) {
            // Contentdesigner savepoint reached.
            upgrade_mod_savepoint(true, 2025021301, 'contentdesigner');
        }
    }

    if ($oldversion < 2025021302 || $upgradepro) {

        $optionstable = new xmldb_table('contentdesigner_options');
        $delay = new xmldb_field('delay', XMLDB_TYPE_CHAR, '10', null, XMLDB_NOTNULL, null, '0', 'duration');

        if ($dbman->field_exists($optionstable, $delay)) {

            // Ensure existing NULL values are replaced with the default value.
            $DB->execute("UPDATE {contentdesigner_options} SET delay = '0' WHERE delay IS NULL");

            // Change the field to NOT NULL.
            $dbman->change_field_notnull($optionstable, $delay);
        }

        // Contentdesigner table updates.
        $table = new xmldb_table('contentdesigner');
        $enablegrading = new xmldb_field('enablegrading', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0', 'timemodified');
        $autostartattempts = new xmldb_field('autostartattempts', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null,
            '0', 'timemodified');

        if ($dbman->field_exists($table, $enablegrading)) {
            $dbman->change_field_precision($table, $enablegrading);
        }

        if ($dbman->field_exists($table, $autostartattempts)) {
            $dbman->change_field_precision($table, $autostartattempts);
        }

        // Don't run the upgrade savepoint if the plugin is upgraded from a free version.
        if (!$upgradepro) {
            upgrade_mod_savepoint(true, 2025021302, 'contentdesigner');
        }
    }

    if ($oldversion < 2025060703 || $upgradepro) {
        // Add description field.
        $table = new xmldb_table('contentdesigner_options');
        $field = new xmldb_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null, 'instance');
        if (!$dbman->field_exists($table, 'description')) {
            $dbman->add_field($table, $field);
        }

        // Add descriptionformat field.
        $field = new xmldb_field('descriptionformat', XMLDB_TYPE_INTEGER, '4', null, null, null, '1', 'description');
        if (!$dbman->field_exists($table, 'descriptionformat')) {
            $dbman->add_field($table, $field);
        }

        // Add showdescription field.
        $field = new xmldb_field('showdescription', XMLDB_TYPE_INTEGER, '4', null, null, null, '1', 'descriptionformat');
        if (!$dbman->field_exists($table, 'showdescription')) {
            $dbman->add_field($table, $field);
        }

        if (!$upgradepro) {
            // Contentdesigner savepoint reached.
            upgrade_mod_savepoint(true, 2025060703, 'contentdesigner');
        }
    }

    return true;
}
