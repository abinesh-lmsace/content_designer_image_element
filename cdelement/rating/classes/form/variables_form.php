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
 * Element Rating - Variables form.
 *
 * @package    cdelement_rating
 * @copyright  2025 bdecent GmbH <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace cdelement_rating\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

/**
 * Form to assign the variables to the courses.
 */
class variables_form extends \moodleform {

    /**
     * Defined the fields for the variable.
     *
     * @return void
     */
    public function definition() {
        global $DB;

        $mform = $this->_form;

        // Current variable id to edit.
        $mform->addElement('hidden', 'id', 0);
        $mform->setType('id', PARAM_INT);

        // General section.
        $mform->addElement('header', 'general', get_string('general', 'core'));

        // Variable fullname.
        $mform->addElement('text', 'fullname', get_string('fullname', 'mod_contentdesigner'));
        $mform->setType('fullname', PARAM_TEXT);
        $mform->addHelpButton('fullname', 'fullname', 'mod_contentdesigner');

        // Variable shortname.
        $mform->addElement('text', 'shortname', get_string('shortname', 'mod_contentdesigner'));
        $mform->setType('shortname', PARAM_TEXT);
        $mform->addRule('shortname', null, 'required');
        $mform->addHelpButton('shortname', 'shortname', 'mod_contentdesigner');

        // Variable description.
        $mform->addElement('textarea', 'description', get_string('description'), ['rows' => 15, 'cols' => 30]);
        $mform->addHelpButton('description', 'description', 'mod_contentdesigner');

        // Variable type.
        $types = [ 0 => get_string('notspecified', 'mod_contentdesigner')];

        $courseroles = get_roles_for_contextlevels(CONTEXT_COURSE);
        list($insql, $inparams) = $DB->get_in_or_equal(array_values($courseroles));
        $roles = $DB->get_records_sql("SELECT * FROM {role} WHERE id $insql", $inparams);
        $rolesoptions = role_fix_names($roles, null, ROLENAME_ALIAS, true);
        $types = array_merge($types, $rolesoptions);

        for ($i = 1; $i <= 10; $i++) {
            $types[] = get_string('customcategory'. $i, 'mod_contentdesigner');
        }

        $mform->addElement('select', 'type', get_string('type', 'mod_contentdesigner'), $types);
        $mform->addRule('type', null, 'required');
        $mform->addHelpButton('type', 'type', 'mod_contentdesigner');

        // Add the Available in Course Categories element.
        $categories = \core_course_category::make_categories_list();
        $cat = $mform->addElement('autocomplete', 'categories', get_string('coursecategories', 'mod_contentdesigner'),
                $categories);
        $cat->setMultiple(true);
        $mform->addHelpButton('categories', 'coursecategories', 'mod_contentdesigner');

        $status = [
            1 => get_string('active'),
            2 => get_string('archived', 'mod_contentdesigner'),
        ];
        $mform->addElement('select', 'status', get_string('status', 'mod_contentdesigner'), $status);
        $mform->addRule('status', null, 'required');
        $mform->addHelpButton('status', 'status', 'mod_contentdesigner');

        // Action buttons.
        $this->add_action_buttons();
    }

    /**
     * Validation.
     *
     * @param array $data
     * @param array $files
     * @return array the errors that were found
     */
    public function validation($data, $files) {
        global $DB;

        $errors = parent::validation($data, $files);

        // Add field validation check for duplicate shortname.
        if ($DB->record_exists('cdelement_rating_variables', ['shortname' => $data['shortname']], '*', IGNORE_MULTIPLE)) {
            $errors['shortname'] = get_string('shortnametaken', 'mod_contentdesigner');
        }
    }
}
