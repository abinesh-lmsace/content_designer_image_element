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
 * Form for editing a general element.
 *
 * @package    cdelement_question
 * @copyright  2024 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace cdelement_question;

/**
 * General option form to create elements.
 *
 * @copyright 2024 bdecent gmbh <https://bdecent.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class general_element_form extends \mod_contentdesigner\form\general_element_form {

    /**
     * Make the custom data as public varaible to access on the elements forms.
     *
     * @var array
     */
    public $_customdata;

    /**
     * Define the form.
     */
    public function definition() {
        global $PAGE, $DB;

        $mform = $this->_form;
        $element = $this->_customdata['elementobj'];
        $instanceid = $this->_customdata['instanceid'];
        $cmid = $this->_customdata['cmid'];
        $courseid = $this->_customdata['courseid'];
        $questionid = $this->_customdata['questionid'] ?? '';

        $config = get_config('cdelement_question');

        $mform->addElement('header', 'elementsettings',
            get_string('elementsettings', 'mod_contentdesigner', ucfirst($element->element_name())));

        // Course id.
        $mform->addElement('hidden', 'courseid', $courseid);
        $mform->setType('courseid', PARAM_INT);

        $mform->addElement('hidden', 'cmid', $cmid);
        $mform->setType('cmid', PARAM_INT);

        $category = $DB->get_field('course', 'category', ['id' => $courseid]);

        $mform->addElement('questioncategory', 'categoryid', get_string('category', 'question'), [
            'contexts' => [
                \context_system::instance(),
                \context_coursecat::instance($category),
                \context_course::instance($courseid),
                \context_module::instance($cmid),
            ],
            'top' => true,
            'currentcat' => 0,
            'nochildrenof' => -1,
        ]);

        // Cmid.
        $options = [
            'ajax' => 'cdelement_question/form-question-selector',
        ];
        $mform->addElement('autocomplete', 'questionid', get_string('question'), [], $options);
        $mform->addRule('questionid', null, 'required', null, 'client');
        $mform->disabledIf('questionid', 'categoryid', 'eq', '');

        $options = [
            'ajax' => 'cdelement_question/form-questionversion-selector',
        ];
        $mform->addElement('autocomplete', 'questionversion', get_string('version'),
            $versions ?? [ 0 => get_string('alwayslatest', 'mod_quiz')], $options);
        $mform->addRule('questionversion', null, 'required', null, 'client');

        $behaviours = \question_engine::get_behaviour_options($this->_customdata['behaviour'] ?? '');
        $mform->addElement('select', 'preferredbehaviour',
                get_string('howquestionsbehave', 'question'), $behaviours);
        $mform->addHelpButton('preferredbehaviour', 'howquestionsbehave', 'question');
        $mform->setDefault('preferredbehaviour', $config->behaviour);

        $options = [
            element::DISBLE_MANDATORY => get_string('no'),
            element::ENABLE_MANDATORY => get_string('yes'),
        ];
        $mform->addElement('select', 'mandatory', get_string('mandatory', 'mod_contentdesigner'), $options);
        $mform->addHelpButton('mandatory', 'mandatory', 'mod_contentdesigner');
        $mform->setDefault('mandatory', $config->mandatory);

        $this->standard_element_settings($mform, $element);

        $buttonstr = '';
        if ($instanceid) {
            $buttonstr = get_string('elementupdate', 'mod_contentdesigner');
        } else {
            $buttonstr = get_string('elementcreate', 'mod_contentdesigner');
        }

        $mform->addElement('hidden', 'chapterid', $this->_customdata['chapterid']);
        $mform->setType('chapterid', PARAM_INT);

        $mform->addElement('hidden', 'sesskey', sesskey());
        $mform->setType('sesskey', PARAM_ALPHANUMEXT);

        $mform->addElement('hidden', 'position', $this->_customdata['position']);
        $mform->setType('position', PARAM_ALPHA);

        $this->add_action_buttons(true, $buttonstr);

        // Form Question selector.
        $PAGE->requires->js_call_amd('cdelement_question/form-question-selector', 'updateCategoryID',
            ['questionid', 'categoryid', get_string('noselection', 'form')]);
        $PAGE->requires->js_call_amd('cdelement_question/form-questionversion-selector', 'updateQuestionID',
            ['questionversion', 'questionid', get_string('noselection', 'form')]);
    }

    /**
     * Definition after data.
     *
     * Update the selected ccategory and questionid, versions related data to the respected elements.
     *
     * @return void
     */
    public function definition_after_data() {

        $mform = $this->_form;

        $categories = $mform->getElement('categoryid')->_optGroups;

        foreach ($categories as &$category) {
            array_walk($category['options'], function(&$option) {
                $list = explode(',', $option['attr']['value']);
                $option['attr']['value'] = $list[0] ?? $option['attr']['value'];
            });
        }
        $mform->getElement('categoryid')->_optGroups = $categories;

        $categoryid = $mform->getElementValue('categoryid');

        $categoryid = is_array($categoryid) ? current($categoryid) : $categoryid;
        if ($categoryid) {
            $variants = \cdelement_question\external::get_question_menu('', $categoryid, 0, 100);
            $options = array_combine(array_column($variants, 'id'), array_column($variants, 'name'));
            $mform->getElement('questionid')->loadArray($options);
        }

        $questionid = $mform->getElementValue('questionid');
        $cmid = $mform->getElementValue('cmid');
        $questionid = is_array($questionid) ? current($questionid) : $questionid;
        if ($questionid) {
            $variants = \cdelement_question\external::get_question_variations($questionid, $cmid);
            $options = array_combine(array_column($variants, 'id'), array_column($variants, 'name'));
            $mform->getElement('questionversion')->loadArray($options);
        }

        parent::definition_after_data();

    }

}
