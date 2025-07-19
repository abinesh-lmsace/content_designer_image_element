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
 * Content designer activity report helper.
 *
 * @package   cdaddon_report
 * @copyright 2024, bdecent gmbh bdecent.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace cdaddon_report;

/**
 * Filter form for the templates table.
 */
class report_table_filter extends \moodleform {

    /** Constants for the attempt method. */
    const NONE = 0;

    /** Constants for the attempt method. */
    const BESTATTEMPT = 1;

    /** Constants for the attempt method. */
    const LASTATTEMPT = 2;

    /** Constants for the attempt method. */
    const SPECIFICATTEMPT = 3;

    /**
     * Filter form elements defined.
     *
     * @return void
     */
    public function definition() {
        $mform =& $this->_form;

        $mform->addElement('html', \html_writer::tag('h3', get_string('filter')));
        $list = [
            self::NONE => get_string('none'),
            self::BESTATTEMPT => get_string('bestattempt', 'mod_contentdesigner'),
            self::LASTATTEMPT => get_string('lastattempt', 'mod_contentdesigner'),
            self::SPECIFICATTEMPT => get_string('specificattempt', 'mod_contentdesigner'),
        ];
        $mform->addElement('select', 'attemptmethod', get_string('attemptmethod', 'mod_contentdesigner'), $list);

        $mform->addElement('text', 'attemptnumber', get_string('attemptnumber', 'mod_contentdesigner'));
        $mform->setType('attemptnumber', PARAM_INT);
        $mform->hideIf('attemptnumber', 'attemptmethod', 'neq', self::SPECIFICATTEMPT);

        $this->add_action_buttons(false, get_string('filter'));
    }
}
