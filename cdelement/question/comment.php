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
 * Question element - Render the page for the editors to comment and mark the user attempts.
 *
 * @package   cdelement_question
 * @copyright 2024 bdecent gmbh <https://bdecent.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use cdelement_question\attempt;

require_once('./../../../../config.php');

$contentinstanceid = required_param('id', PARAM_INT);
$cmid = required_param('cmid', PARAM_INT);
$userid = optional_param('userid', $USER->id, PARAM_INT);
$sesskey = required_param('sesskey', PARAM_ALPHANUMEXT);
$cdattemptid = optional_param('cdattemptid', 0, PARAM_INT);

list ($course, $cm) = get_course_and_cm_from_cmid($cmid, 'contentdesigner');

$context = \context_module::instance($cmid);
$PAGE->set_context($context);

$urlparams = [
    'id' => $contentinstanceid,
    'cmid' => $cmid,
    'userid' => $userid,
    'sesskey' => $sesskey,
];
$url = new moodle_url('/mod/contentdesigner/cdelement/question/comment.php', $urlparams);
$PAGE->set_url($url);

confirm_sesskey();

// Check login and permissions.
require_login($course, false, $cm);
require_capability('cdelement/question:grade', $context);

$element = cdelement_question\element::SHORTNAME;
$elementobj = mod_contentdesigner\editor::get_element($element, $cmid);

$cdattempt = $DB->get_record('contentdesigner_attempts', ['id' => $cdattemptid]);

$data = $elementobj->prepare_formdata($contentinstanceid);
$attempt = new attempt($cm->id, $data->id, $data, $userid, $cdattempt);

if ($submitted = data_submitted()) {

    if (optional_param('submit', false, PARAM_BOOL) && question_engine::is_manual_grade_in_range($attempt->get_uniqueid(),
        $submitted->slot)) {

        $transaction = $DB->start_delegated_transaction();
        $attempt->process_submitted_data(time(), $submitted);
        $transaction->allow_commit();

        echo $OUTPUT->notification(get_string('changessaved'), 'notifysuccess');
        close_window(2, true);
        die;
    }
}

$PAGE->set_heading(format_string($course->fullname));

$PAGE->set_pagelayout('popup');

echo $OUTPUT->header();

$context = [
    'question' => $attempt->render_attempt_for_commenting(),
    'slot' => $attempt->get_slot(),
    'sesskey' => sesskey(),
    'userid' => $userid,
    'id' => $contentinstanceid,
    'cmid' => $cm->id,
];
echo $OUTPUT->render_from_template('cdelement_question/questiongrading', $context);

echo $OUTPUT->footer();
