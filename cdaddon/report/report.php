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
 * Content designer activity report.
 *
 * @package   cdaddon_report
 * @copyright 2024, bdecent gmbh bdecent.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__.'/../../../../config.php');
require_once($CFG->dirroot. '/lib/tablelib.php');

// Params.
$cmid = required_param('id', PARAM_INT);
$userid = optional_param('user', null, PARAM_INT);
$action = optional_param('action', '', PARAM_TEXT);
$download = optional_param('download', '', PARAM_ALPHA);
$cdattemptid = optional_param('cdattemptid', 0, PARAM_INT);

$url = new \moodle_url('/mod/contentdesigner/cdaddon/report/report.php', ['id' => $cmid]);
$PAGE->set_url($url);
$modulecontext = \context_module::instance($cmid);

$cm = get_coursemodule_from_id('contentdesigner', $cmid);
$contentdesigner = $DB->get_record('contentdesigner', ['id' => $cm->instance]);
$course = get_course($cm->course);
$PAGE->set_course($course);
$PAGE->set_cm($cm);
$PAGE->set_context($modulecontext);
$PAGE->set_heading(get_string('pluginname', 'cdaddon_report', ['course' => $course->fullname]));
$downloadfilename = get_string('reportsfilename', 'contentdesigner', ['name' => $contentdesigner->name]);

$output = '';

require_login();

require_capability('mod/contentdesigner:addinstance', $modulecontext);

if ($action == "deleteprogress") {

    $completion = new \completion_info($course);
    $completiondata = $completion->get_data($cm, false, $userid);

    if ($completiondata->completionstate == COMPLETION_COMPLETE) {

        // Delete from database.
        $DB->delete_records('course_modules_completion', ['coursemoduleid' => $cm->id, 'userid' => $userid]);

        // Check if there is an associated course completion criteria.
        $criteria = $completion->get_criteria(COMPLETION_CRITERIA_TYPE_ACTIVITY);
        $acriteria = false;
        foreach ($criteria as $criterion) {
            if ($criterion->moduleinstance == $cm->id) {
                $acriteria = $criterion;
                break;
            }
        }

        if ($acriteria) {
            // Delete all criteria completions relating to this activity.
            $DB->delete_records('course_completion_crit_compl',
                ['course' => $course->id, 'criteriaid' => $acriteria->id]);
            $DB->delete_records('course_completions', ['course' => $course->id]);
        }

        // Difficult to find affected users, just purge all completion cache.
        cache::make('core', 'completion')->purge();
        cache::make('core', 'coursecompletion')->purge();

    }

    if ($contentdesignercompletion = $DB->get_record('contentdesigner_completion',
        ['contentdesignerid' => $cm->instance, 'userid' => $userid])) {
        $data = new \stdclass();
        $data->id = $contentdesignercompletion->id;
        if ($contentdesignercompletion->completion) {
            $data->completion = 0;
        }
        if ($contentdesignercompletion->mandatorycompletion) {
            $data->mandatorycompletion = 0;
        }
        $data->timecreated = time();
        $DB->update_record('contentdesigner_completion', $data);
    }

    $chapters = $DB->get_records('cdelement_chapter', ['contentdesignerid' => $cm->instance]);
    foreach ($chapters as $chapter) {
        if ($chaptercompletion = $DB->get_record('cdelement_chapter_completion', ['instance' => $chapter->id,
            'userid' => $userid])) {
                $DB->delete_records('cdelement_chapter_completion', ['instance' => $chaptercompletion->instance,
                    'userid' => $userid]);
        }
    }

    // Redirect to the same page to view the activity report.
    redirect($PAGE->url,  get_string('datadeleted', 'mod_contentdesigner'), null, \core\output\notification::NOTIFY_SUCCESS);
} else if ($action == "deleteattempt") {
    $DB->delete_records('contentdesigner_attempts', ['id' => $cdattemptid]);
    redirect($PAGE->url,  get_string('attemptdeleted', 'mod_contentdesigner'), null, \core\output\notification::NOTIFY_SUCCESS);
}

// Build activity report filterset.
$filterset = new \cdaddon_report\table\activity_report_filterset;

$filterset->add_filter(
    new \core_table\local\filter\integer_filter('courseid', \core_table\local\filter\filter::JOINTYPE_DEFAULT, [(int) $course->id])
);

$attemptmethod = new \core_table\local\filter\integer_filter('attemptmethod');
if ($attemptmethodid = optional_param('attemptmethod', null, PARAM_INT)) {
    $attemptmethod->add_filter_value($attemptmethodid);
}
$filterset->add_filter($attemptmethod);

$attemptnumber = new \core_table\local\filter\integer_filter('attemptnumber');
if ($attemptnumberid = optional_param('attemptnumber', null, PARAM_INT)) {
    $attemptnumber->add_filter_value($attemptnumberid);
}
$filterset->add_filter($attemptnumber);

// Activity report table.
$reporttable = new \cdaddon_report\table\activity_report("contentdesigner-activity-report-{$cm->id}");
$reporttable->define_baseurl($PAGE->url);
$reporttable->set_filterset($filterset);
$reporttable->is_downloading($download, $downloadfilename);

// Page header output.
if (!$reporttable->is_downloading()) {
    echo $OUTPUT->header();
    echo $OUTPUT->heading(format_string($contentdesigner->name));
    // List of content designer activity report table output.
    echo $output;
}

// Filter button.
$createbutton = $OUTPUT->box_start();
$createbutton .= \html_writer::start_div('filter-form-container');
$filter = new \cdaddon_report\report_table_filter($url->out(false), ['id' => $cmid]);
$createbutton .= \html_writer::tag('div', $filter->render(), ['id' => 'contentdesigner-report-filterform',
    'class' => 'cdreport-filterform']);
$createbutton .= \html_writer::end_div();
$createbutton .= $OUTPUT->box_end();

if (isset($reporttable)) {

    // Show the button.
    echo $createbutton;

    $pagesize = $reporttable->is_downloading() ? 0 : 10;
    echo $reporttable->out($pagesize, true);
}

if (!$reporttable->is_downloading()) {
    // Page footer output.
    echo $OUTPUT->footer();
}
