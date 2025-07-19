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
 * This file contains the backup code for the cdelement_question plugin.
 *
 * @package   cdelement_question
 * @copyright  2024 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/backup/moodle2/backup_stepslib.php');

/**
 * Provides the information to backup question contents.
 *
 * This just adds its filearea to the annotations and records the number of files.
 */
class backup_cdelement_question_subplugin extends backup_subplugin {

    use backup_questions_attempt_data_trait;
    use backup_question_reference_data_trait;

    /**
     * Returns the subplugin information to attach to question element
     * @return backup_subplugin_element
     */
    protected function define_contentdesigner_subplugin_structure() {

        $userinfo = $this->get_setting_value('userinfo');

        // Create XML elements.
        $subplugin = $this->get_subplugin_element();
        $subpluginwrapper = new backup_nested_element($this->get_recommended_name());
        $subpluginelement = new backup_nested_element('cdelement_question', ['id'], [
            'contentdesignerid', 'title', 'visible', 'categoryid', 'questionid',
            'questionversion', 'preferredbehaviour', 'mandatory', 'timecreated', 'timemodified',
        ]);

        $this->add_question_references($subpluginelement, 'cdelement_question', 'slot');

        $elementquestion = new backup_nested_element('elementquestionattempts');
        $elementquestionattempts = new backup_nested_element('cdelement_question_attempts', ['id'], [
            'contentdesignerid', 'userid', 'cdattemptid', 'uniqueid', 'status', 'timemodified',
        ]);

        // This module is using questions, so produce the related question states and sessions
        // attaching them to the $attempt element based in 'uniqueid' matching.
        $this->add_question_usages($elementquestionattempts, 'uniqueid');

        $attemptslots = new backup_nested_element('elementquestionslots');
        $elementquestionslots = new backup_nested_element('cdelement_question_slots', ['id'], [
            'instanceid', 'cdattemptid', 'userid', 'slot', 'status',
        ]);

        // Connect XML elements into the tree.
        $subplugin->add_child($subpluginwrapper);
        $subpluginwrapper->add_child($subpluginelement);

        // Element question.
        $subplugin->add_child($elementquestion);
        $elementquestion->add_child($elementquestionattempts);

        // Element question slots.
        $subplugin->add_child($attemptslots);
        $attemptslots->add_child($elementquestionslots);

        // Set source to populate the data.
        $subpluginelement->set_source_table('cdelement_question', ['contentdesignerid' => backup::VAR_PARENTID]);

        if ($userinfo) {

            $sql = 'SELECT * FROM {cdelement_question_attempts} WHERE contentdesignerid=:contentdesignerid';
            $elementquestionattempts->set_source_sql($sql, ['contentdesignerid' => backup::VAR_PARENTID]);
            $elementquestionattempts->annotate_ids('user', 'userid');

            // Question attempt slots.
            $sql = 'SELECT * FROM {cdelement_question_slots} WHERE instanceid IN (
                SELECT id FROM {cdelement_question} WHERE contentdesignerid=:contentdesignerid
            )';
            $elementquestionslots->set_source_sql($sql, ['contentdesignerid' => backup::VAR_PARENTID]);
            $elementquestionslots->annotate_ids('user', 'userid');
        }

        $subpluginelement->annotate_ids('question_instanceid', 'id');

        $subpluginelement->annotate_files('mod_contentdesigner', 'questionelementbg', null);

        return $subplugin;
    }

}
