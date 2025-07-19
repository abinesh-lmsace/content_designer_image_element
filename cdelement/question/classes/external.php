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
 * Question element external werbservice deifintion to manage the questions selector
 *
 * @package   cdelement_question
 * @copyright  2024 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace cdelement_question;

defined('MOODLE_INTERNAL') || die('No direct access');

use external_value;
require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/question/engine/bank.php');

/**
 * Question element external service methods.
 */
class external extends \external_api {

    /**
     * Paramters definition for the method to get list of questions for the category.
     *
     * @return external_function_parameters
     */
    public static function get_question_menu_parameters() {
        return new \external_function_parameters(
            [
                'query' => new external_value(PARAM_RAW, 'Query string'),
                'categoryid' => new external_value(PARAM_RAW_TRIMMED, 'Category id'),
                'limitfrom' => new external_value(PARAM_INT, 'limitfrom we are fetching the records from', VALUE_DEFAULT, 0),
                'limitnum' => new external_value(PARAM_INT, 'Number of records to fetch', VALUE_DEFAULT, 25),
            ]
        );
    }

    /**
     * Fetch the list of questions instance menu.
     *
     * @param string $query
     * @param int $categoryid Category id.
     * @param int $limitfrom Limitfrom we are fetching the records from.
     * @param int $limitnum Number of records to fetch.
     * @return array
     */
    public static function get_question_menu($query, $categoryid, $limitfrom, $limitnum) {
        global $DB;

        $params = self::validate_parameters(self::get_question_menu_parameters(), [
            'query' => $query,
            'categoryid' => $categoryid,
            'limitfrom' => $limitfrom,
            'limitnum' => $limitnum,
        ]);

        $categoryid = $params['categoryid'];
        $limitfrom = $params['limitfrom'];
        $limitnum = $params['limitnum'];

        // Look at each question in the category.
        $questionids = \question_bank::get_finder()->get_questions_from_categories(current(explode(',', $categoryid) ?: []), null);

        if (empty($questionids)) {
            return [];
        }

        list($insql, $inparams) = $DB->get_in_or_equal(array_values($questionids), SQL_PARAMS_NAMED, 'cate');
        $sql = "SELECT q.id, q.name
                FROM {question} q
                WHERE q.id $insql";

        $list = $DB->get_records_sql($sql, ['questionids' => $questionids] + $inparams);

        return $list;
    }

    /**
     * Returns the list of questions menu.
     *
     * @return external_value True if data updated otherwise  returns false.
     */
    public static function get_question_menu_returns() {
        return new \external_multiple_structure(
            new \external_single_structure(
                [
                    'id' => new external_value(PARAM_INT, 'Question ID'),
                    'name' => new external_value(PARAM_RAW, 'Question name'),
                ]
            )
        );
    }

    /**
     * Paramters definition for the method to get list of question variations for the category.
     *
     * @return external_function_parameters
     */
    public static function get_question_variations_parameters() {

        return new \external_function_parameters(
            [
                'questionid' => new external_value(PARAM_INT, 'limitfrom we are fetching the records from', VALUE_REQUIRED),
                'cmid' => new external_value(PARAM_INT, 'Course module id', VALUE_REQUIRED),
            ]
        );
    }

    /**
     * Get the question variations.
     *
     * @param int $questionid
     * @param int $cmid
     * @return array
     */
    public static function get_question_variations(int $questionid, int $cmid) {
        global $PAGE;

        $params = self::validate_parameters(self::get_question_variations_parameters(), [
            'questionid' => $questionid,
            'cmid' => $cmid,
        ]);

        $questionid = $params['questionid'];
        $cmid = $params['cmid'];

        if (empty($PAGE->context)) {
            $PAGE->set_context(\context_module::instance($cmid));
        }

        $selector = \core_question\output\question_version_selection::make_for_question('question_comment_version_dropdown',
            $questionid);
        $qbankrenderer = $PAGE->get_renderer('core_question', 'bank');
        $versionselection = $selector->export_for_template($qbankrenderer);

        $list[] = ['id' => 0, 'name' => get_string('alwayslatest', 'mod_contentdesigner')];
        foreach ($versionselection['options'] as $version) {
            $list[] = ['id' => $version->version, 'name' => $version->name];
        }

        return $list;
    }

    /**
     * Returns the list of variations menu.
     *
     * @return external_multiple_structure List of variations
     */
    public static function get_question_variations_returns() {
        return new \external_multiple_structure(
            new \external_single_structure(
                [
                    'id' => new external_value(PARAM_INT, 'Version ID'),
                    'name' => new external_value(PARAM_RAW, 'Version name'),
                ]
            )
        );
    }

}
