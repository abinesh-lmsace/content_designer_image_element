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
 * Element question - Manage user attempts.
 *
 * @package   cdelement_question
 * @copyright  2024 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace cdelement_question;

use stdClass;
use question_engine;
use question_usage_by_activity;
use context_module;
use question_display_options;
use question_state;

/**
 * Manage the user attempts.
 */
class attempt {

    /**
     * The course module ID.
     *
     * @var int
     */
    protected int $cmid;

    /**
     * The ID of the Content Designer instance.
     *
     * @var int
     */
    protected int $instanceid;

    /**
     * Data about the current instance.
     *
     * @var stdClass
     */
    protected stdClass $instance;

    /**
     * ID of the user interacting with the question.
     *
     * @var int
     */
    protected int $userid;

    /**
     * Tracks the question attempt usage.
     *
     * @var question_usage_by_activity
     */
    protected question_usage_by_activity $quba;

    /**
     * The slot number of the current question.
     *
     * @var int
     */
    protected int $slot;

    /**
     * Data related to the current slot (e.g., status, user).
     *
     * @var stdClass
     */
    protected $slotdata;

    /**
     * The module context.
     *
     * @var \context_module
     */
    protected \context_module $context;

    /**
     * Options for displaying the question.
     *
     * @var display_options
     */
    protected display_options $options;

    /**
     * A helper class for managing question attempts.
     *
     * @var helper
     */
    protected helper $helper;

    /**
     * The current Content Designer attempt.
     *
     * @var stdClass
     */
    protected $cdattempt;

    /**
     * Initializes the instance with details about the course module, instance, and user.
     * It also determines if there is an existing question attempt or if a new one needs to be created.
     *
     * @param int $cmid
     * @param int $instanceid
     * @param stdClass|null $instance
     * @param int|null $userid
     * @param stdClass|null $cdattempt
     */
    public function __construct(int $cmid, int $instanceid, ?stdClass $instance=null, ?int $userid=null, $cdattempt = null) {
        global $USER, $DB;

        // Course module id.
        $this->cmid = $cmid;

        $this->cdattempt = $cdattempt;

        $this->context = context_module::instance($cmid);

        // Instance id.
        $this->instanceid = $instanceid;

        $this->instance = $instance ?: $this->get_instancedata();

        $this->userid = $userid ?: $USER->id;

        $this->slotdata = $DB->get_record('cdelement_question_slots',
            ['userid' => $this->userid, 'cdattemptid' => $cdattempt->id, 'instanceid' => $this->instance->id]);

        $this->slot = (isset($this->slotdata->slot) && $this->slotdata->status) ? $this->slotdata->slot : false;

        $this->options = new display_options($this->instance->preferredbehaviour ?? '');

        $this->helper = \cdelement_question\helper::instance($this->instance->contentdesignerid);

        if ($attempt = $this->helper->find_usage($this->userid, $this->cdattempt->id)) {
            $this->load_question_usage($attempt->uniqueid);
        } else {
            $this->create_new_usage();
        }
    }

    /**
     * Checks if the user has completed the current question.
     *
     * @return bool
     */
    public function is_user_completed() {
        if ($this->get_slot() && $this->slotdata->status) {
            $question = $this->quba->get_question_attempt($this->slot);
            $state = question_state::get($question->get_state());
            $stateclass = get_class($state);

            return (strtolower($question->get_state_string(false)) == 'complete'
                || is_subclass_of($state, 'question_state_graded')
                || $stateclass === 'question_state_graded'
                || $stateclass === 'question_state_gaveup'
                || is_subclass_of($state, 'question_state_gaveup')
            );
        }

        return false;
    }

    /**
     * Fetches the unique ID of the current question usage attempt.
     *
     * @return int
     */
    public function get_uniqueid() {

        if ($attempt = $this->helper->find_usage($this->userid, $this->cdattempt->id)) {
            $this->load_question_usage($attempt->uniqueid);
        }
        return $this->quba->get_id();
    }

    /**
     * Retrieves the question_usage_by_activity object for managing question attempts.
     *
     * @return question_usage_by_activity
     */
    public function get_quba() {
        if ($attempt = $this->helper->find_usage($this->userid, $this->cdattempt->id)) {
            $this->load_question_usage($attempt->uniqueid);
            return $this->quba;
        }
    }

    /**
     * Fetches data about the current Content Designer instance.
     *
     * @return stdClass
     */
    protected function get_instancedata(): stdClass {

        $element = \mod_contentdesigner\editor::get_element(\cdelement_question\element::SHORTNAME, $this->cmid);
        $instance = $element->get_instance($this->instanceid, true);
        return (object) $instance;
    }

    /**
     * Get the question id by the variant questions.
     *
     * @param stdclass $question
     * @return \question_definition loaded from the database.
     */
    protected function get_questionid_byvariant($question) {
        $versionids = \qbank_previewquestion\helper::load_versions($question->questionbankentryid);
        $questionid = \qbank_previewquestion\helper::get_restart_id($versionids, $this->instance->questionversion);
        return \question_bank::load_question($questionid);
    }

    /**
     * Check the same questions shows form the question back.
     *
     * @param stdclass $question
     * @return bool
     */
    protected function is_same_question($question) {
        $versionids = \qbank_previewquestion\helper::load_versions($question->questionbankentryid);
        return array_key_exists($this->instance->questionid, $versionids);
    }

    /**
     * Confirm is the question is updated.
     *
     * @return bool
     */
    protected function is_question_updated() {
        $slot = $this->get_slot();

        $questionattempt = $this->quba->get_question_attempt($slot);
        $question = $this->quba->get_question($slot);
        $currentquestion = $this->get_questionid_byvariant($question);

        if (!$this->is_same_question($question)
            || $currentquestion->id != $question->id
            || $questionattempt->get_variant() != $this->instance->questionversion
            || ($this->instance->questionversion != 0 && $this->instance->questionversion != $question->version)) {

            return true;
        }
    }

    /**
     * Starts a new question attempt for the user.
     *
     * @return question_definition
     */
    public function start_new_attempt() {
        // Todo: In upcoming version, if need to manage attempts like quiz. The attempt verification script goes here.
        $variant = $this->instance->questionversion;

        // Start new attempt.
        if (empty($this->quba)) {
            $this->create_new_usage(); // Create a new usage.
        }

        $maxmark = display_options::MAX_MARK;
        $question = \question_bank::load_question($this->instance->questionid);
        $question = $this->get_questionid_byvariant($question);

        $this->slot = $this->quba->add_question($question, $question->defaultmark ?? $maxmark);
        $this->quba->start_question($this->slot, $variant);

        // Todo: Update the log of content designer to start of new attempt.
        $this->save_usage();

        // Update the new usage.
        return $this->helper->update_new_usage($this->quba->get_id(), $this->userid, $this->instanceid,
            $this->slot, $this->cdattempt->id);
    }

    /**
     * Continues the last question attempt for the user, if available.
     * Resumes a previous attempt or starts a new one if necessary.
     *
     * @param stdClass $attempt
     * @return void
     */
    protected function continue_last_attempt($attempt) {
        global $CFG;

        require_once($CFG->dirroot.'/question/engine/questionattempt.php');

        // Load the question usage.
        $this->load_question_usage($attempt->uniqueid);

        if (!empty($this->slot)) {

            $question = $this->quba->get_question($this->slot);

            if (!$this->slotdata->status || $this->is_question_updated()) {

                $maxmark = display_options::MAX_MARK;
                $question = \question_bank::load_question($this->instance->questionid);
                $question = $this->get_questionid_byvariant($question);

                $this->quba->add_question_in_place_of_other($this->slot, $question, $question->defaultmark ?? $maxmark);
                $this->quba->start_question($this->slot, $this->instance->questionversion);
                $this->save_usage();

                // Change flag the new question is added in the place of previous question.
                $this->helper->update_slot_status($this->slotdata->id, true);
            }

        } else {
            $this->start_new_attempt();
        }

        $this->helper->update_new_usage($this->quba->get_id(), $this->userid, $this->instanceid, $this->slot,
            $this->cdattempt->id);
    }

    /**
     * Loads a question usage attempt by its unique ID. Retrieves question usage details from the database.
     *
     * @param int $qubaid
     * @return void
     */
    protected function load_question_usage(int $qubaid) {
        global $DB;
        $this->quba = question_engine::load_questions_usage_by_activity($qubaid, $DB);
        $this->quba->set_preferred_behaviour($this->instance->preferredbehaviour);
    }

    /**
     * Creates a new question usage instance.
     *
     * @return void
     */
    public function create_new_usage() {
        $this->quba = question_engine::make_questions_usage_by_activity('cdelement_question', $this->context);
        $this->quba->set_preferred_behaviour($this->instance->preferredbehaviour);
        $this->save_usage();
    }

    /**
     * Returns the slot number of the current question.
     *
     * @return int|bool
     */
    public function get_slot() {

        if (!empty($this->slot)) {
            return $this->slot;
        }

        if ($attempt = $this->helper->find_usage($this->userid, $this->cdattempt->id)) {
            $this->load_question_usage($attempt->uniqueid);
            if ($this->load_question_slot($attempt)) {
                return $this->slot;
            }
        }

        return false;
    }

    /**
     * Saves the current question usage data to the database.
     *
     * @return void
     */
    protected function save_usage() {
        // Save the question usage.
        \question_engine::save_questions_usage_by_activity($this->quba);
    }

    /**
     * Loads the slot details for the current question.
     *
     * @param stdClass $attempt
     * @return bool
     */
    protected function load_question_slot($attempt) {

        if (empty($this->slotdata)) {
            $this->start_new_attempt();
        } else if (!$this->slotdata->status) {
            $this->continue_last_attempt($attempt);
        } else {
            $this->slot = $this->slotdata->slot;
        }

        return false;
    }

    /**
     * Processes and saves data submitted by the user for a question attempt.
     *
     * @param int|bool $time
     * @param stdClass $submitteddata
     * @return void
     */
    public function process_submitted_data($time=null, $submitteddata=null) {

        $attempt = $this->helper->find_usage($this->userid, $this->cdattempt->id);

        if (!empty($attempt->uniqueid)) {

            $this->load_question_usage($attempt->uniqueid);
            $this->load_question_slot($attempt);

            $this->quba->process_all_actions($time, (array) $submitteddata);
            $this->save_usage();
        }
    }

    /**
     * Extracts the responses from the user's submission.
     *
     * @param int $slot
     * @param stdClass $postdata
     * @return array
     */
    public function is_complete_response($slot, $postdata = null) {
        return $this->quba->extract_responses($slot, (array) $postdata);
    }

    /**
     * Marks the current question as finished.
     *
     * @return void
     */
    public function finish_question() {
        $this->quba->finish_question($this->slot);
        $this->save_usage();
    }

    /**
     * Return an appropriate icon (green tick, red cross, etc.) for a grade.
     *
     * * Modified version of mod_quiz  attempt_report class method.
     *
     * @param float $fraction grade on a scale 0..1.
     * @return string html fragment.
     */
    public function icon_for_fraction($fraction) {
        global $OUTPUT;

        $feedbackclass = question_state::graded_state_for_fraction($fraction)->get_feedback_class();
        return $OUTPUT->pix_icon('i/grade_' . $feedbackclass, get_string($feedbackclass, 'question'),
                'moodle', ['class' => 'icon']);
    }

    /**
     * Renders the question attempt interface for the user.
     *
     * @return string
     */
    public function render_attempt() {
        global $PAGE;
        if ($attempt = $this->helper->find_usage($this->userid, $this->cdattempt->id)) {
            $this->continue_last_attempt($attempt);
            $state = $this->quba->get_question_state($this->slot, false);
            $stateclass = get_class($state);
            $isfinished = str_contains($stateclass, 'question_state_graded') ? true : false;
        } else {
            $attempt = $this->start_new_attempt();
        }

        $displaynumber = $attempt ? self::get_instance_displaynumber($this->instance->id, $this->instance->contentdesignerid) : 1;

        $PAGE->requires->js_module('core_question_engine');

        $this->quba->render_question_head_html($this->slot);

        $questionoutput = $this->quba->render_question($this->slot, $this->options, $displaynumber ?: 1);

        $customdata = [
            'cmid' => $this->cmid,
            'contentdesignerid' => $this->instance->contentdesignerid ?? 0,
            'instanceid' => $this->instanceid ?? 1,
            'behavior' => $this->instance->behaviour ?? $this->options->behaviour,
            'questionoutput' => $questionoutput,
            'questionid' => $this->instance->questionid,
            'slot' => $this->slot,
            'finished' => $isfinished ?? false,
            'cdattemptid' => $this->cdattempt->id,
        ];

        $questionform = new question_form(null, $customdata, 'get');

        return $questionform->render();
    }

    /**
     * Renders the question for manual grading or teacher comments.
     *
     * @return string HTML of the question.
     */
    public function render_attempt_for_commenting() {

        $attempt = $this->helper->find_usage($this->userid, $this->cdattempt->id);

        if (!empty($attempt->uniqueid)) {

            $this->load_question_usage($attempt->uniqueid);
            $this->load_question_slot($attempt);

            $this->options->generalfeedback = question_display_options::HIDDEN;
            $this->options->manualcomment = question_display_options::EDITABLE;
            $this->options->readonly = \cdelement_question\display_options::READONLY;

            return $this->quba->render_question($this->slot, $this->options, $this->instance->title);
        }

        return '';
    }

    /**
     * Returns the display number of the current instance in the activity.
     *
     * @param int $instanceid
     * @param int $contentdesignerid
     * @return int
     */
    protected static function get_instance_displaynumber(int $instanceid, $contentdesignerid) {
        global $DB;

        $sql = '
            SELECT cc.instance
            FROM {contentdesigner_content} cc
            JOIN {contentdesigner_elements} cde ON cde.id = cc.element
            WHERE cc.contentdesignerid=:contentdesignerid AND cde.shortname=:question ORDER BY cc.position ASC';

        $list = $DB->get_records_sql($sql, ['question' => 'question', 'contentdesignerid' => $contentdesignerid]);

        $index = array_search($instanceid, array_column(array_values($list), 'instance'));

        return  $index + 1;
    }
}
