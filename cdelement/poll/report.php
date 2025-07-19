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
 * Poll element report page.
 *
 * Modified from Moodle mod/chocie/report.php.
 *
 * @package    cdelement_poll
 * @copyright  2024 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__.'/../../../../config.php');
require_once($CFG->dirroot. '/lib/tablelib.php');

// Params.
$cmid = required_param('cmid', PARAM_INT);
$pollid = required_param('id', PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHANUMEXT);
$attemptids = optional_param_array('attemptid', [], PARAM_INT); // Get array of responses to delete or modify.
$userids = optional_param_array('userid', [], PARAM_INT); // Get array of users whose choices need to be modified.


$url = new moodle_url('/mod/contentdesigner/cdelement/poll/report.php', ['id' => $pollid, 'cmid' => $cmid]);

if ($action !== '') {
    $url->param('action', $action);
}

$PAGE->set_url($url);
$modulecontext = \context_module::instance($cmid);

if (!$cm = get_coursemodule_from_id('contentdesigner', $cmid)) {
    throw new \moodle_exception("invalidcoursemodule");
}

if (!$course = $DB->get_record("course", ["id" => $cm->course])) {
    throw new \moodle_exception("coursemisconf");
}

require_login($course, false, $cm);

require_capability('mod/contentdesigner:viewcontenteditor', $modulecontext);

if (!$contentdesigner = $DB->get_record('contentdesigner', ['id' => $cm->instance])) {
    throw new \moodle_exception('invalidcoursemodule');
}

$course = get_course($cm->course);
$PAGE->set_course($course);
$PAGE->set_cm($cm);
$PAGE->set_context($modulecontext);
$PAGE->set_heading(get_string('pluginname', 'cdelement_poll', ['course' => $course->fullname]));

$element = \mod_contentdesigner\editor::get_element('poll', $cmid);
$instance = $DB->get_record('cdelement_poll', ['id' => $pollid, 'contentdesignerid' => $contentdesigner->id]);

if (data_submitted() && confirm_sesskey()) {
    if ($action === 'delete') {
        // Delete responses of other users.
        $element->poll_delete_responses($attemptids, $instance, $cm, $course);
        redirect($url);
    }

    if (preg_match('/^choose_(\d+)$/', $action, $actionmatch)) {
        // Modify responses of other users.
        $newoptionid = (int)$actionmatch[1];
        $element->poll_modify_responses($userids, $attemptids, $newoptionid, $instance, $cm, $course);
        redirect($url);
    }

}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string("responses", "mod_contentdesigner"));

$groupmode = groups_get_activity_groupmode($cm);
$users = $element->poll_get_response_data($instance, $cm, $groupmode, true);
$responses = $element->prepare_poll_show_responses($instance, $course, $cm, $users);
$resultstable = $element->display_publish_responses($responses);

echo $OUTPUT->box($resultstable);
echo $OUTPUT->footer();
