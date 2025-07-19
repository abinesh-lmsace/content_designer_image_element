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
 * Poll element external werbservice deifintion.
 *
 * @package   cdelement_poll
 * @copyright  2024 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace cdelement_poll;

defined('MOODLE_INTERNAL') || die('No direct access');

use external_value;
require_once($CFG->libdir . '/externallib.php');

/**
 * Chapter external service methods.
 */
class external extends \external_api {

    /**
     * Paramters definition for the methos update poll response of user.
     *
     * @return external_function_parameters
     */
    public static function store_poll_result_data_parameters() {
        return new \external_function_parameters(
            [
                'pollid' => new \external_value(PARAM_INT, 'The poll id'),
                'optionids' => new \external_multiple_structure(new \external_value(PARAM_INT, 'options ID',
                        VALUE_REQUIRED, '', NULL_NOT_ALLOWED), 'Array of option IDs', VALUE_DEFAULT, []),
                'instanceid' => new \external_value(PARAM_INT, 'Content designer id'),
            ]
        );
    }

    /**
     * Store the user response for the poll.
     *
     * @param int $pollid Poll ID.
     * @param array $optionids Result option id from the poll.
     * @param int $instanceid Content designer id.
     * @return bool
     */
    public static function store_poll_result_data($pollid, $optionids, $instanceid) {
        global $DB, $USER;

        $vaildparams = self::validate_parameters(self::store_poll_result_data_parameters(),
        ['pollid' => $pollid, 'optionids' => $optionids, 'instanceid' => $instanceid]);

        $contentdesignerid = $vaildparams['instanceid'];
        $cm = get_coursemodule_from_instance('contentdesigner', $contentdesignerid);
        self::validate_context(\context_module::instance($cm->id));

        $userid = $USER->id;
        $formanswer = $vaildparams['optionids'];
        $poll = $DB->get_record('cdelement_poll', ['id' => $vaildparams['pollid'],
            'contentdesignerid' => $contentdesignerid]);

        if (!empty($formanswer)) {

            if (is_array($formanswer)) {
                $formanswers = $formanswer;
            } else {
                $formanswers = [$formanswer];
            }

            $current = $DB->get_records('cdelement_poll_answers', ['pollid' => $poll->id, 'userid' => $userid]);

            $existinganswers = array_map(function($answer) {
                return $answer->optionid;
            }, $current);

            $deletedanswersnapshots = [];

            if ($current) {
                // Update an existing answer.
                foreach ($current as $c) {
                    if (in_array($c->optionid, $formanswers)) {
                        $DB->set_field('cdelement_poll_answers', 'timemodified', time(), ['id' => $c->id]);
                    } else {
                        $deletedanswersnapshots[] = $c;
                        $DB->delete_records('cdelement_poll_answers', ['id' => $c->id]);
                    }
                }

                // Add new ones.
                foreach ($formanswers as $f) {
                    if (!in_array($f, $existinganswers)) {
                        $newanswer = new \stdClass();
                        $newanswer->optionid = $f;
                        $newanswer->pollid = $poll->id;
                        $newanswer->userid = $userid;
                        $newanswer->timemodified = time();
                        $DB->insert_record("cdelement_poll_answers", $newanswer);
                    }
                }
                return true;
            } else {
                // Add new answer.
                foreach ($formanswers as $answer) {
                    $newanswer = new \stdClass();
                    $newanswer->pollid = $poll->id;
                    $newanswer->userid = $userid;
                    $newanswer->optionid = $answer;
                    $newanswer->timemodified = time();
                    $DB->insert_record("cdelement_poll_answers", $newanswer);
                }
                return true;
            }
        }
        return false;
    }

    /**
     * Returns the result data.
     *
     * @return external_value True if data updated otherwise  returns false.
     */
    public static function store_poll_result_data_returns() {
        return new \external_value(PARAM_BOOL, 'Result of stored user response');
    }
}
