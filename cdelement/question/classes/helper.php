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
 * Element question - helper to manage user attempts for moduel based.
 *
 * @package   cdelement_question
 * @copyright  2024 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace cdelement_question;

/**
 * Helper class for managing question attempts and slots for a specific Content Designer activity.
 */
class helper {

    /**
     * @var int The ID of the Content Designer instance.
     */
    protected $contentdesignerid;

    /**
     * Constructor for the helper class.
     *
     * @param int $contentdesignerid The ID of the Content Designer activity.
     */
    public function __construct(int $contentdesignerid) {
        $this->contentdesignerid = $contentdesignerid;
    }

    /**
     * Factory method to create an instance of the helper class.
     *
     * @param int $contentdesignerid The ID of the Content Designer activity.
     * @return self An instance of the helper class.
     */
    public static function instance($contentdesignerid) {
        return new self($contentdesignerid);
    }

    /**
     * Finds a usage record for a given user in the Content Designer activity.
     *
     * @param int $userid The ID of the user.
     * @param int|null $cdattemptid The ID of the attempt.
     * @return object|bool The usage record object if found, otherwise false.
     */
    public function find_usage(int $userid, $cdattemptid = null) {
        global $DB;

        $conditions = [
            'userid' => $userid,
            'contentdesignerid' => $this->contentdesignerid,
        ];

        if ($cdattemptid !== null) {
            $conditions['cdattemptid'] = $cdattemptid;
        }

        $record = $DB->get_record('cdelement_question_attempts', $conditions, '*', IGNORE_MULTIPLE);

        return $record ?? false;
    }

    /**
     * Finds a usage record for a given user in the Content Designer activity.
     *
     * @param int $userid The ID of the user.
     * @param int $uniqueid Unique id of the question attempt.
     * @return object|bool The usage record object if found, otherwise false.
     */
    public static function find_usage_by_uniqueid(int $userid, $uniqueid = null) {
        global $DB;

        $conditions = [
            'userid' => $userid,
            'uniqueid' => $uniqueid,
        ];

        $record = $DB->get_record('cdelement_question_attempts', $conditions, '*', IGNORE_MULTIPLE);

        return $record ?? false;
    }

    /**
     * Updates or creates a new usage record for a user in the Content Designer activity.
     *
     * @param int $qubaid The unique ID for the question usage (Question Usage by Activity).
     * @param int $userid The ID of the user.
     * @param int $contentid The ID of the content item.
     * @param int $slot The slot number of the question.
     * @param int $cdattemptid The ID of the attempt.
     * @return object|bool The updated or newly created usage record.
     */
    public function update_new_usage(int $qubaid, int $userid, int $contentid, int $slot, int $cdattemptid) {
        global $DB;

        // Check if an attempt already exists for the user.
        if ($attempt = $this->find_usage($userid, $cdattemptid)) {
            $attempt->uniqueid = $qubaid;
            $DB->update_record('cdelement_question_attempts', $attempt);
        } else {
            // Create a new record if no attempt exists.
            $data = [
                'contentdesignerid' => $this->contentdesignerid,
                'userid' => $userid,
                'uniqueid' => $qubaid,
                'cdattemptid' => $cdattemptid,
                'timemodified' => time(),
            ];

            $DB->insert_record('cdelement_question_attempts', $data);
        }

        // Update the slot information.
        $this->update_slot($userid, $contentid, $slot, $cdattemptid);

        // Return the updated or newly created usage record.
        return $this->find_usage($userid, $cdattemptid);
    }

    /**
     * Retrieves the slot information for a user and content item.
     *
     * @param int $userid The ID of the user.
     * @param int $contentid The ID of the content item.
     * @param int $cdattemptid The ID of the attempt.
     * @return object|null The slot record if found, otherwise null.
     */
    public function get_slot($userid, $contentid, $cdattemptid) {
        global $DB;

        return $DB->get_record('cdelement_question_slots', ['instanceid' => $contentid,
            'userid' => $userid, 'cdattemptid' => $cdattemptid]);
    }

    /**
     * Updates or creates a slot record for a user and content item.
     *
     * @param int $userid The ID of the user.
     * @param int $contentid The ID of the content item.
     * @param int $slot The slot number of the question.
     * @param int $cdattemptid The ID of the attempt.
     * @param bool $status The status of the slot (default: true).
     */
    public function update_slot(int $userid, int $contentid, int $slot, int $cdattemptid, bool $status=true) {
        global $DB;
        if ($slotdata = $DB->get_record('cdelement_question_slots', ['instanceid' => $contentid,
            'userid' => $userid, 'cdattemptid' => $cdattemptid])) {
            // Update the existing slot record with the new slot and status.
            $slotdata->slot = $slot;
            $slotdata->status = $status;
            $DB->update_record('cdelement_question_slots', $slotdata);
        } else {
            // Create a new slot record if none exists.
            $data = [
                'instanceid' => $contentid,
                'cdattemptid' => $cdattemptid,
                'userid' => $userid,
                'slot' => $slot,
                'status' => (int) $status,
            ];

            $DB->insert_record('cdelement_question_slots', $data);
        }
    }

    /**
     * Updates the status of a specific slot record.
     *
     * @param int $slotid The ID of the slot record.
     * @param bool $status The new status to set for the slot.
     */
    public function update_slot_status(int $slotid, bool $status) {
        global $DB;

        $DB->set_field('cdelement_question_slots', 'status', (int) $status, ['id' => $slotid]);
    }
}
