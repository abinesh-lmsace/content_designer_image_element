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
 * Internal library of functions for module contentdesigner
 *
 * @package    mod_contentdesigner
 * @copyright  2024 Your Name <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_contentdesigner\editor;

/**
 * Display a summary of the user's attempts.
 *
 * @param array $attempts An array of attempt objects
 * @param object $contentdesigner The content designer object
 * @return string HTML to display the summary
 */
function contentdesigner_display_attempts_summary($attempts, $contentdesigner) {
    global $OUTPUT;
    $table = new html_table();
    $table->head = [get_string('attempt', 'contentdesigner'), get_string('started', 'contentdesigner'),
        get_string('finished', 'contentdesigner'), get_string('grade', 'contentdesigner')];
    $table->data = [];
    foreach ($attempts as $attempt) {
        $row = [
            $attempt->attempt,
            userdate($attempt->timestart),
            $attempt->timefinish ? userdate($attempt->timefinish) : '-',
            $attempt->grade ? format_float($attempt->grade, 2) : '-',
        ];
        $table->data[] = $row;
    }
    return html_writer::table($table);
}

/**
 * Check if a new attempt can be started.
 *
 * @param object $contentdesigner The content designer object
 * @param int $attemptnumber The number of attempts already made
 * @return bool Whether a new attempt can be started
 */
function contentdesigner_can_start_new_attempt($contentdesigner, $attemptnumber) {
    return $contentdesigner->attemptsallowed == 0 || $attemptnumber < $contentdesigner->attemptsallowed;
}

/**
 * Create a new attempt.
 *
 * @param int $contentdesignerid The ID of the content designer activity
 * @param int $userid The ID of the user
 * @return object The new attempt object
 */
function contentdesigner_create_attempt($contentdesignerid, $userid) {
    global $DB;
    $attempt = new stdClass();
    $attempt->contentdesignerid = $contentdesignerid;
    $attempt->userid = $userid;
    $attempt->timestart = time();
    $attempt->timefinish = 0;
    $attempt->attempt = $DB->count_records('contentdesigner_attempts', ['contentdesignerid' => $contentdesignerid,
        'userid' => $userid]) + 1;
    $attempt->id = $DB->insert_record('contentdesigner_attempts', $attempt);
    return $attempt;
}
/**
 * Display a question.
 *
 * @param object $question The question object
 * @param object $attempt The attempt object
 * @return string HTML to display the question
 */
function contentdesigner_display_question($question, $attempt) {
    global $OUTPUT;
    $output = $OUTPUT->heading($question->name);
    $output .= html_writer::div($question->questiontext, 'questiontext');
    // You'll need to implement the form for answering the question here.
    // This is a simplified example.
    $output .= html_writer::start_tag('form', ['method' => 'post', 'action' => 'attempt.php']);
    $output .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'attemptid', 'value' => $attempt->id]);
    $output .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'questionid', 'value' => $question->id]);
    $output .= html_writer::empty_tag('input', ['type' => 'text', 'name' => 'answer', 'required' => 'required']);
    $output .= html_writer::empty_tag('input', ['type' => 'submit', 'value' => get_string('submit', 'contentdesigner')]);
    $output .= html_writer::end_tag('form');
    return $output;
}

/**
 * Finish an attempt.
 *
 * @param object $attempt The attempt object
 */
function contentdesigner_finish_attempt($attempt) {
    global $DB;
    $attempt->timefinish = time();
    $contentdesigner = $DB->get_record('contentdesigner', ['id' => $attempt->contentdesignerid]);
    $cm = get_coursemodule_from_instance('contentdesigner', $contentdesigner->id);

    $sql  = 'SELECT cc.*, ce.id as elementid, ce.shortname as elementname
    FROM {contentdesigner_content} cc
    JOIN {contentdesigner_elements} ce ON ce.id = cc.element
    WHERE cc.contentdesignerid = ?';

    $params = [$contentdesigner->id];
    $contents = $DB->get_records_sql($sql, $params);
    $totalmark = 0;
    $maxmark = 0;
    $grade = 0;
    foreach ($contents as $content) {
        $element = editor::get_element($content->element, $cm->id, $attempt);
        $editor = editor::get_editor($cm->id);
        $instance = $element->get_instance($content->instance);
        if ($element->supports_grade() && !$editor->get_option_field($instance->id, $content->element, 'excludeungraded')) {
            // Process the grade before calculating the final grade.
            if (method_exists($element, 'pre_process_grade')) {
                $element->pre_process_grade();
            }

            $totalmark += $element->get_mark($instance);
            $maxmark += $element->get_max_mark($instance);
        }
    }

    if ($maxmark > 0) {
        $grade = (int) round(($totalmark / $maxmark) * $contentdesigner->grade);
    }

    $attempt->grade = $grade;
    $DB->update_record('contentdesigner_attempts', $attempt);
    contentdesigner_update_grades($contentdesigner, $attempt->userid);
}

/**
 * Calculate the final grade based on all attempts.
 *
 * @param object $contentdesigner The content designer object
 * @param array $attempts An array of attempt objects
 * @return float The calculated final grade
 */
function contentdesigner_calculate_final_grade($contentdesigner, $attempts) {
    // Implement your grading method here (highest, average, first, last attempt)
    // This is a simple example using the highest grade.
    if (empty($attempts)) {
        return 0;
    }
    $grademethod = $contentdesigner->grademethod;
    switch ($grademethod) {
        case CONTENTDESIGNER_ATTEMPTFIRST:
            $firstattempt = reset($attempts);
            return $firstattempt->grade;

        case CONTENTDESIGNER_ATTEMPTLAST:
            $lastattempt = end($attempts);
            return $lastattempt->grade;

        case CONTENTDESIGNER_GRADEAVERAGE:
            $sum = 0;
            $count = 0;
            foreach ($attempts as $attempt) {
                if (!is_null($attempt->grade)) {
                    $sum += $attempt->grade;
                    $count++;
                }
            }
            return $count ? ($sum / $count) : 0;

        case CONTENTDESIGNER_GRADEHIGHEST:
            $max = 0;
            foreach ($attempts as $attempt) {
                if ($attempt->grade > $max) {
                    $max = $attempt->grade;
                }
            }
            return $max;

        default:
            throw new coding_exception('Invalid grading method: ' . $grademethod);
    }
}
