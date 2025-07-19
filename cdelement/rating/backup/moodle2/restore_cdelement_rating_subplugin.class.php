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
 * This file contains the restore code for the cdelement_rating plugin.
 *
 * @package   cdelement_rating
 * @copyright  2025 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Restore subplugin class.
 *
 * Provides the necessary information needed to restore cdelement_rating subplugin.
 */
class restore_cdelement_rating_subplugin extends restore_subplugin {

    /**
     * Returns the paths to be handled by the subplugin at the course level.
     *
     * @return array
     */
    protected function define_contentdesigner_subplugin_structure() {

        // Define the virtual plugin element for the restore process.
        $paths = [];
        $userinfo = $this->get_setting_value('userinfo');

        // Add the cdelement_rating element (main element).
        $elpath = $this->get_pathfor('/cdelement_rating');
        $paths[] = new restore_path_element('cdelement_rating', $elpath);

        if ($userinfo) {
            $paths[] = new restore_path_element('cdelement_rating_responses',
                $this->get_pathfor('/cdelementrating_responses/cdelement_rating_responses'));
        }

        return $paths;
    }

    /**
     * Processes the cdelement_rating data to be restored.
     *
     * @param array $data
     */
    public function process_cdelement_rating($data) {
        global $DB;

        // Decode data (e.g., xml ids).
        $data = (object) $data;
        $oldid = $data->id;
        $data->contentdesignerid = $this->get_new_parentid('contentdesigner');

        // Insert the cdelement_rating data into the database.
        $newitemid = $DB->insert_record('cdelement_rating', $data);

        // Immediately after inserting, apply mapping for later reference (e.g., in case other elements refer to this rating).
        $this->set_mapping('rating_instanceid', $oldid, $newitemid);
        $this->add_related_files('mod_contentdesigner', 'ratingelementbg', 'rating_instanceid', null, $oldid);
    }

    /**
     * Processes the rating response data to be restored.
     *
     * @param array $data
     */
    public function process_cdelement_rating_responses($data) {
        global $DB;

        $data = (object) $data;
        // Map the ratingid and userid to the newly restored IDs.
        $data->ratingid = $this->get_mappingid('rating_instanceid', $data->ratingid);
        $data->userid = $this->get_mappingid('user', $data->userid);

        // Insert the rating answer data into the database.
        $DB->insert_record('cdelement_rating_responses', $data);
    }
}
