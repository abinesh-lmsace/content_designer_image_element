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
 * This file contains the restore code for the cdelement_tableofcontents plugin.
 *
 * @package   cdelement_tableofcontents
 * @copyright  2024 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Restore subplugin class.
 *
 * Provides the necessary information needed to restore tableofcontents element subplugin.
 */
class restore_cdelement_tableofcontents_subplugin extends restore_subplugin {

    /**
     * Returns the paths to be handled by the subplugin.
     *
     * @return array
     */
    protected function define_contentdesigner_subplugin_structure() {

        $paths = [];

        $elename = $this->get_namefor('instance');
        // We used get_recommended_name() so this works.
        $elepath = $this->get_pathfor('/cdelement_tableofcontents');
        $paths[] = new restore_path_element($elename, $elepath);

        $paths[] = new restore_path_element('cdelement_tableofcontentsopitons',
            '/activity/contentdesigner/cdelement_tableofcontentsopitons/cdelement_tableofcontents_options');

        return $paths;
    }

    /**
     * Processes one tableofcontents element instance.
     *
     * @param array $data
     */
    public function process_cdelement_tableofcontents_instance($data) {
        global $DB;

        $data = (object)$data;

        $oldinstance = $data->id;
        $data->contentdesignerid = $this->get_new_parentid('contentdesigner');

        // The mapping is set in the restore for the tableofcontents element instance.
        $newinstance = $DB->insert_record('cdelement_tableofcontents', $data);
        $this->set_mapping('tableofcontents_instanceid', $oldinstance, $newinstance, true);
        $this->add_related_files('mod_contentdesigner', 'tableofcontentselementbg', 'tableofcontents_instanceid', null,
            $oldinstance);
    }

    /**
     * Process the tableofcontents options.
     *
     * @param array $data
     */
    public function process_cdelement_tableofcontentsopitons($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        // Map the tableofcontentid to the newly restored tableofcontentid.
        $data->instance = $this->get_mappingid('tableofcontents_instanceid', $data->instance);
        $data->timecreated = time();

        // Insert the general options for the tableofcontents element instance.
        $DB->insert_record('contentdesigner_options', $data);
    }

    /**
     * Restore the editor images after the instance executed.
     *
     * @return void
     */
    public function after_execute_contentdesigner() {
        $this->add_related_files('mod_contentdesigner', 'cdelement_tableofcontents_intro', 'tableofcontents_instanceid');
    }
}
