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
 * This file contains the backup code for the cdelement_rating plugin.
 *
 * @package   cdelement_rating
 * @copyright  2025 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Provides the information to backup rating contents.
 *
 * This just adds its filearea to the annotations and records the number of files.
 */
class backup_cdelement_rating_subplugin extends backup_subplugin {

    /**
     * Returns the subplugin information to attach to rating element
     * @return backup_subplugin_element
     */
    protected function define_contentdesigner_subplugin_structure() {
        $userinfo = $this->get_setting_value('userinfo');

        // Create XML elements.
        $subplugin = $this->get_subplugin_element();

        $subpluginwrapper = new backup_nested_element($this->get_recommended_name());
        $subpluginelement = new backup_nested_element('cdelement_rating', ['id'], [
            'contentdesignerid', 'title', 'visible', 'scale', 'numericcount', 'description',
            'changerating', 'variables', 'label', 'resulttype', 'mandatory', 'timecreated', 'timemodified',
        ]);

        $responses = new backup_nested_element('cdelementrating_responses');
        $response = new backup_nested_element('cdelement_rating_responses', ['id'], [
            'ratingid', 'userid', 'response', 'timemodified',
        ]);

        // Connect XML elements into the tree.
        $subplugin->add_child($subpluginwrapper);
        $subpluginwrapper->add_child($subpluginelement);

        $subpluginwrapper->add_child($responses);
        $responses->add_child($response);

        // Define sources.
        $subpluginelement->set_source_table('cdelement_rating', ['contentdesignerid' => backup::VAR_PARENTID]);

        $responsesql = 'SELECT * FROM {cdelement_rating_responses} WHERE ratingid IN (
            SELECT id FROM {cdelement_rating} WHERE contentdesignerid=:contentdesignerid
        )';

        // All the rest of elements only happen if we are including user info.
        if ($userinfo) {
            $response->set_source_sql($responsesql, ['contentdesignerid' => backup::VAR_PARENTID]);
            // Define id annotations.
            $response->annotate_ids('user', 'userid');
        }

        return $subplugin;
    }
}
