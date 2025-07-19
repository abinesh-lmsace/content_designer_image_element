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
 * Content Designer elements grades.
 *
 * @package    mod_contentdesigner
 * @copyright  2025 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot.'/mod/contentdesigner/lib.php');

$id = required_param('id', PARAM_INT);          // Course module ID.
$userid = optional_param('userid', 0, PARAM_INT); // Graded user ID (optional).

$cm = get_coursemodule_from_id('contentdesigner', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$contentdesigner = $DB->get_record('contentdesigner', ['id' => $cm->instance], '*', MUST_EXIST);

require_login($course, true, $cm);

$context = \context_module::instance($cm->id);
require_capability('mod/contentdesigner:viewgrades', $context);

// Print the page header.
$PAGE->set_url('/mod/contentdesigner/grades.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($contentdesigner->name));
$PAGE->set_heading(format_string($course->fullname));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('grades', 'grades'));

// Get all course users.
$users = get_enrolled_users($context, 'mod/contentdesigner:view', 0, 'u.*', 'lastname ASC');

// Display grades table.
$table = new \html_table();
$table->head = [
    get_string('fullname'),
    get_string('questiongrades', 'mod_contentdesigner'),
    get_string('h5pgrades', 'mod_contentdesigner'),
    get_string('overallgrade', 'grades'),
];
$table->data = [];

foreach ($users as $user) {
    $elementgrades = contentdesigner_get_element_grades($contentdesigner, $user->id);
    $overallgrade = contentdesigner_calculate_final_grade($contentdesigner, $elementgrades);

    $questiongrades = [];
    $h5pgrades = [];

    foreach ($elementgrades as $elementid => $gradeinfo) {
        list($type, $id) = explode('_', $elementid);
        if ($type === 'question') {
            $questiongrades[] = "{$gradeinfo['grade']} ({$gradeinfo['weight']}%)";
        } else if ($type === 'h5p') {
            $h5pgrades[] = "{$gradeinfo['grade']} ({$gradeinfo['weight']}%)";
        }
    }

    $table->data[] = [
        fullname($user),
        implode(', ', $questiongrades),
        implode(', ', $h5pgrades),
        number_format($overallgrade, 2),
    ];
}

echo \html_writer::table($table);

echo $OUTPUT->footer();
