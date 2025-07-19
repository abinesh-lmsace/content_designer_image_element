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
 * External functions for contentdesigner. Helps to maintain the completion.
 *
 * @package    mod_contentdesigner
 * @copyright  2022 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_contentdesigner;

defined('MOODLE_INTERNAL') || die('No direct access');

global $CFG;

if ($CFG->branch <= 401) {
    require_once($CFG->libdir.'/externallib.php');
}

use core_external\external_value;
use core_external\external_function_parameters;

/**
 * External methods to update completion for contentdesigner.
 */
class external extends \core_external\external_api {

    /**
     * Paramters definition for the methos update completion state of user.
     *
     * @return external_function_parameters
     */
    public static function update_completion_parameters() {
        return new external_function_parameters(
            [
                'contentdesignerid' => new external_value(PARAM_INT, 'Course module id'),
                'method' => new external_value(PARAM_TEXT, 'Completion method'),
            ]
        );
    }

    /**
     * Update the content designer module completion state for the current logged in user.
     *
     * @param int $contentdesignerid Content designer module instance id.
     * @param string $method Completion method name.
     *
     * @return bool true if everything updated fine, false if not.
     */
    public static function update_completion($contentdesignerid, $method) {
        global $DB, $USER;

        $vaildparams = self::validate_parameters(self::update_completion_parameters(),
        ['contentdesignerid' => $contentdesignerid, 'method' => $method]);

        $contentdesignerid = $vaildparams['contentdesignerid'];
        $method = $vaildparams['method'];

        $cm = get_coursemodule_from_instance('contentdesigner', $contentdesignerid);
        self::validate_context(\context_module::instance($cm->id));

        $record = $DB->get_record('contentdesigner_completion', ['contentdesignerid' => $contentdesignerid, 'userid' => $USER->id]);
        $contentdesigner = $DB->get_record('contentdesigner', ['id' => $contentdesignerid]);
        $data = new \stdclass();
        $data->contentdesignerid = $contentdesignerid;
        $data->userid = $USER->id;

        if ($method == 'completionendreach' && $contentdesigner->completionendreach) {
            $data->completion = true;
        } else if ($method == 'completionmandatory' && $contentdesigner->completionmandatory) {
            $data->mandatorycompletion = true;
        }

        $data->timecreated = time();

        $cm = get_coursemodule_from_instance('contentdesigner', $contentdesignerid);
        list ($course, $cm) = get_course_and_cm_from_cmid($cm->id, 'contentdesigner');
        $completion = new \completion_info($course);

        if (isset($record->id)) {
            $data->id = $record->id;
            if ($completion->is_enabled($cm) &&
                ($contentdesigner->completionendreach || $contentdesigner->completionmandatory)) {
                $completion->update_state($cm, COMPLETION_COMPLETE);
            }
            $DB->update_record('contentdesigner_completion', $data);
        } else {
            $data->timecreated = time();
            if ($DB->insert_record('contentdesigner_completion', $data)) {
                if ($completion->is_enabled($cm) &&
                    ($contentdesigner->completionendreach || $contentdesigner->completionmandatory)) {
                    $completion->update_state($cm, COMPLETION_COMPLETE);
                }
                return true;
            } else {
                return false;
            }
        }
        return true;
    }

    /**
     * Returns the updated result of module completion.
     *
     * @return external_value True if state updated otherwise  returns false.s
     */
    public static function update_completion_returns() {
        return new external_value(PARAM_BOOL, 'Result of stored user response');
    }
}
