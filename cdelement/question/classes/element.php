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
 * Extended class of elements for Question.
 *
 * @package   cdelement_question
 * @copyright  2024 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace cdelement_question;

use html_writer;
use moodle_url;
use popup_action;
use question_state;
use stdClass;
use question_engine;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/mod/contentdesigner/lib.php');

/**
 * Question element instance extended the base elements.
 */
class element extends \mod_contentdesigner\elements {

    /**
     * Shortname of the element.
     */
    const SHORTNAME = 'question';

    /**
     * Element name which is visbile for the users
     *
     * @return string
     */
    public function element_name() {
        return get_string('pluginname', 'cdelement_question');
    }

    /**
     * Element shortname which is used as identical purpose.
     *
     * @return string
     */
    public function element_shortname() {
        return self::SHORTNAME;
    }

    /**
     * Verify the element is supports the grade.
     *
     * @return bool
     */
    public function supports_grade() {
        return true;
    }

    /**
     * Icon of the element.
     *
     * @param renderer $output
     * @return string HTML fragment
     */
    public function icon($output) {
        return $output->pix_icon('e/help', get_string('pluginname', 'cdelement_question'));
    }

    /**
     * Queestion element supports custom form. Therefore it contain all the form definitions separated.
     *
     * @return bool
     */
    public function supports_custom_form(): bool {
        return true;
    }

    /**
     * Replace the element on refersh the content. Some elements may need to update the content on refresh the elmenet.
     *
     * @return bool
     */
    public function supports_replace_onrefresh(): bool {
        return true;
    }

    /**
     * Summary of pre_process_grade
     * @return void
     */
    public function pre_process_grade() {
        global $DB;
        $totalmark = 0;
        $record = $DB->get_record('cdelement_question_attempts', ['cdattemptid' => $this->cdattempt->id]);
        if ($record) {
            $quba = question_engine::load_questions_usage_by_activity($record->uniqueid);
            $totalmark = $quba->get_total_mark();
        }
        $DB->set_field('cdelement_question_attempts', 'mark', $totalmark, ['cdattemptid' => $this->cdattempt->id]);
    }

    /**
     * Analyze the H5P is mantory to view upcoming then check the instance is attempted.
     *
     * @param stdclass $instance Instance data of the element.
     * @return bool True if need to stop the next instance Otherwise false if render of next elements.
     */
    public function prevent_nextelements($instance): bool {
        global $USER;
        if (isset($instance->mandatory) && $instance->mandatory) {
            $attempt = new attempt($this->cm->id, $instance->id, $instance, $USER->id, $this->cdattempt);
            return !$attempt->is_user_completed();
        }

        return false;
    }

    /**
     * Element form element definition.
     *
     * @param moodle_form $mform
     * @param genreal_element_form $formobj
     * @return void
     */
    public function element_form(&$mform, $formobj) {
        return general_element_form::class;
    }

    /**
     * Update the question reference, this helps to backup the questions.
     *
     * @param int $itemid
     * @param stdClass $data
     * @return void
     */
    protected function update_question_references(int $itemid, stdClass $data) {
        global $DB;

        $qbankentryid = $DB->get_field('question_versions', 'questionbankentryid', ['questionid' => $data->questionid]);

        if ($reference = $DB->get_record('question_references', ['itemid' => $itemid, 'component' => 'cdelement_question'])) {

            $reference->questionbankentryid = $qbankentryid;
            $reference->version = $data->questionversion ?: null;

            $DB->update_record('question_references', $reference);

        } else {

            $record = new stdClass();

            $record->itemid = $itemid;
            $record->component = 'cdelement_question';
            $record->questionbankentryid = $qbankentryid;
            $record->usingcontextid = \context_module::instance($data->cmid)->id;
            $record->questionarea = 'slot';
            $record->version = $data->questionversion ?: null;

            $DB->insert_record('question_references', $record);
        }
    }

    /**
     * Update the element instance. Override the function in elements element class to add custom rules.
     *
     * @param stdclass $data
     * @return int
     */
    public function update_instance($data) {
        global $DB;

        if ($data->instanceid == false) {
            $data->timemodified = time();
            $data->timecreated = time();
            $instanceid = $DB->insert_record($this->tablename, $data);
            $this->update_question_references($instanceid, $data);
            return $instanceid;

        } else {
            $data->timecreated = time();
            $data->id = $data->instanceid;

            $record = $DB->get_record($this->tablename, ['id' => $data->instanceid]);

            // If the behaviour is changes, remove the previous question from slot and insert new question.
            if ($record && (
                $record->preferredbehaviour !== $data->preferredbehaviour
                || $record->questionversion !== $data->questionversion
                || $record->categoryid !== $data->categoryid
                || $record->questionid !== $data->questionid
            )) {
                $DB->set_field('cdelement_question_slots', 'status', (int) false, ['instanceid' => $data->instanceid,
                    'cdattemptid' => $this->cdattempt->id]);
            }

            if ($DB->update_record($this->tablename, $data)) {
                $this->update_question_references($data->id, $data);
                return $data->id;
            }
        }
    }

    /**
     * Render the view of element instance, Which is displayed in the student view.
     *
     * @param \stdClass $instance
     * @return string
     */
    public function render_attempt($instance) {
        global $CFG;
        require_once($CFG->libdir . '/questionlib.php');
        $attempt = new attempt($instance->cmid, $instance->id, $instance, null, $this->cdattempt);

        return $attempt->render_attempt();
    }

    /**
     * Wrapper round print_question from lib/questionlib.php.
     *
     * @param int $instance
     * @return string HTML of the question.
     */
    public function render_attempt_for_commenting($instance) {
        global $CFG;

        require_once($CFG->libdir . '/questionlib.php');

        $attempt = new attempt($instance->cmid, $instance->id, $instance, null, $this->cdattempt);
        return $attempt->render_attempt_for_commenting();
    }

    /**
     * Render the question element output.
     *
     * @param stdClass $instance
     * @return string
     */
    public function render($instance) {
        global $PAGE;
        $url = new moodle_url('/mod/contentdesigner/cdelement/question/startattempt.php', [
            'id' => $instance->id, 'cmid' => $instance->cmid, 'sesskey' => sesskey(), 'attemptid' => $this->cdattempt->id,
        ]);

        return html_writer::tag('div',
            html_writer::tag('iframe', '', ['src' => $url, 'class' => 'responsive', 'data-instanceid' => $instance->id]),
            ['class' => '']
        );
    }

    /**
     * Initiate the JS.
     */
    public function initiate_js() {
        global $PAGE;
        $PAGE->requires->js_call_amd('cdelement_question/question', 'init', []);
    }

    /**
     * Define the each question instance as different column for the report.
     *
     * @param array $columns
     * @param array $headers
     * @param stdClass $table
     * @return void
     */
    public function define_report_columns(array &$columns, array &$headers, $table) {
        global $DB;

        $records = $DB->get_records('cdelement_question',
            ['contentdesignerid' => $this->cm->instance, 'visible' => self::STATUS_VISIBLE]);

        $i = 1;
        foreach ($records as $id => $record) {
            $columns[] = 'cdelement_question_' . $id;
            $headers[] = get_string('questionnumber', 'mod_contentdesigner', $i);
            $table->no_sorting('cdelement_question_' . $id);
            $i++;
        }
    }

    /**
     * Report field data.
     *
     * Find the column is related to the questions and fetch the related grades of the attempts for grading.
     *
     * @param string $colname
     * @param stdClass $row
     * @return string
     */
    public function col_report_field(string $colname, stdClass $row) {
        global $DB, $OUTPUT;

        if (str_starts_with($colname, 'cdelement_question_')) {
            $questionelementid = str_replace('cdelement_question_', '', $colname);
            $cdattempt = $DB->get_record('contentdesigner_attempts', ['id' => $row->attemptid]);
            $record = $DB->get_record('cdelement_question', ['id' => $questionelementid]);
            if (!empty($record)) {

                if (empty($cdattempt)) {
                    return '-';
                }

                $attempt = new attempt($this->cm->id, $record->id, $record, $row->id, $cdattempt);

                $quba = $attempt->get_quba();
                $slot = $attempt->get_slot();

                if (empty($quba) || empty($slot)) {
                    return '-';
                }

                try {
                    $question = $quba->get_question_attempt($slot);
                    if (empty($attempt->get_quba()->get_question_attempt($attempt->get_slot())->get_last_step())) {
                        return '-';
                    }

                } catch (\Exception $e) {
                    return '-';
                }

                $stepdata = $attempt->get_quba()->get_question_attempt($attempt->get_slot())->get_last_step();
                $state = question_state::get($stepdata->get_state());

                // Get max mark.
                if ($question->get_max_mark() == 0) {
                    $grade = '-';
                } else if (is_null($stepdata->get_fraction())) {
                    $grade = ($state == question_state::$needsgrading) ? get_string('requiresgrading', 'question') : '-';
                } else {
                    $feedbackimg = $attempt->icon_for_fraction($stepdata->get_fraction());
                    $mark = $question->fraction_to_mark($stepdata->get_fraction());
                    $grade = $feedbackimg . $mark . ' / ' . $this->calculate_grade($mark, $question->get_max_mark(),
                        $row->id) . '%';
                }

                // Get the question url.
                $questionurl = new moodle_url('/mod/contentdesigner/cdelement/question/comment.php', [
                    'id' => $record->id, 'cmid' => $this->cm->id, 'userid' => $row->id, 'cdattemptid' => $cdattempt->id,
                    'sesskey' => sesskey(),
                ]);

                $action = new popup_action('click',  $questionurl, 'reviewquestion', ['height' => 750, 'width' => 650]);
                $output = $OUTPUT->action_link($questionurl,  $grade, $action, ['title' => get_string('reviewresponse', 'quiz')]);

                return $output ?? '-';
            }
        }
    }

    /**
     * Delete the poll element settings.
     *
     * @param int $instanceid
     * @return bool $status
     */
    public function delete_element($instanceid) {
        global $DB;
        parent::delete_element($instanceid);
        try {
            $transaction = $DB->start_delegated_transaction();

            // Delete the element settings.
            if ($this->get_instance($instanceid)) {

                $question = $DB->get_record('cdelement_question', ['id' => $instanceid]);

                $DB->delete_records($this->tablename(), ['id' => $instanceid]);
                $DB->delete_records('cdelement_question_attempts', ['contentdesignerid' => $question->contentdesignerid]);
                $DB->delete_records('cdelement_question_slots', ['instanceid' => $instanceid,
                    'cdattemptid' => $this->cdattempt->id]);
            }

            if ($this->get_instance_options($instanceid)) {
                // Delete the element general settings.
                $DB->delete_records('contentdesigner_options', ['element' => $this->element_id(),
                    'instance' => $instanceid]);
            }

            $transaction->allow_commit();
        } catch (\Exception $e) {
            // Extra cleanup steps.
            $transaction->rollback($e); // Rethrows exception.
            throw new \moodle_exception('questionnotdeleted', 'cdelement_question');
        }
        return true;
    }

    /**
     * Get the question quba.
     *
     * @param stdClass $instance The instance of the question element.
     * @param int|null $userid The user id.
     *
     * @return \question_attempt
     */
    public function get_question_quba($instance, $userid = null) {
        $attempt = new attempt($this->cm->id, $instance->id, $instance, $userid, $this->cdattempt);
        $quba = $attempt->get_quba();
        $slot = $attempt->get_slot();
        $question = $quba->get_question_attempt($slot);
        return $question;
    }

    /**
     * Get the mark of the question element.
     *
     * @param stdClass $instance The instance of the question element.
     * @return number|null the corresponding mark.
     */
    public function get_mark($instance) {
        return $this->get_question_quba($instance)->get_mark();
    }

    /**
     * Get the maximum mark of the question element.
     *
     * @param stdClass $instance The instance of the question element.
     * @param int|null $userid The user id.
     *
     * @return number|null the corresponding maximum mark.
     */
    public function get_max_mark($instance, $userid = null) {
        return $this->get_question_quba($instance, $userid)->get_max_mark();
    }

    /**
     * Get the maximum grade possible for this question element.
     *
     * @param stdClass $instance The instance of the question element.
     * @return float The maximum grade.
     */
    public function get_max_grade($instance) {
        global $DB;

        $maxgrade = $DB->get_field('question', 'defaultmark', ['id' => $instance->questionid]);
        return $maxgrade !== false ? $maxgrade * ($instance->weight / 100) : 0;
    }

}
