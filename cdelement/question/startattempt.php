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
 * Question element - Startattempt new attempt or continue the preview attempt.
 *
 * @package   cdelement_question
 * @copyright 2024 bdecent gmbh <https://bdecent.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use cdelement_question\display_options;

require_once('./../../../../config.php');

$contentinstanceid = required_param('id', PARAM_INT);
$cmid = required_param('cmid', PARAM_INT);
$sesskey = required_param('sesskey', PARAM_ALPHANUMEXT);
$cdattemptid = optional_param('attemptid', 0, PARAM_INT);

list ($course, $cm) = get_course_and_cm_from_cmid($cmid, 'contentdesigner');
$contentdesigner = $DB->get_record('contentdesigner', ['id' => $cm->instance], '*', MUST_EXIST);

$cdattempt = $DB->get_record('contentdesigner_attempts', ['id' => $cdattemptid, 'contentdesignerid' => $contentdesigner->id],
    '*', MUST_EXIST);

// Check login and permissions.
require_login($course, false, $cm);

$context = context_module::instance($cmid);
$PAGE->set_context($context);

$urlparams = [
    'id' => $contentinstanceid,
    'cmid' => $cmid,
    'sesskey' => $sesskey,
];
$url = new moodle_url('/mod/contentdesigner/cdelement/question/startattempt.php', $urlparams);
$PAGE->set_url($url);

confirm_sesskey();

$element = cdelement_question\element::SHORTNAME;
$elementobj = mod_contentdesigner\editor::get_element($element, $cmid, $cdattempt);

$PAGE->set_pagelayout('embedded');

$options = new display_options();
if ($options->forcedlanguage) {
    $oldlang = force_current_language($options->forcedlanguage);
}

$PAGE->requires->js_call_amd('cdelement_question/questionform', 'init', []);

$data = $elementobj->prepare_formdata($contentinstanceid);
$questionattempt = $elementobj->render_attempt($data);

echo $OUTPUT->header();

echo $questionattempt;

$PAGE->requires->js_amd_inline('document.body.classList.add("loaded")');

echo $OUTPUT->footer();

if (isset($oldlang)) {
    force_current_language($oldlang);
}
