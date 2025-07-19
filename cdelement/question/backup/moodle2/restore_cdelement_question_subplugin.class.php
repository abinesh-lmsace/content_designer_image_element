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
 * This file contains the restore code for the cdelement_question plugin.
 *
 * @package   cdelement_question
 * @copyright  2024 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Restore subplugin class.
 *
 * Provides the necessary information needed to restore cdelement_question subplugin.
 */
class restore_cdelement_question_subplugin extends restore_subplugin {

    /** @var stdclass $currentquestionattempt Current question attempt. */
    protected $currentquestionattempt;

    use restore_questions_attempt_data_trait;
    use restore_question_reference_data_trait;

    /**
     * Returns the paths to be handled by the subplugin.
     * @return array
     */
    protected function define_contentdesigner_subplugin_structure() {

        $paths = [];

        $userinfo = $this->get_setting_value('userinfo');

        $elename = $this->get_namefor('instance');
        // We used get_recommended_name() so this works.
        $elepath = $this->get_pathfor('/cdelement_question');
        $elementrestore = new restore_path_element($elename, $elepath);
        $paths[] = $elementrestore;

        $this->add_question_references($elementrestore, $paths);

        if ($userinfo) {
            $questionattempt = new restore_path_element('cdelement_question_attempts',
                '/activity/contentdesigner/cdelementquestionattempts/cdelement_question_attempts');
            $paths[] = $questionattempt;
            // Add the user question attempts.
            $this->add_question_usages($questionattempt, $paths);

            $attemptslots = new restore_path_element('cdelement_question_slots',
                '/activity/contentdesigner/cdelementquestionslots/cdelement_question_slots');
            $paths[] = $attemptslots;
        }

        return $paths;
    }

    /**
     * Processes one question element instance
     * @param array $data
     */
    public function process_cdelement_question_instance($data) {
        global $DB;

        $data = (object) $data;

        $oldinstance = $data->id;
        $data->contentdesignerid = $this->get_new_parentid('contentdesigner');
        $data->questionid = $this->get_mappingid('question', $data->questionid) ?: $data->questionid;
        $data->categoryid = $this->get_mappingid('question_category', $data->categoryid);

        // The mapping is set in the restore for the question element instance.
        $newinstance = $DB->insert_record('cdelement_question', $data);
        $this->set_mapping('question_instanceid', $oldinstance, $newinstance, true);

        $this->add_related_files('mod_contentdesigner', 'questionelementbg', 'question_instanceid', null, $oldinstance);

    }

    /**
     * Process the question element user attempts.
     *
     * @param array $data
     * @return void
     */
    public function process_cdelement_question_attempts($data) {

        $data = (object) $data;
        $data->contentdesignerid = $this->get_new_parentid('contentdesigner');
        $olduserid = $data->userid;
        $data->userid = $this->get_mappingid('user', $olduserid, 0);

        $this->currentquestionattempt = clone($data);
    }

    /**
     * New usage created for the user attempt, Update the usageid to the element question attempts.
     *
     * @param int $newusageid
     * @return void
     */
    protected function inform_new_usage_id($newusageid) {
        global $DB;

        $data = $this->currentquestionattempt;
        if ($data === null) {
            return;
        }
        $oldid = $data->id;
        unset($data->id);
        $data->uniqueid = $newusageid;
        $newitemid = $DB->insert_record('cdelement_question_attempts', $data);
        // Save quiz_attempt->id mapping, because logs use it.
        $this->set_mapping('cdelement_question_attempts', $oldid, $newitemid, false);
    }

    /**
     * Process the question element slots.
     */
    protected function process_question_attempt_step_data() {

    }

    /**
     * Restore the question element slots for user attempt.
     *
     * @param array $data
     * @return void
     */
    protected function process_cdelement_question_slots($data) {
        global $DB;

        $data = (object) $data;

        $data->instanceid = $this->get_mappingid('question_instanceid', $data->instanceid);

        $olduserid = $data->userid;
        $data->userid = $this->get_mappingid('user', $olduserid, 0);

        $usageid = $DB->get_field('question_attempts', 'uniqueid', [
            'contentdesignerid' => $this->get_new_parentid('contentdesigner'), 'userid' => $data->userid]);

        $data->slot = $DB->get_field('question_attempts', 'slot', [
            'questionid' => $data->questionid, 'questionusageid' => $usageid]);

        $newitemid = $DB->insert_record('cdelement_question_slots', $data);

    }

}
