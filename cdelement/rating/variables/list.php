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
 * Element plugin "Rating" - Variables listing file.
 *
 * @package   cdelement_rating
 * @copyright  2025 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Require config.
require_once('./../../../../../config.php');

// Require admin library.
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/tablelib.php');

// Get parameters.
$action = optional_param('action', null, PARAM_ALPHAEXT);
$variableid = optional_param('id', null, PARAM_INT);
$tab = optional_param('t', 'active', PARAM_ALPHA);

// Get system context.
$context = context_system::instance();

// Access checks.
require_login();
require_capability('cdelement/rating:managevariables', $context);

// Create a page URL.
$urlparams = [];
$urlparams = ($tab == 'archive') ? ['t' => 'archive'] : [];
$pageurl = new moodle_url('/mod/contentdesigner/cdelement/rating/variables/list.php', $urlparams);

// Prepare the page (to make sure that all necessary information is already set even if we just handle the actions as a start).
$PAGE->set_context($context);
$PAGE->set_url($pageurl);
$PAGE->set_cacheable(false);

// Further prepare the page.
$PAGE->set_heading(get_string('variablelisthead', 'mod_contentdesigner'));

// Further prepare the page.
$PAGE->set_title(get_string('variableslist', 'mod_contentdesigner'));

if ($action !== null && confirm_sesskey()) {
    $variableid = required_param('id', PARAM_INT);

    // Start the query transaction snapshots.
    $transaction = $DB->start_delegated_transaction();

    // Perform the requested action.
    switch ($action) {
        // Triggered action is delete, then init the deletion of skill and levels.
        case 'delete':
            \cdelement_rating\helper::delete_variables($variableid);
            break;
        case 'archive':
            \cdelement_rating\helper::archive_variables($variableid);
            break;
        case 'active':
            \cdelement_rating\helper::active_variables($variableid);
            break;
    }

    // Allow to update the changes to database.
    $transaction->allow_commit();

    // Redirect to the same page.
    redirect($pageurl);
}

if ($tab == 'archive') {
    $table = new \cdelement_rating\table\archived_variables($context->id);
} else {
    $table = new \cdelement_rating\table\active_variables($context->id);
}

$table->define_baseurl($pageurl);

echo $OUTPUT->header();
echo get_string('variablelistdesc', 'mod_contentdesigner');

// Table Tabs.
$tabs = [];

// Active variables table tab.
$tabs[] = new tabobject('active',
    new moodle_url($PAGE->url, ['t' => 'active']), get_string('activevariables', 'mod_contentdesigner'), '', true);

// Archive variables table tab.
$tabs[] = new tabobject('archive',
    new moodle_url($PAGE->url, ['t' => 'archive']), get_string('archivevariables', 'mod_contentdesigner'), '', true);

// Create variable button.
$createbutton = $OUTPUT->box_start();

// Setup create template button on page.
$caption = get_string('createvariable', 'mod_contentdesigner');
$editurl = new \moodle_url('/mod/contentdesigner/cdelement/rating/variables/edit.php', ['sesskey' => sesskey()]);

// IN Moodle 4.2, primary button param depreceted.
$primary = defined('single_button::BUTTON_PRIMARY') ? single_button::BUTTON_PRIMARY : true;
$singlebutton = new single_button($editurl, $caption, 'get', $primary);
$createbutton .= $OUTPUT->render($singlebutton);

$createbutton .= $OUTPUT->box_end();

echo $createbutton;

echo $OUTPUT->tabtree($tabs, $tab);

$table->initialbars(false);
$table->out(50, true);

echo $OUTPUT->footer();
