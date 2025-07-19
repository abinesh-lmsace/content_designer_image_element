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
 * Videotime element external werbservice deifintion to manage the videotime completion.
 *
 * @package   cdelement_videotime
 * @copyright  2024 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace cdelement_videotime;

defined('MOODLE_INTERNAL') || die('No direct access');

use external_value;
require_once($CFG->libdir . '/externallib.php');

/**
 * Video time element external service methods.
 */
class external extends \external_api {

    /**
     * Paramters definition for the methos to get list of video time instances.
     *
     * @return external_function_parameters
     */
    public static function get_instance_menu_parameters() {
        return new \external_function_parameters(
            [
                'query' => new external_value(PARAM_RAW, 'Query string'),
                'courseid' => new external_value(PARAM_INT, 'Course module id'),
                'limitfrom' => new external_value(PARAM_INT, 'limitfrom we are fetching the records from', VALUE_DEFAULT, 0),
                'limitnum' => new external_value(PARAM_INT, 'Number of records to fetch', VALUE_DEFAULT, 25),
            ]
        );
    }

    /**
     * Fetch the list of video time instance menu.
     *
     * @param string $query Query string
     * @param int $courseid Course module id.
     * @param int $limitfrom limitfrom we are fetching the records from.
     * @param array $limitnum Number of records to fetch.
     *
     * @return bool
     */
    public static function get_instance_menu($query, $courseid, $limitfrom, $limitnum) {
        global $DB;

        $params = self::validate_parameters(self::get_instance_menu_parameters(), [
            'query' => $query,
            'courseid' => $courseid,
            'limitfrom' => $limitfrom,
            'limitnum' => $limitnum,
        ]);

        $courseid = $params['courseid'];
        $sql = 'SELECT v.id, v.name
                FROM {videotime} v
                WHERE v.course=:course';

        $list = $DB->get_records_sql($sql, ['course' => $courseid]);

        return $list;
    }

    /**
     * Returns the list of instances menu.
     *
     * @return external_value True if data updated otherwise  returns false.
     */
    public static function get_instance_menu_returns() {
        return new \external_multiple_structure(
            new \external_single_structure(
                [
                    'id' => new external_value(PARAM_INT, 'Video time ID'),
                    'name' => new external_value(PARAM_RAW, 'Video time name'),
                ]
            )
        );
    }

    /**
     * Paramters definition for the methos verify the videotime progress of user.
     *
     * @return external_function_parameters
     */
    public static function verify_completion_parameters() {
        return new \external_function_parameters(
            [
                'instanceid' => new external_value(PARAM_INT, 'Query string'),
            ]
        );
    }

    /**
     * Verify the videotime progress of user.
     *
     * @param int $videotimeid Instance id of the videotime.
     *
     * @return bool
     */
    public static function verify_completion($videotimeid) {
        global $USER;

        $params = self::validate_parameters(self::verify_completion_parameters(), [
            'instanceid' => $videotimeid,
        ]);

        $videotimeid = $params['instanceid'];
        list($course, $cm) = get_course_and_cm_from_instance($videotimeid, 'videotime');
        self::validate_context(\context_module::instance($cm->id));

        // Get completion state.
        $completion = new \completion_info($course);
        $result = false;
        if ($completion->is_enabled($cm)) {
            $current = $completion->get_data($cm, false, $USER->id);
            $result = $current->completionstate == COMPLETION_COMPLETE ? true : false;
        }

        return $result;
    }

    /**
     * Result the status of videotime completion
     *
     * @return external_value True if data updated otherwise  returns false.
     */
    public static function verify_completion_returns() {
        return new external_value(PARAM_BOOL, 'Result of videotime completion');
    }
}
