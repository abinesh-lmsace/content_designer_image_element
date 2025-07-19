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
 * Element question - Question element dynamic form.
 *
 * @package   cdelement_question
 * @copyright  2024 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace cdelement_question;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/formslib.php');

use moodle_url;
use moodleform;

/**
 * Form class for include the questions with some custom requried data.
 */
class question_form extends \core_form\dynamic_form {

    /**
     * Content form elements defined.
     *
     * @return void
     */
    public function definition() {
        $mform = $this->_form;

        $mform->updateAttributes(['id' => 'responseform', 'class' => 'mform element-question-form responseform']);

        $mform->addElement('hidden', 'questionid', $this->_customdata['questionid'] ?? 0);
        $mform->setType('questionid', PARAM_INT);

        $mform->addElement('hidden',  'slots',  $this->_customdata['slot'] ?? 1);
        $mform->setType('slots', PARAM_INT);

        // Course module id.
        $mform->addElement('hidden', 'cmid', $this->_customdata['cmid']);
        $mform->setType('cmid', PARAM_INT);

        // Instance id.
        $mform->addElement('hidden', 'id', $this->_customdata['instanceid']);
        $mform->setType('id', PARAM_INT);

        // Instance id.
        $mform->addElement('hidden', 'cdattemptid', $this->_customdata['cdattemptid']);
        $mform->setType('cdattemptid', PARAM_INT);

        // Finish attempt.
        $mform->addElement('hidden', 'finishattempt', 0);
        $mform->setType('finishattempt', PARAM_INT);

        $mform->addElement('html', $this->_customdata['questionoutput']);

        // Is the question attempt finished.
        if (empty($this->_customdata['finished'])) {
            $behavior = $this->_customdata['behavior'];
            if ( $behavior != 'immediatefeedback' && $behavior != 'immediatecbm') {
                $this->add_action_buttons(false, get_string('finish', 'contentdesigner'));
            }
        }
    }

    /**
     * Get the context related to this form.
     */
    protected function get_context_for_dynamic_submission(): \context {
        // Block record id.
        $cmid = $this->optional_param('cmid', 0, PARAM_INT);

        return $cmid ? \context_module::instance($cmid) : \context_system::instance();
    }

    /**
     * Check the access of the current user.
     *
     * @return void
     */
    protected function check_access_for_dynamic_submission(): void {
        // Validatation of user capability goes here.
    }

    /**
     * Process the submission from AJAX.
     *
     * @return void
     */
    public function process_dynamic_submission() {
        global $DB;
        // Get the submitted content data.
        $formdata = (object) fix_utf8($this->_ajaxformdata);

        $cmid = $formdata->cmid;
        $instanceid = $formdata->id;
        $attempt = $DB->get_record('contentdesigner_attempts', ['id' => $formdata->cdattemptid], '*', MUST_EXIST);
        $questionattempt = new attempt($cmid, $instanceid, null, null, $attempt);
        $questionattempt->process_submitted_data(null, $formdata);

        $response = $questionattempt->is_complete_response($formdata->slots, $formdata);

        if (($response['answer'] != -1) && $formdata->finishattempt) {
            $questionattempt->finish_question();
        }
    }

    /**
     * Set the data to the form, update the data to the elements.
     *
     * @return void
     */
    public function set_data_for_dynamic_submission(): void {
        $cmid = $this->optional_param('cmid', 0, PARAM_INT);
        $instanceid = $this->optional_param('instanceid', 0, PARAM_INT);

        $defaults = [
            'cmid' => $cmid,
            'instanceid' => $instanceid,
        ];

        // Setup the block config data to form.
        $this->set_data($defaults);
    }

    /**
     * Get the page ulr to submittit the form data.
     */
    protected function get_page_url_for_dynamic_submission(): moodle_url {
        return new \moodle_url('/local/dash/content/contentform.php', []);
    }

    /**
     * Fetch the widget form elemants.
     *
     * @return \MoodleQuickForm
     */
    public function get_form() {
        return $this->_form;
    }

}
