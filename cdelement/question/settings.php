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
 * Question element - Admin settings.
 *
 * @package   cdelement_question
 * @copyright 2024 bdecent gmbh <https://bdecent.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use cdelement_question\element;

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

    global $CFG;

    require_once($CFG->dirroot . '/lib/questionlib.php');

    $hiddenorvisible = [
        question_display_options::HIDDEN => get_string('notshown', 'question'),
        question_display_options::VISIBLE => get_string('shown', 'question'),
    ];

    $marksoptions = [
        question_display_options::HIDDEN => get_string('notshown', 'question'),
        question_display_options::MAX_ONLY => get_string('showmaxmarkonly', 'question'),
        question_display_options::MARK_AND_MAX => get_string('showmarkandmax', 'question'),
    ];

    // Behaviour.
    $behaviours = \question_engine::get_behaviour_options('');
    $page->add(new admin_setting_configselect(
        'cdelement_question/behaviour', get_string('howquestionbehaves', 'mod_contentdesigner'),
        get_string('howquestionbehavesdesc', 'mod_contentdesigner'), 'adaptive', $behaviours));

    // Mandatory.
    $options = [
        element::DISBLE_MANDATORY => get_string('no'),
        element::ENABLE_MANDATORY => get_string('yes'),
    ];
    $page->add(new admin_setting_configselect('cdelement_question/mandatory',
        get_string('mandatory', 'mod_contentdesigner'),
        get_string('mandatory_help', 'mod_contentdesigner'), 1, $options));

    // Whether correct.
    $page->add(new admin_setting_configselect('cdelement_question/correctness',
        get_string('whethercorrect', 'question'),
        get_string('whethercorrectdesc', 'mod_contentdesigner'),
        1, $hiddenorvisible));

    // Show marks.
    $page->add(new admin_setting_configselect('cdelement_question/marks',
            get_string('marks', 'question'),
            get_string('showmarksdesc', 'mod_contentdesigner'), 2, $marksoptions));

    // Decimal places in grades.
    $page->add(new admin_setting_configselect('cdelement_question/decimalplaces',
            get_string('decimalplaces', 'quiz'), get_string('decimalplacesdesc', 'mod_contentdesigner'),
            2, question_engine::get_dp_options()));

    // Specific feedback.
    $page->add(new admin_setting_configselect('cdelement_question/feedback',
            get_string('specificfeedback', 'question'),
            get_string('specificfeedbackdesc', 'mod_contentdesigner'),
            1, $hiddenorvisible));

    // General feedback.
    $page->add(new admin_setting_configselect('cdelement_question/generalfeedback',
            get_string('generalfeedback', 'question'),
            get_string('generalfeedbackdesc', 'mod_contentdesigner'),
            1, $hiddenorvisible));

    // Right answer.
    $page->add(new admin_setting_configselect('cdelement_question/rightanswer',
            get_string('rightanswer', 'question'),
            get_string('rightanswerdesc', 'mod_contentdesigner'),
            0, $hiddenorvisible));

    // Response history.
    $page->add(new admin_setting_configselect('cdelement_question/history',
            get_string('responsehistory', 'question'),
            get_string('responsehistorydesc', 'mod_contentdesigner'),
            0, $hiddenorvisible));

     // Response history.
    $page->add(new admin_setting_configselect('cdelement_question/history',
            get_string('responsehistory', 'question'),
            get_string('responsehistorydesc', 'mod_contentdesigner'),
            0, $hiddenorvisible));

    // Force lanaguage.
    $languages = get_string_manager()->get_list_of_translations();
    $languages = ['' => get_string('forceno')] + $languages;
    if ($languages) {
        $page->add(new admin_setting_configselect('cdelement_question/forcedlanguage', get_string('forcelanguage'), '', 0,
            $languages));
    }

}
