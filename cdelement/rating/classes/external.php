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
 * Rating element external werbservice deifintion.
 *
 * @package   cdelement_rating
 * @copyright  2025 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace cdelement_rating;

defined('MOODLE_INTERNAL') || die('No direct access');

use core_external;
use external_value;

require_once($CFG->libdir . '/externallib.php');

/**
 * Chapter external service methods.
 */
class external extends core_external\external_api {

    /**
     * Paramters definition for the methos update rating response of user.
     *
     * @return \external_function_parameters
     */
    public static function store_rating_result_data_parameters() {
        return new \external_function_parameters(
            [
                'value' => new \external_value(PARAM_FLOAT, 'The rating value'),
                'rateid' => new \external_value(PARAM_INT, 'The rating id'),
                'contentdesignerid' => new \external_value(PARAM_INT, 'Content designer id'),
            ]
        );
    }

    /**
     * Store the user response for the rating.
     *
     * @param int $value rating response value.
     * @param array $rateid Rating id.
     * @param int $contentdesignerid Content designer id.
     * @return array
     */
    public static function store_rating_result_data($value, $rateid, $contentdesignerid) {
        global $DB, $USER;

        $vaildparams = self::validate_parameters(self::store_rating_result_data_parameters(),
        ['value' => $value, 'rateid' => $rateid, 'contentdesignerid' => $contentdesignerid]);

        $result = '';
        $average = null;
        $userid = $USER->id;
        $value = $vaildparams['value'];
        $rateid = $vaildparams['rateid'];
        $contentdesignerid = $vaildparams['contentdesignerid'];

        $cm = get_coursemodule_from_instance('contentdesigner', $contentdesignerid);
        self::validate_context(\context_module::instance($cm->id));

        if (!empty($value)) {

            if ($DB->record_exists('cdelement_rating_responses', ['ratingid' => $rateid, 'userid' => $userid])) {
                $record = $DB->get_record('cdelement_rating_responses', ['ratingid' => $rateid, 'userid' => $userid]);
                $record->ratingid = $rateid;
                $record->userid = $userid;
                $record->response = $value;
                $record->timemodified = time();
                $ratingid = $rateid;
                $DB->update_record('cdelement_rating_responses', $record);
            } else {
                $record = new \stdClass();
                $record->ratingid = $rateid;
                $record->userid = $userid;
                $record->response = $value;
                $record->timecreated = time();
                $ratingid = $DB->insert_record('cdelement_rating_responses', $record);
            }

            if (!empty($ratingid) && ($ratedata = $DB->get_record('cdelement_rating', ['id' => $rateid]))) {

                if ($ratedata->scale != \cdelement_rating\element::SCALENUMERIC) {
                    if ($ratedata->resulttype == \cdelement_rating\element::RESULTCOUNT) {
                        $result = \cdelement_rating\helper::get_count_response($ratedata, $value);
                        $average = \cdelement_rating\helper::get_most_selected_response_count($ratedata->id);
                    }

                } else {
                    if ($ratedata->resulttype == \cdelement_rating\element::RESULTAVERAGE) {
                        $result = \cdelement_rating\helper::get_average_response($rateid);
                        $average = \cdelement_rating\helper::get_numeric_average_value($rateid);
                    }
                }
            }
        }

        return [
            'average' => $average,
            'result' => ($ratedata->resulttype != \cdelement_rating\element::RESULTDISABLED) ? $result : '',
        ];
    }

    /**
     * Return a message.
     *
     * @return array message.
     */
    public static function store_rating_result_data_returns() {
        return new \external_single_structure(
            [
                'result' => new \external_value(PARAM_TEXT, 'Return rating result'),
                'average' => new \external_value(PARAM_FLOAT, 'Return average rating'),
            ]
        );
    }

}
