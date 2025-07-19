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
 * Privacy Subsystem implementation for cdelement_question.
 *
 * @package    cdelement_question
 * @category   privacy
 * @copyright  2024 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace cdelement_question\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\userlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\approved_contextlist;

/**
 * Implementation of the privacy subsystem plugin provider for the question element.
 *
 * @package   cdelement_question
 * @copyright  2024 bdecent gmbh <https://bdecent.de>
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

        $questionattemptsmetadata = [
            'contentdesignerid' => 'privacy:metadata:cdelement_question_attempts:contentdesignerid',
            'userid' => 'privacy:metadata:cdelement_question_attempts:userid',
            'uniqueid' => 'privacy:metadata:cdelement_question_attempts:uniqueid',
            'status' => 'privacy:metadata:cdelement_question_attempts:status',
            'timemodified' => 'privacy:metadata:cdelement_question_attempts:timemodified',
        ];

        $questionslotsmetadata = [
            'instanceid' => 'privacy:metadata:cdelement_question_slots:instanceid',
            'userid' => 'privacy:metadata:cdelement_question_slots:userid',
            'slot' => 'privacy:metadata:cdelement_question_slots:slot',
            'status' => 'privacy:metadata:cdelement_question_slots:status',
        ];

        $collection->add_database_table( 'cdelement_question_attempts', $questionattemptsmetadata,
            'privacy:metadata:questionattempts');
        $collection->add_database_table( 'cdelement_question_slots', $questionslotsmetadata, 'privacy:metadata:questionslots');

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

        // Prepare SQL to fetch question element data for the user.
        list($insql, $inparams) = $DB->get_in_or_equal($contentdesignerids, SQL_PARAMS_NAMED);
        $sql = "SELECT q.id as questionelementid, q.title as title, q.categoryid as categoryid,
                q.questionid as questionid, q.preferredbehaviour as behaviour,
                q.contentdesignerid as contentdesignerid, qa.*, qs.*
                FROM {cdelement_question} q
                INNER JOIN {cdelement_question_attempts} qa ON qa.contentdesignerid = q.contentdesignerid AND qa.userid = :userid
                INNER JOIN {cdelement_question_slots} qs ON qs.instanceid = q.id  AND qs.userid = qa.userid
                WHERE q.contentdesignerid $insql";

        $params = ['userid' => $user->id];
        $questionrecords = $DB->get_records_sql($sql, array_merge($params, $inparams));
        $data = [];

        foreach ($questionrecords as $record) {
            if (!isset($data[$record->questionelementid])) {
                $data[$record->questionelementid] = (object) [
                    'title' => $record->title,
                    'categoryid' => $record->categoryid,
                    'questionid' => $record->questionid,
                    'behaviour' => $record->behaviour,
                    'contentdesignerid' => $record->contentdesignerid,
                ];
            }

            $data[$record->questionelementid]->questionattempts[] = (object) [
                'uniqueid' => $record->uniqueid,
                'status' => $record->status,
                'contentdesignerid' => $record->contentdesignerid,
                'timemodified' => isset($record->timemodified) ? userdate($record->timemodified) : null,
            ];

            $data[$record->questionelementid]->questionslots[] = (object) [
                'instanceid' => $record->instanceid,
                'slot' => $record->slot,
                'status' => $record->status,
            ];
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
        $sql = "SELECT qa.userid
        FROM {cdelement_question} q
        JOIN {cdelement_question_attempts} qa ON qa.contentdesignerid = q.contentdesignerid
        JOIN {cdelement_question_slots} qs ON qs.instanceid = q.id
        WHERE q.contentdesignerid = :instanceid";
        $userlist->add_from_sql('userid', $sql, $params);
    }

    /**
     * Delete all options and answers data for all users in the specified context.
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
        $list = $DB->get_records('cdelement_question', ['contentdesignerid' => $cm->instance]);
        $ids = array_column($list, 'id');
        list($insql, $inparams) = $DB->get_in_or_equal($ids, SQL_PARAMS_NAMED, 'ch');
        $DB->delete_records_select('cdelement_question_slots', "instance $insql", $inparams);
        $DB->delete_records('cdelement_question_attempts', ['contentdesignerid' => $cm->instance]);

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
        $list = $DB->get_records('cdelement_question', ['contentdesignerid' => $contentdesigner->id]);
        $ids = array_column($list, 'id');
        list($insql, $inparams) = $DB->get_in_or_equal($ids, SQL_PARAMS_NAMED, 'ch');
        $DB->delete_records_select('cdelement_question_slots', "userid {$userinsql} AND instance $insql ",
            $userinparams + $inparams );
        $params = ['contentdesignerid' => $contentdesigner->id];
        $DB->delete_records_select('cdelement_question_attempts', "userid {$userinsql} AND contentdesignerid=:contentdesignerid",
            $params + $userinparams);
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
            $list = $DB->get_records('cdelement_question', ['contentdesignerid' => $instanceid]);
            $ids = array_column($list, 'id');
            list($insql, $inparams) = $DB->get_in_or_equal($ids, SQL_PARAMS_NAMED, 'ch');
            $DB->delete_records_select('cdelement_question_slots', "userid=:userid AND instance $insql",
                ['userid' => $userid] + $inparams);
            $params = ['contentdesignerid' => $instanceid, 'userid' => $userid];
            $DB->delete_records_select('cdelement_question_attempts', "userid=:userid AND contentdesignerid=:contentdesignerid",
                $params);
        }
    }
}
