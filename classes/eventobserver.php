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
 * Event observer for the content designer module.
 *
 * @package    mod_contentdesigner
 * @copyright  2024 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_contentdesigner;

/**
 * Event observer for the content designer module.
 *
 * @package    mod_contentdesigner
 * @copyright  2024 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class eventobserver {

    /**
     * Start time save form the course module viewed by the user in first time.
     *
     * @param  mixed $event
     */
    public static function course_module_starttime($event) {
        global $DB;
        $cm = get_coursemodule_from_id('contentdesigner', $event->contextinstanceid);
        $contentdesigner = $DB->get_record('contentdesigner', ['id' => $cm->instance]);
        if (!$DB->record_exists('contentdesigner_completion', ['contentdesignerid' => $cm->instance,
                'userid' => $event->userid])) {
                $data = new \stdclass();
                $data->contentdesignerid = $contentdesigner->id;
                $data->userid = $event->userid;
                $data->completion = 0;
                $data->mandatorycompletion = 0;
                $data->starttime = $event->timecreated;
                $data->timecreated = time();
                $DB->insert_record('contentdesigner_completion', $data);
        }
    }
}
