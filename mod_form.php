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
 * Content designer module instance add and update form.
 *
 * @package    mod_contentdesigner
 * @copyright  2022 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/contentdesigner/lib.php');

/**
 * Content designer module form.
 */
class mod_contentdesigner_mod_form extends moodleform_mod {

    /**
     * Define the mform elements.
     * @return void
     */
    public function definition() {
        global $CFG;

        $mform =& $this->_form;

        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('name'), ['size' => '48']);
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $this->standard_intro_elements();

        $this->navigation_definition($mform);

        // Enable grading checkbox.
        $enablegrading = $mform->createElement('advcheckbox', 'enablegrading',
            get_string('enablegrading', 'contentdesigner'));

        // Auto-start attempts checkbox.
        $autostartattempts = $mform->createElement('advcheckbox', 'autostartattempts',
            get_string('autostartattempts', 'contentdesigner'));

        // Grade settings.
        $this->standard_grading_coursemodule_elements();
        $mform->insertElementBefore($enablegrading, 'grade');
        $mform->insertElementBefore($autostartattempts, 'grade');

        $mform->addHelpButton('enablegrading', 'enablegrading', 'contentdesigner');
        $mform->addHelpButton('autostartattempts', 'autostartattempts', 'contentdesigner');

        // Number of attempts.
        $attemptoptions = ['0' => get_string('unlimited')];
        for ($i = 1; $i <= CONTENTDESIGNER_MAX_ATTEMPT_OPTION; $i++) {
            $attemptoptions[$i] = $i;
        }
        $mform->addElement('select', 'attemptsallowed', get_string('attemptsallowed', 'contentdesigner'),
                $attemptoptions);

        // Grading method.
        $mform->addElement('select', 'grademethod', get_string('grademethod', 'contentdesigner'),
        contentdesigner_get_grading_options());
        $mform->addHelpButton('grademethod', 'grademethod', 'contentdesigner');

        // Hide grade settings when grading disabled.
        $mform->hideIf('grade', 'enablegrading', 'notchecked');
        $mform->hideIf('gradecat', 'enablegrading', 'notchecked');
        $mform->hideIf('gradepass', 'enablegrading', 'notchecked');
        $mform->hideIf('attemptsallowed', 'enablegrading', 'notchecked');
        $mform->hideIf('grademethod', 'enablegrading', 'notchecked');
        $mform->hideIf('autostartattempts', 'enablegrading', 'notchecked');
        $mform->hideIf('completionusegrade', 'enablegrading', 'notchecked');

        $this->standard_coursemodule_elements();

        $this->add_action_buttons();
    }

    /**
     * Custom completion rules definition.
     *
     * @return array
     */
    public function add_completion_rules() {
        global $CFG;
        $mform = $this->_form;

        if ($CFG->branch >= 403) {
            $suffix = $this->get_suffix();
            $completionendreach = 'completionendreach' . $suffix;
            $completionmandatory = 'completionmandatory' . $suffix;
        } else {
            $completionendreach = 'completionendreach';
            $completionmandatory = 'completionmandatory';
        }

        $mform->addElement('checkbox', $completionendreach, get_string('completionendreach', 'contentdesigner') );
        $mform->setDefault($completionendreach, 0);
        $mform->addHelpButton($completionendreach, 'completionendreach', 'contentdesigner');

        $mform->addElement('checkbox', $completionmandatory, get_string('completionmandatory', 'contentdesigner') );
        $mform->setDefault($completionmandatory, 0);
        $mform->addHelpButton($completionmandatory, 'completionmandatory', 'contentdesigner');

        return [$completionendreach, $completionmandatory];
    }

    /**
     * Navigation method form fields.
     *
     * @param moodle_form $mform
     */
    public function navigation_definition(&$mform) {

        $mform->addElement('header', 'navigationhead', get_string('navigationhead', 'contentdesigner'));
        $options = [
            CONTENTDESIGNER_NAVIGATION_SEQUENTIAL => get_string('sequential', 'contentdesigner'),
            CONTENTDESIGNER_NAVIGATION_FREE => get_string('free', 'contentdesigner'),
        ];
        $default = get_config('mod_contentdesigner', 'navigation');
        $mform->addElement('select', 'navigation', get_string('navigation', 'contentdesigner'), $options);
        $mform->addHelpButton('navigation', 'navigation', 'contentdesigner');
        $mform->setDefault('navigation', $default ?: CONTENTDESIGNER_NAVIGATION_SEQUENTIAL);
    }

    /**
     * Validate the form to check custom completion has selected conditions.
     *
     * @param array $data Input data not yet validated.
     * @return bool True if one or more rules is enabled, false if none are.
     */
    public function completion_rule_enabled($data) {
        return (!empty($data['completionendreach']) || !empty($data['completionmandatory']));
    }

    /**
     * Get the maximum number of attempts available for any user on this quiz.
     *
     * @return int the maximum number of attempts allowed in any override, or for the quiz, or 0 if unlimited.
     */
    protected function get_max_attempts_for_any_override() {
        global $DB;

        $maxattempts = $DB->get_field('contentdesigner', 'attempts', ['id' => $this->_instance]);
        if ($maxattempts == 0) {
            return 0;
        }

        if (empty($this->_instance)) {
            return $maxattempts;
        }

        $overridemaxattempts = $DB->get_field_sql("
                SELECT MAX(attempts)
                FROM {contentdesigner_overrides}
                WHERE contentdesigner = ?",
                [$this->_instance]);

        if ($overridemaxattempts === null) {
            return $maxattempts;
        }

        return max($maxattempts, $overridemaxattempts);
    }

    /**
     * Validate the form data.
     *
     * @param array $data Form data.
     * @param array $files Form files.
     * @return array Array of errors.
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        return $errors;
    }
}
