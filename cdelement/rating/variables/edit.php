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
 * Element plugin "Rating" - variable edit file.
 *
 * @package   cdelement_rating
 * @copyright  2025 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Require config.
require_once('./../../../../../config.php');

// Require admin library.
require_once($CFG->libdir.'/adminlib.php');

// Get parameters.
$id = optional_param('id', null, PARAM_INT);

// Get system context.
$context = \context_system::instance();

// Access checks.
require_login();
require_sesskey();

require_capability('cdelement/rating:managevariables', $context);

$listurl = new moodle_url('/mod/contentdesigner/cdelement/rating/variables/list.php');

// Prepare the page.
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/mod/contentdesigner/cdelement/rating/variables/edit.php', ['id' => $id, 'sesskey' => sesskey()]));
$PAGE->set_cacheable(false);

$PAGE->set_title(get_string('variables', 'mod_contentdesigner'));

if ($id !== null && $id > 0) {

    $PAGE->set_heading(get_string('editvariable', 'mod_contentdesigner'));
    $PAGE->navbar->add(get_string('edit'));

} else {
    $PAGE->set_heading(get_string('createvariable', 'mod_contentdesigner'));
    $PAGE->navbar->add(get_string('create'));
}

$form = new \cdelement_rating\form\variables_form(null, ['id' => $id]);

if ($data = $form->get_data()) {

    $variableid = \cdelement_rating\helper::manage_instance($data);

    // Redirect to variable list.
    redirect($listurl);

    // Otherwise if the form was cancelled.
} else if ($form->is_cancelled()) {
    // Redirect to variable list.
    redirect($listurl);
}

// If a variable ID is given.
if ($id !== null && $id > 0) {
    // Fetch the data for the variable.
    if ($record = \cdelement_rating\helper::get_data($id)) {

        // Set the variable data to the variable edit form.
        $form->set_data($record);

        // If the variable is not available.
    } else {
        // Add a notification to the page.
        \core\notification::error(get_string('error:variablenotfound', 'mod_contentdesigner'));

        // Redirect to variable list (where the notification is shown).
        redirect($listurl);
    }
}

// Start page output.
echo $OUTPUT->header();

// Show form.
echo $form->display();

// Finish page output.
echo $OUTPUT->footer();
