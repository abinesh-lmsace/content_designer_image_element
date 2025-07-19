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
 * This file contains the restore code for the cdelement_poll plugin.
 *
 * @package   cdelement_poll
 * @copyright  2024 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Restore subplugin class.
 *
 * Provides the necessary information needed to restore cdelement_poll subplugin.
 */
class restore_cdelement_poll_subplugin extends restore_subplugin {

    /**
     * Returns the paths to be handled by the subplugin at the course level.
     *
     * @return array
     */
    protected function define_contentdesigner_subplugin_structure() {

        // Define the virtual plugin element for the restore process.
        $paths = [];
        $userinfo = $this->get_setting_value('userinfo');

        // Add the cdelement_poll element (main element).
        $elpath = $this->get_pathfor('/cdelement_poll');
        $paths[] = new restore_path_element('cdelement_poll', $elpath);

        // Add the poll options and poll answers.
        $paths[] = new restore_path_element('cdelement_poll_options',
            $this->get_pathfor('/cdelementpoll_options/cdelement_poll_options'));
        if ($userinfo) {
            $paths[] = new restore_path_element('cdelement_poll_answers',
                $this->get_pathfor('/cdelementpoll_answers/cdelement_poll_answers'));
        }

        return $paths;
    }

    /**
     * Processes the cdelement_poll data to be restored.
     *
     * @param array $data
     */
    public function process_cdelement_poll($data) {
        global $DB;

        // Decode data (e.g., xml ids).
        $data = (object) $data;
        $oldid = $data->id;
        $data->contentdesignerid = $this->get_new_parentid('contentdesigner');

        // Insert the cdelement_poll data into the database.
        $newitemid = $DB->insert_record('cdelement_poll', $data);

        // Immediately after inserting, apply mapping for later reference (e.g., in case other elements refer to this poll).
        $this->set_mapping('poll_instanceid', $oldid, $newitemid);
        $this->add_related_files('mod_contentdesigner', 'pollelementbg', 'poll_instanceid', null, $oldid);
    }

    /**
     * Processes the poll option data to be restored.
     *
     * @param array $data
     */
    public function process_cdelement_poll_options($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;

        // Map the pollid to the newly restored pollid.
        $data->pollid = $this->get_mappingid('poll_instanceid', $data->pollid);

        // Insert the poll option data into the database.
        $newitemid = $DB->insert_record('cdelement_poll_options', $data);

        // Apply the mapping for the poll option (this may not be necessary, but good for reference).
        $this->set_mapping('poll_options_instance', $oldid, $newitemid);
    }

    /**
     * Processes the poll answer data to be restored.
     *
     * @param array $data
     */
    public function process_cdelement_poll_answers($data) {
        global $DB;

        $data = (object) $data;
        // Map the pollid and userid to the newly restored IDs.
        $data->pollid = $this->get_mappingid('poll_instanceid', $data->pollid);
        $data->optionid = $this->get_mappingid('poll_options_instance', $data->optionid);
        $data->userid = $this->get_mappingid('user', $data->userid);

        // Insert the poll answer data into the database.
        $DB->insert_record('cdelement_poll_answers', $data);
    }

}
