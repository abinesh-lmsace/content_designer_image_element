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
 * List of content designer activity report.
 *
 * @package   cdaddon_report
 * @copyright 2024, bdecent gmbh bdecent.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace cdaddon_report\table;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/tablelib.php');

use moodle_url;
use mod_contentdesigner\editor;

use cdaddon_report\table\activity_report_search;

/**
 * Content designer activity report.
 */
class activity_report extends \core_user\table\participants {

    /**
     * Course module instance.
     *
     * @var cm_info
     */
    protected $cm;

    /**
     * Current contentdesigner instance record data.
     *
     * @var stdclass
     */
    public $contentdesigner;

    /**
     * Context.
     *
     * @var stdclass
     */
    public $context;

    /**
     * Fetch completions users list.
     *
     * @param  int $tableid
     * @return void
     */
    public function __construct($tableid) {
        global $PAGE, $DB;
        parent::__construct($tableid);
        // Page doesn't set when called via dynamic table.
        // Fix this use the cmid from table unique id.
        if (empty($PAGE->cm)) {
            $expuniqueid = explode('-', $tableid);
            $cmid = (int) end($expuniqueid);
            $this->cm = get_coursemodule_from_id('contentdesigner', $cmid);
        } else {
            $this->cm = $PAGE->cm;
        }

        $this->contentdesigner = $DB->get_record('contentdesigner', ['id' => $this->cm->instance]);
        $this->context = $this->get_context();

        // Set download option to reports.
        $this->downloadable = true;
        $this->showdownloadbuttonsat = [TABLE_P_BOTTOM];
    }

    /**
     * Get the table context.
     *
     * @return void
     */
    public function get_context(): \context {
        return \context_module::instance($this->cm->id);
    }

    /**
     * Setup and Render the menus table.
     *
     * @param int $pagesize Size of page for paginated displayed table.
     * @param bool $useinitialsbar Whether to use the initials bar which will only be used if there is a fullname column defined.
     * @param string $downloadhelpbutton
     */
    public function out($pagesize, $useinitialsbar, $downloadhelpbutton = '') {

        // Define table headers and columns.
        $columns = [];
        $headers = [];

        $columns[] = 'attempt';
        $headers[] = get_string('attempt', 'mod_contentdesigner');

        $columns[] = 'fullname';
        $headers[] = get_string('fullname');

        $extrafields = \core_user\fields::get_identity_fields($this->context);
        foreach ($extrafields as $field) {
            $headers[] = \core_user\fields::get_display_name($field);
            $columns[] = $field;
        }

        $columns[] = 'starttime';
        $headers[] = get_string('starttime', 'mod_contentdesigner');

        $columns[] = 'completedtime';
        $headers[] = get_string('completedtime', 'mod_contentdesigner');

        $columns[] = 'progress';
        $headers[] = get_string('progress', 'mod_contentdesigner');

        $elements = editor::get_elements();
        foreach ($elements as $element => $path) {
            // Get the element class.
            $elementobj = editor::get_element($element, $this->cm->id);
            if (method_exists($elementobj, 'define_report_columns')) {
                $elementobj->define_report_columns($columns, $headers, $this);
            }

        }

        $columns[] = 'totalgrade';
        $headers[] = get_string('totalgrade', 'mod_contentdesigner');

        $columns[] = 'actions';
        $headers[] = get_string('actions');

        $columns[] = 'lastaccess';
        $headers[] = get_string('lastaccess');

        $this->define_columns($columns);
        $this->define_headers($headers);

        // Remove sorting for some fields.
        $this->sortable(true, 'sortorder', SORT_ASC);

        $this->no_sorting('title');
        $this->no_sorting('progress');
        $this->no_sorting('starttime');
        $this->no_sorting('completedtime');
        $this->no_sorting('actions');
        $this->no_sorting('attempt');
        $this->no_sorting('totalgrade');

        $this->set_attribute('id', 'contentdesigner_activity_report');

        $this->guess_base_url();
        $this->setup();

        $this->extrafields = $extrafields;
        $this->pagesize = $pagesize;
        $this->query_db($pagesize, $useinitialsbar);

        \table_sql::out($pagesize, $useinitialsbar, $downloadhelpbutton);
    }

    /**
     * Get the sql where condition.
     * @return string
     */
    public function get_sql_sort() {
        return 'attemptid';
    }

    /**
     * Guess the base url for the content desiger activity report table.
     */
    public function guess_base_url(): void {
        $this->baseurl = new moodle_url('/mod/contentdesigner/cdaddon/report/report.php', ['id' => $this->cm->id]);
    }

    /**
     * Generate the fullname column.
     *
     * @param \stdClass $data
     * @return string
     */
    public function col_fullname($data) {
        global $OUTPUT;

        if ($this->is_downloading()) {
            return fullname($data);
        }
        return $OUTPUT->user_picture($data, ['size' => 35, 'courseid' => $this->course->id, 'includefullname' => true]);
    }

    /**
     * Generate the user email column.
     *
     * @param \stdClass $data
     * @return string
     */
    public function col_email($data) {
        return $data->email ?? '';
    }

    /**
     * Genearate the start time column.
     *
     * @param \stdClass $data
     * @return string
     */
    public function col_starttime($data) {
        global $DB;
        $starttime = '';
        if ($record = $DB->get_record('contentdesigner_completion', ['contentdesignerid' => $this->cm->instance,
             'userid' => $data->id])) {
            $starttime = !empty($record->starttime) ? userdate($record->starttime, get_string('strftimedatetime')) : '';
        }
        return $starttime;
    }

    /**
     * Genearate the completed time column.
     *
     * @param \stdClass $data
     * @return string
     */
    public function col_completedtime($data) {
        $completion = new \completion_info($this->course);
        $completiondata = $completion->get_data($this->cm, false, $data->id);

        $completedtime = '';
        if ($completion->is_enabled($this->cm) && ($completiondata->completionstate != COMPLETION_INCOMPLETE)) {
            $completedtime = userdate($completiondata->timemodified, get_string('strftimedatetime'));
        }

        return $completedtime;
    }

    /**
     * Generate the Progress column with a progress bar.
     *
     * @param \stdClass $data User data for the row.
     * @return string HTML output for the progress bar.
     */
    public function col_progress($data) {
        global $DB, $OUTPUT;

        // Get all chapters for this content designer instance that are visible.
        $condition = ['contentdesignerid' => $this->cm->instance, 'visible' => 1];
        $chapters = $DB->get_records('cdelement_chapter', $condition, 'position ASC');
        $totalchapters = count($chapters);
        $completedchapters = 0;

        // Initialize progress bar HTML.
        $progressbarhtml = '<div class="cd-report-progress-bar-container">';

        foreach ($chapters as $chapter) {
            // Check if the current user has completed this chapter.
            $completion = $DB->get_record('cdelement_chapter_completion', ['instance' => $chapter->id, 'userid' => $data->id]);

            // Determine the class for completed or incomplete chapters.
            $statusclass = $completion ? 'report-chapter-completed' : 'report-chapter-incomplete';

            // Increment completed chapters if the chapter is completed.
            if ($completion) {
                $completedchapters++;
            }

            // Add a section to the progress bar with the appropriate class.
            $progressbarhtml .= '<div class="cd-report-progress-segment ' . $statusclass . '"></div>';
        }

        $progressbarhtml .= '</div>'; // Close the progress bar container.

        // Calculate completion percentage and add a text label.
        $completionpercentage = ($totalchapters > 0) ? round(($completedchapters / $totalchapters) * 100) : 0;
        $progresslabel = '<div class="cd-report-progress-label">' . $completionpercentage . '% completed</div>';

        $url = new \moodle_url('/mod/contentdesigner/cdaddon/report/report.php', [
            'id' => $this->cm->id,
            'user' => $data->id,
            'action' => 'deleteprogress',
        ]);

        $action = $OUTPUT->action_icon(
            $url,
            new \pix_icon('t/delete', get_string('delete')),
            new \confirm_action(get_string('confirmdeleteinstance', 'mod_contentdesigner')),
            ['class' => 'action-delete'],
            false,
        );

        $action = !is_siteadmin($data->id) ? $action : '';

        return $progressbarhtml . $progresslabel . $action;
    }

    /**
     * Genearate the actions column.
     *
     * @param \stdClass $data
     * @return string
     */
    public function col_actions($data) {
        global $OUTPUT;

        $url = new \moodle_url('/mod/contentdesigner/cdaddon/report/report.php', [
            'id' => $this->cm->id,
            'user' => $data->id,
            'cdattemptid' => $data->attemptid,
            'action' => 'deleteattempt',
        ]);

        $action = $OUTPUT->action_icon(
            $url,
            new \pix_icon('t/delete', get_string('deleteattempt', 'mod_contentdesigner')),
            new \confirm_action(get_string('deleteattemptconfirm', 'mod_contentdesigner')),
            ['class' => 'action-delete'],
            true,
        );

        return $action;
    }

    /**
     * Genearate the attempt column.
     *
     * @param \stdClass $data
     * @return mixed|string
     */
    public function col_attempt($data) {
        global $DB;
        $attemptid = $data->attemptid;
        if ($attemptid) {
            return $DB->get_field('contentdesigner_attempts', 'attempt', ['id' => $attemptid]);
        }
        return get_string('notyetattempted', 'mod_contentdesigner');
    }

    /**
     * Genearate the total grade column.
     *
     * @param \stdClass $data
     * @return string
     */
    public function col_totalgrade($data) {
        global $DB;
        if ($grade = $DB->get_record('contentdesigner_attempts', ['userid' => $data->id,
            'contentdesignerid' => $this->cm->instance, 'id' => $data->attemptid], '*', IGNORE_MULTIPLE)) {
            return format_float($grade->grade, get_config('cdelement_question', 'decimalplaces'));
        }
        return '-';
    }

    /**
     * Allows to set the display column value for all columns without "col_xxxxx" method.
     * @param string $colname column name
     * @param stdClass $data current record
     *
     * @return string
     */
    public function other_cols($colname, $data) {
        global $DB;
        $result = '';
        $elements = editor::get_elements();

        foreach ($elements as $element => $path) {
            // Get the element class.
            $cdattempt = $DB->get_record('contentdesigner_attempts', ['id' => $data->attemptid], '*', IGNORE_MULTIPLE);
            $elementobj = editor::get_element($element, $this->cm->id, $cdattempt);
            if (method_exists($elementobj, 'col_report_field')) {
                $result = $elementobj->col_report_field($colname, $data);
                if ($result) {
                    return $result;
                }
            }
        }

        return $result ?? '';
    }


    /**
     * Query the database for results to display in the table.
     *
     * @param int $pagesize size of page for paginated displayed table.
     * @param bool $useinitialsbar do you want to use the initials bar.
     */
    public function query_db($pagesize, $useinitialsbar = true) {

        list($twhere, $tparams) = $this->get_sql_where();

        $psearch = new activity_report_search($this->course, $this->context, $this->filterset, $this->cm->instance);

        $sort = $this->get_sql_sort();
        if ($sort && !method_exists('moodle_database', 'get_counted_records_sql')) {
            $sort = 'ORDER BY ' . $sort;
            // Add filter for user context assigned users.
            $total = $psearch->get_total_participants_count($twhere, $tparams);
        }

        $this->use_pages = true;
        $rawdata = $psearch->get_participants($twhere, $tparams, $sort, $this->get_page_start(), $this->get_page_size());
        $total = $total ?? $rawdata->current()->fullcount ?? 0;
        $this->pagesize($pagesize, $total);

        $this->rawdata = [];
        foreach ($rawdata as $user) {
            $this->rawdata[] = $user;
        }
        $rawdata->close();

        if ($this->rawdata) {
            $this->allroleassignments = get_users_roles($this->context, array_keys($this->rawdata),
                    true, 'c.contextlevel DESC, r.sortorder ASC');
        } else {
            $this->allroleassignments = [];
        }

        // Set initial bars.
        if ($useinitialsbar) {
            $this->initialbars(true);
        }
    }

}
