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
 * Definition backup-activity-task
 *
 * @package   mod_contentdesigner
 * @copyright 2022, bdecent gmbh bdecent.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die('No direct access !');

require_once($CFG->dirroot . '/mod/contentdesigner/backup/moodle2/backup_contentdesigner_stepslib.php');

/**
 * Step to perform instance database backup.
 */
class backup_contentdesigner_activity_task extends backup_activity_task {

    /**
     * No specific settings for this activity
     */
    public function define_my_settings() {
        // Content deisnger don't have any specified settings.
    }

    /**
     * Define backup structure steps to store the instance data in the contentdesigner.xml.
     */
    public function define_my_steps() {
        // Only single structure step.
        $this->add_step(new backup_contentdesigner_activity_structure_step('contentdesigner_structure', 'contentdesigner.xml'));

        $this->add_step(new backup_calculate_question_categories('activity_question_categories'));

        $this->add_step(new backup_delete_temp_questions('clean_temp_questions'));
    }

    /**
     * No content encoding needed for this activity
     *
     * @param string $content some HTML text that eventually contains URLs to the activity instance scripts
     * @return string the same content with no changes
     */
    public static function encode_content_links($content) {
        return $content;
    }
}
