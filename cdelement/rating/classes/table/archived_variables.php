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
 * Element Rating - Archived variables list table.
 *
 * @package    cdelement_rating
 * @copyright  2025 bdecent GmbH <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace cdelement_rating\table;

use core_course_category;
use html_writer;
use stdClass;

/**
 * Element Rating - Archived variables list table.
 */
class archived_variables extends \table_sql {

    /**
     * Table contructor to define columns and headers.
     */
    public function __construct() {

        // Call parent constructor.
        parent::__construct('archivedvariableslist');

        // Define table headers and columns.
        $columns = ['name', 'shortname', 'type', 'description', 'categories', 'status', 'elementscount', 'responsescount',
            'timecreated', 'timearchived', 'actions',
        ];
        $headers = [
            get_string('fullname'),
            get_string('shortname'),
            get_string('type', 'mod_contentdesigner'),
            get_string('description'),
            get_string('categories'),
            get_string('status', 'mod_contentdesigner'),
            get_string('elementscount', 'mod_contentdesigner'),
            get_string('responsescount', 'mod_contentdesigner'),
            get_string('timecreated'),
            get_string('timearchived', 'mod_contentdesigner'),
            get_string('actions'),
        ];

        $this->define_columns($columns);
        $this->define_headers($headers);

        // Remove sorting for some fields.
        $this->sortable(false);

        // Do not make the table collapsible.
        $this->collapsible(false);
        $this->initialbars(false);

        $this->set_attribute('id', 'archived_variables_list');
    }

    /**
     * Get the variables list.
     *
     * @param int $pagesize
     * @param bool $useinitialsbar
     * @throws \dml_exception
     */
    public function query_db($pagesize, $useinitialsbar = false) {

        $condition = 'status != 1';

        // Set the query values to fetch variables.
        $this->set_sql('*', '{cdelement_rating_variables}', $condition, []);

        parent::query_db($pagesize, $useinitialsbar);
    }

    /**
     * Description of the variable.
     *
     * @param stdClass $row
     * @return string
     */
    public function col_description(stdClass $row): string {
        return format_text($row->description, FORMAT_HTML, ['overflowdiv' => false]);
    }

    /**
     * Name of the variable column. Format the string to support multilingual.
     *
     * @param stdClass $row
     * @return string
     */
    public function col_name(stdClass $row): string {
        return format_string($row->fullname);
    }

    /**
     * Type of the variable.
     *
     * @param stdClass $row
     * @return string
     */
    public function col_type(stdClass $row): string {
        return \cdelement_rating\helper::get_variable_type($row->id);
    }

    /**
     * Status of the variable. Active or archived.
     *
     * @param stdClass $row
     * @return string
     */
    public function col_status(stdClass $row): string {
        return $row->status == 1 ? get_string('active') : get_string('archived', 'mod_contentdesigner');
    }

    /**
     * Categories list where this variable is available.
     *
     * @param stdClass $row
     * @return string
     */
    public function col_categories(stdClass $row): string {

        $categories = $row->categories ?? [];
        if (empty($categories)) {
            return '';
        }

        $categories = json_decode($categories);
        $list = core_course_category::get_many($categories);
        $list = array_map(fn($cate) => $cate->get_formatted_name(), $list);

        return implode(', ', $list);
    }

    /**
     * Number of elements in the variable.
     *
     * @param stdClass $row
     * @return string
     */
    public function col_elementscount(stdClass $row): string {
        return \cdelement_rating\helper::get_variables_elements_count($row->id);
    }

    /**
     * Variable created time in user readable.
     *
     * @param stdClass $row
     * @return string
     */
    public function col_timecreated(stdClass $row): string {
        return userdate($row->timecreated);
    }

    /**
     * Number of responses for the variable.
     *
     * @param stdClass $row
     * @return string
     */
    public function col_responsescount(stdClass $row): string {
        return \cdelement_rating\helper::get_variables_responses_count($row->id);
    }

    /**
     * Actions to manage the variable row. Like edit, change status, archive and delete.
     *
     * @param stdClass $row
     * @return string
     */
    public function col_actions(stdClass $row): string {
        global $OUTPUT;

        // Variables List URL.
        $listurl = new \moodle_url('/mod/contentdesigner/cdelement/rating/variables/list.php', [
            'id' => $row->id,
            'sesskey' => \sesskey(),
        ]);

        $actions = [];

        // Delete the variable.
        $actions[] = [
            'url' => new \moodle_url($listurl, ['action' => 'delete', 't' => 'archive']),
            'icon' => new \pix_icon('t/delete', \get_string('delete')),
            'attributes' => ['class' => 'action-delete'],
            'action' => new \confirm_action(get_string('confirmdeletevariable', 'mod_contentdesigner')),
        ];

        // Unarchive the variables.
        $actions[] = [
            'url' => new \moodle_url($listurl, ['t' => 'active', 'action' => 'active']),
            'icon' => new \pix_icon('f/active', \get_string('active'), 'mod_contentdesigner'),
            'attributes' => ['class' => 'action-active'],
            'action' => new \confirm_action(get_string('confirmactivevariable', 'mod_contentdesigner')),
        ];

        $actionshtml = [];
        foreach ($actions as $action) {
            if (!is_array($action)) {
                $actionshtml[] = $action;
                continue;
            }
            $action['attributes']['role'] = 'button';
            $actionshtml[] = $OUTPUT->action_icon(
                $action['url'],
                $action['icon'],
                ($action['action'] ?? null),
                $action['attributes'],
            );
        }
        return html_writer::div(join('', $actionshtml), 'variable-item-actions item-actions mr-0');
    }

    /**
     * Variable archived time in user readable.
     *
     * @param stdClass $row
     * @return string
     */
    public function col_timearchived(stdClass $row): string {
        return userdate($row->timearchived);
    }
}
