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
 * This file contains the backup code for the cdelement_poll plugin.
 *
 * @package   cdelement_poll
 * @copyright  2024 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Provides the information to backup poll contents.
 *
 * This just adds its filearea to the annotations and records the number of files.
 */
class backup_cdelement_poll_subplugin extends backup_subplugin {

    /**
     * Returns the subplugin information to attach to poll element
     * @return backup_subplugin_element
     */
    protected function define_contentdesigner_subplugin_structure() {
        $userinfo = $this->get_setting_value('userinfo');

        // Create XML elements.
        $subplugin = $this->get_subplugin_element(); // This is the root element.

        $subpluginwrapper = new backup_nested_element($this->get_recommended_name());
        $subpluginelement = new backup_nested_element('cdelement_poll', ['id'], [
            'contentdesignerid', 'title', 'visible', 'question', 'selectoptioncount', 'resulttype', 'updaterating',
            'submissionmessage', 'mandatory', 'timecreated', 'timemodified',
        ]);

        $options = new backup_nested_element('cdelementpoll_options');
        $option = new backup_nested_element('cdelement_poll_options', ['id'], [
            'pollid', 'text', 'timecreated', 'timemodified',
        ]);

        $answers = new backup_nested_element('cdelementpoll_answers');
        $answer = new backup_nested_element('cdelement_poll_answers', ['id'], [
            'pollid', 'userid', 'optionid', 'timemodified',
        ]);

        // Connect XML elements into the tree.
        $subplugin->add_child($subpluginwrapper);
        $subpluginwrapper->add_child($subpluginelement);

        $subpluginwrapper->add_child($options);
        $options->add_child($option);

        $subpluginwrapper->add_child($answers);
        $answers->add_child($answer);

        // Define sources.
        $subpluginelement->set_source_table('cdelement_poll', ['contentdesignerid' => backup::VAR_PARENTID]);

        $sql = 'SELECT * FROM {cdelement_poll_options} WHERE pollid IN (
            SELECT id FROM {cdelement_poll} WHERE contentdesignerid=:contentdesignerid
        )';

        $answersql = 'SELECT * FROM {cdelement_poll_answers} WHERE pollid IN (
            SELECT id FROM {cdelement_poll} WHERE contentdesignerid=:contentdesignerid
        )';

        $option->set_source_sql($sql, ['contentdesignerid' => backup::VAR_PARENTID]);

        // All the rest of elements only happen if we are including user info.
        if ($userinfo) {
            $answer->set_source_sql($answersql, ['contentdesignerid' => backup::VAR_PARENTID]);
            // Define id annotations.
            $answer->annotate_ids('user', 'userid');
        }

        return $subplugin;
    }
}
