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
 * Privacy provider class of rating element.
 *
 * @package   cdelement_rating
 * @copyright  2025 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace cdelement_rating\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\userlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\approved_contextlist;

/**
 * Implementation of the privacy subsystem plugin provider for the rating element.
 *
 * @package   cdelement_rating
 * @copyright  2025 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
        \core_privacy\local\metadata\provider,
        // This plugin is capable of determining which users have data within it.
        \core_privacy\local\request\core_userlist_provider,
        \mod_contentdesigner\privacy\contentdesignerelements_provider {

    /**
     * Return the fields which contain personal data.
     *
     * @param collection $collection a reference to the collection to use to store the metadata.
     * @return collection the updated collection of metadata items.
     */
    public static function get_metadata(collection $collection): collection {

        $metadata = [
            'ratingid' => 'privacy:metadata:cdelement_rating_responses:ratingid',
            'userid' => 'privacy:metadata:cdelement_rating_responses:userid',
            'response' => 'privacy:metadata:cdelement_rating_responses:response',
            'timemodified' => 'privacy:metadata:cdelement_rating_responses:timemodified',
        ];

        $collection->add_database_table(
            'cdelement_rating_responses',
            $metadata,
            'privacy:metadata:ratingresponses'
        );

        return $collection;
    }

    /**
     * Helper function to export completions.
     *
     * The array of "completions" is actually the result returned by the SQL in export_user_data.
     * It is more of a list of sessions. Which is why it needs to be grouped by context id.
     *
     * @param array $contentdesignerids Array of completions to export the logs for.
     * @param stdclass $user User record object.
     * @return array
     */
    public static function export_element_user_data(array $contentdesignerids, \stdclass $user) {
        global $DB;

        // Prepare SQL to fetch rating element data for the user.
        list($insql, $inparams) = $DB->get_in_or_equal($contentdesignerids, SQL_PARAMS_NAMED);

        $sql = "SELECT ra.id, ra.contentdesignerid, ra.title AS title,
        rp.ratingid, rp.response , rp.timemodified
        FROM {cdelement_rating} ra
        INNER JOIN {cdelement_rating_responses} rp ON rp.ratingid = ra.id AND rp.userid = :userid
        WHERE ra.contentdesignerid {$insql}
        ORDER BY ra.id";

        $params = ['userid' => $user->id];
        $records = $DB->get_records_sql($sql, $params + $inparams);

        $data = [];
        foreach ($records as $record) {
            // Check if the rating ID already exists in $data.
            if (!isset($data[$record->ratingid])) {
                $data[$record->ratingid] = (object) [
                    'title' => !empty($record->title) ? $record->title : '',
                    'ratingid' => $record->ratingid,
                    'response' => $record->response,
                    'contentdesignerid' => $record->contentdesignerid,
                    'timemodified' => isset($record->timemodified) ? userdate($record->timemodified) : null,
                ];
            }
        }

        return $data;
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        if (!$context instanceof \context_module) {
            return;
        }

        $params = [
            'instanceid' => $context->instanceid,
        ];

        // Discussion authors.
        $sql = "SELECT rp.userid
        FROM {cdelement_rating} ra
        JOIN {cdelement_rating_responses} rp ON rp.ratingid = ra.id
        WHERE ra.contentdesignerid = :instanceid";
        $userlist->add_from_sql('userid', $sql, $params);

    }

    /** Delete all options and answers data for all users in the specified context.
     *
     * @param context $context Context to delete data from.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        if ($context->contextlevel != CONTEXT_MODULE) {
            return;
        }

        $cm = get_coursemodule_from_id('contentdesigner', $context->instanceid);
        if (!$cm) {
            return;
        }
        $list = $DB->get_records('cdelement_rating', ['contentdesignerid' => $cm->instance]);
        $ids = array_column($list, 'id');
        list($insql, $inparams) = $DB->get_in_or_equal($ids, SQL_PARAMS_NAMED, 'ch');
        $DB->delete_records_select('cdelement_rating_responses', "instance $insql", $inparams);

    }

    /**
     * Delete multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        $context = $userlist->get_context();
        $cm = $DB->get_record('course_modules', ['id' => $context->instanceid]);
        $contentdesigner = $DB->get_record('contentdesigner', ['id' => $cm->instance]);

        list($userinsql, $userinparams) = $DB->get_in_or_equal($userlist->get_userids(), SQL_PARAMS_NAMED, 'usr');
        $list = $DB->get_records('cdelement_rating', ['contentdesignerid' => $contentdesigner->id]);
        $ids = array_column($list, 'id');
        list($insql, $inparams) = $DB->get_in_or_equal($ids, SQL_PARAMS_NAMED, 'ch');
        $DB->delete_records_select('cdelement_rating_responses', "userid {$userinsql} AND instance $insql ",
            $userinparams + $inparams );
    }

    /**
     * Delete user completion data for multiple context.
     *
     * @param approved_contextlist $contextlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            $instanceid = $DB->get_field('course_modules', 'instance', ['id' => $context->instanceid], MUST_EXIST);
            $list = $DB->get_records('cdelement_rating', ['contentdesignerid' => $instanceid]);
            $ids = array_column($list, 'id');
            list($insql, $inparams) = $DB->get_in_or_equal($ids, SQL_PARAMS_NAMED, 'ch');
            $DB->delete_records_select('cdelement_rating_responses', "userid=:userid AND instance $insql",
                ['userid' => $userid] + $inparams);
        }
    }
}
