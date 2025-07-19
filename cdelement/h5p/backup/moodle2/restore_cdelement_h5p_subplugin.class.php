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
 * This file contains the restore code for the feedback_file plugin.
 *
 * @package    cdelement_h5p
 * @copyright  2022 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Restore subplugin class.
 *
 * Provides the necessary information needed to restore cdelement_h5p subplugin.
 */
class restore_cdelement_h5p_subplugin extends restore_subplugin {

    /**
     * Returns the paths to be handled by the subplugin.
     * @return array
     */
    protected function define_contentdesigner_subplugin_structure() {

        $paths = [];

        $elename = $this->get_namefor('instance');
        // We used get_recommended_name() so this works.
        $elepath = $this->get_pathfor('/cdelement_h5p');
        $paths[] = new restore_path_element($elename, $elepath);

        $userinfo = $this->get_setting_value('userinfo');
        if ($userinfo) {
            $paths[] = new restore_path_element('cdelement_h5p_xapistates',
            '/activity/contentdesigner/cdelementh5p_xapistates/cdelement_h5p_xapistates');
        }

        return $paths;
    }

    /**
     * Processes one cdelement_h5p element
     * @param mixed $data
     */
    public function process_cdelement_h5p_instance($data) {
        global $DB;

        $data = (object)$data;

        $oldinstance = $data->id;
        $data->contentdesignerid = $this->get_new_parentid('contentdesigner');
        $newinstance = $DB->insert_record('cdelement_h5p', $data);
        $this->set_mapping('h5p_instanceid', $oldinstance, $newinstance, true);

        // Add files need to restore.
        $this->add_related_files('cdelement_h5p', 'package', 'h5p_instanceid', null, $oldinstance);
        $this->add_related_files('mod_contentdesigner', 'h5pelementbg', 'h5p_instanceid', null, $oldinstance);
    }

    /**
     * Processes one cdelement_h5p element
     * @param mixed $data
     */
    public function process_cdelement_h5p_xapistates($data) {
        global $DB;

        $data = (object) $data;
        $data->itemid = $this->task->get_contextid();
        $data->userid = $this->get_mappingid('user', $data->userid);
        $h5pinstanceid = str_replace('cdelement_h5p-', '', $data->registration);
        $data->registration = 'cdelement_h5p-' . $this->get_mappingid('h5p_instanceid', $h5pinstanceid);
        $data->timemodified = time();

        $newinstance = $DB->insert_record('cdelement_h5p_xapistates', $data);
    }
}
