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

namespace mod_contentdesigner;

/**
 * Class for handling different action views for contentdesigner
 *
 * @package    mod_contentdesigner
 * @copyright  2022 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class content_display {

    /** @var object The contentdesigner record */
    protected $contentdesigner;

    /** @var object The course module record */
    protected $cm;

    /** @var object The course record */
    protected $course;

    /** @var object The context */
    protected $context;

    /**
     * Constructor
     *
     * @param object $contentdesigner The contentdesigner record
     * @param object $cm The course module record
     * @param object $course The course record
     * @param object $context The context
     */
    public function __construct($contentdesigner, $cm, $course, $context) {
        $this->contentdesigner = $contentdesigner;
        $this->cm = $cm;
        $this->course = $course;
        $this->context = $context;
    }

    /**
     * View function that handles different actions
     *
     * @param string $action The action to perform
     * @param bool $noheader Whether to display the header or not
     * @return void
     */
    public function view($action, $noheader = false) {
        global $OUTPUT, $USER, $DB;

        // Default if no action is provided.
        if (empty($action)) {
            if ($this->contentdesigner->enablegrading) {
                echo $this->view_summary($noheader);
            } else {
                echo $this->view_content($noheader);
            }
            return;
        }

        // Handle different actions.
        switch ($action) {

            case 'makeattempt':
                echo $this->make_attempt();
                break;

            case 'continueattempt':
                $attemptid = required_param('attemptid', PARAM_INT);
                echo $this->continue_attempt($attemptid);
                break;
            case 'finishattempt':
                $attemptid = required_param('attemptid', PARAM_INT);
                $this->finish_attempt($attemptid);
            default:
                if ($this->contentdesigner->enablegrading) {
                    echo $this->view_summary();
                } else {
                    echo $this->view_content();
                }
                break;
        }

        echo $OUTPUT->footer();
    }

    /**
     * Finish an attempt
     *
     * @param int $attemptid The attempt ID
     * @param bool $noheader Whether to display the header or not
     * @return \moodle_url Redirect URL after finishing the attempt
     */
    public function finish_attempt($attemptid, $noheader = false) {
        global $DB, $USER;
        $attempt = $DB->get_record('contentdesigner_attempts', [
            'id' => $attemptid, 'contentdesignerid' => $this->contentdesigner->id,
            'userid' => $USER->id], '*', MUST_EXIST);
        contentdesigner_finish_attempt($attempt);
        if (!$noheader) {
            return redirect(
                new \moodle_url('/mod/contentdesigner/view.php', ['id' => $this->cm->id]),
                get_string('attemptfinished', 'contentdesigner'));
        }
    }

    /**
     * Display the attempt summary page
     *
     * @param bool $noheader Whether to display the header or not
     * @return string The HTML content for the summary page
     */
    public function view_summary($noheader = false) {
        global $OUTPUT, $USER;

        $content  = '';
        // Get current user's attempts.
        $attempts = contentdesigner_get_user_attempts($this->contentdesigner->id, $USER->id);
        // Add auto start attempt functionality.
        if ($this->contentdesigner->autostartattempts && (empty($attempts) || !current($attempts)->timefinish)) {
            if (empty($attempts)) {
                // For popup compatibility, render attempt directly instead of redirecting.
                return $this->make_attempt($noheader);
            } else {
                $lastattempt = end($attempts);
                // For popup compatibility, render attempt directly instead of redirecting.
                return $this->continue_attempt($lastattempt->id, $noheader);
            }
        }

        if (!$noheader) {
            $content .= $OUTPUT->header();
        }
        $gradeitem = \grade_item::fetch([
            'itemtype' => 'mod',
            'itemmodule' => 'contentdesigner',
            'iteminstance' => $this->contentdesigner->id,
            'itemnumber' => 0,
            'courseid' => $this->course->id,
        ]);

        // Display content designer introduction.
        if (!empty($this->contentdesigner->intro)) {
            $content .= $OUTPUT->box(format_module_intro('contentdesigner', $this->contentdesigner, $this->cm->id),
                'generalbox mod_introbox', 'contentdesignerintro');
        }

        $content .= $OUTPUT->box(contentdesigner_get_infomessages($this->contentdesigner, $gradeitem));

        if (empty($attempts)) {
            // If no attempts, show start attempt button.
            $url = new \moodle_url('/mod/contentdesigner/view.php', [
                'id' => $this->cm->id,
                'action' => 'makeattempt'
            ]);
            $content .= $OUTPUT->single_button($url, get_string('startattempt', 'contentdesigner'), 'get');
        } else {
            $lastattempt = end($attempts);
            if (!$lastattempt->timefinish) {
                $url = new \moodle_url('/mod/contentdesigner/view.php', [
                    'id' => $this->cm->id,
                    'action' => 'continueattempt',
                    'attemptid' => $lastattempt->id
                ]);
                $content .= $OUTPUT->single_button($url,
                get_string('continueattempt', 'contentdesigner'), 'get');
            } else if (contentdesigner_can_start_new_attempt($this->contentdesigner, count($attempts))) {
                $url = new \moodle_url('/mod/contentdesigner/view.php', [
                    'id' => $this->cm->id,
                    'action' => 'makeattempt'
                ]);
                $content .= $OUTPUT->single_button($url, get_string('startnewattempt', 'contentdesigner'), 'get');
            }

            // Display summary of previous attempts.
            $content .= $OUTPUT->box(contentdesigner_display_attempts_summary($attempts, $this->contentdesigner));
        }

        if (!$noheader) {
            $content .= $OUTPUT->footer();
        }

        return $content;
    }

    /**
     * Display the content
     *
     * @param bool $noheader Whether to display the header or not
     * @return string The HTML content for the content designer
     */
    public function view_content($noheader = false) {
        global $OUTPUT, $USER, $DB, $PAGE;

        $content = '';
        if (!$noheader) {
            $content .= $OUTPUT->header();
        }

        // Display content designer introduction.
        if (!empty($this->contentdesigner->intro)) {
            $content .= $OUTPUT->box(format_module_intro('contentdesigner', $this->contentdesigner, $this->cm->id),
                'generalbox mod_introbox', 'contentdesignerintro');
        }

        $cdattempt = $DB->get_record('contentdesigner_attempts',
            ['userid' => $USER->id, 'contentdesignerid' => $this->cm->instance],
            '*', IGNORE_MULTIPLE);

        if (!$cdattempt) {
            $cdattempt = contentdesigner_create_attempt($this->cm->instance, $USER->id);
        }

        // Render the page view of the elements.
        $editor = new \mod_contentdesigner\editor($this->cm, $this->course, $cdattempt);
        $editor->initiate_js();

        $content .= $editor->render_elements();

        if (!$noheader) {
            $content .= $OUTPUT->footer();
        }

        return $content;

    }

    /**
     * Make a new attempt
     *
     * @param bool $noheader Whether to display the header or not
     * @return string The HTML content for the attempt form
     */
    public function make_attempt($noheader = false) {
        global $USER;

        // Create a new attempt.
        $cdattempt = contentdesigner_create_attempt($this->contentdesigner->id, $USER->id);

        // Include the attempt form.
        $content = $this->get_attempt_form($cdattempt, $noheader);

        return $content;
    }

    /**
     * Continue an existing attempt
     *
     * @param int $attemptid The attempt ID
     * @param bool $noheader Whether to display the header or not
     * @return string The HTML content for the attempt form
     */
    public function continue_attempt($attemptid, $noheader = false) {
        global $DB, $USER;

        $cdattempt = $DB->get_record('contentdesigner_attempts', ['id' => $attemptid], '*', MUST_EXIST);

        // Security check - ensure the attempt belongs to this user.
        if ($cdattempt->userid != $USER->id) {
            throw new \moodle_exception('nopermissions', 'error', '', 'continue this attempt');
        }

        // Include the attempt form.
        $content = $this->get_attempt_form($cdattempt, $noheader);

        return $content;
    }

    /**
     * Get the form fields for the attempt form
     *
     * @param object $cdattempt The content designer attempt object
     * @param bool $noheader Whether to display the header or not
     * @return string The HTML content for the form fields
     */
    public function get_attempt_form($cdattempt, $noheader=false) {
        global $OUTPUT;

        $content = '';

        if (!$noheader) {
            $content .= $OUTPUT->header();
        }

        // Display content designer introduction.
        if (!empty($this->contentdesigner->intro)) {
            $content .= $OUTPUT->box(format_module_intro('contentdesigner', $this->contentdesigner, $this->cm->id),
                'generalbox mod_introbox', 'contentdesignerintro');
        }

        // Show attempt form.
        $content .= \html_writer::start_tag('form', [
            'id' => 'attempt-form',
            'method' => 'post',
            'action' => new \moodle_url('/mod/contentdesigner/view.php'),
            'class' => 'contentdesigner-form'
        ]);

        $content .= \html_writer::empty_tag('input', [
            'type' => 'hidden',
            'name' => 'id',
            'value' => $this->cm->id
        ]);

        $content .= \html_writer::empty_tag('input', [
            'type' => 'hidden',
            'name' => 'action',
            'value' => 'submit'
        ]);

        $content .= \html_writer::empty_tag('input', [
            'type' => 'hidden',
            'name' => 'attemptid',
            'value' => $cdattempt->id
        ]);

        $content .= \html_writer::empty_tag('input', [
            'type' => 'hidden',
            'name' => 'sesskey',
            'value' => sesskey()
        ]);
        // Render the page view of the elements.
        $editor = new \mod_contentdesigner\editor($this->cm, $this->course, $cdattempt);
        $editor->initiate_js();

        $content .= $editor->render_elements();
        $content .= \html_writer::end_tag('form');

        return $content;
    }
}
