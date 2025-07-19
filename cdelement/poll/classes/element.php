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
 * Extended class of elements for Poll.
 *
 * @package   cdelement_poll
 * @copyright  2024 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace cdelement_poll;

use html_writer;
use stdClass;
use moodle_url;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir.'/formslib.php');

/**
 * poll element instance extended the base elements.
 */
class element extends \mod_contentdesigner\elements {

    /**
     * Shortname of the element.
     */
    const SHORTNAME = 'poll';

    /**
     * Result: hidden.
     */
    const RESULTHIDDEN = 0;

    /**
     * Result: Displaye always.
     */
    const RESULTDISPLAYALWAYS = 1;

    /**
     * Result: Displayed after their own rating .
     */
    const RESULTDISPLAYAFTERRATE = 2;

    /**
     * Update Rate: Disabled.
     */
    const DISABLED = 0;

    /**
     * Update Rate: Enabled.
     */
    const ENABLED = 1;

    /**
     * Participation is optional.
     */
    const MANDATORYNO = 0;

    /**
     * The learner must make a choice to proceed.
     */
    const MANDATORYYES = 1;

    /**
     * Element name which is visbile for the users
     *
     * @return string
     */
    public function element_name() {
        return get_string('pluginname', 'cdelement_poll');
    }

    /**
     * Element shortname which is used as identical purpose.
     *
     * @return string
     */
    public function element_shortname() {
        return self::SHORTNAME;
    }

    /**
     * Icon of the element.
     *
     * @param renderer $output
     * @return string HTML fragment
     */
    public function icon($output) {
        return $output->pix_icon('i/scales', get_string('pluginname', 'cdelement_poll'));
    }

    /**
     * Is the element supports the reports.
     * @return bool
     */
    public function supports_reports(): bool {
        return true;
    }

    /**
     * Search area definition.
     *
     * @return array Table and fields to search.
     */
    public function search_area_list(): array {
        return ['cdelement_poll' => 'question'];
    }

    /**
     * Element form element definition.
     *
     * @param moodleform $mform
     * @param genreal_element_form $formobj
     * @return void
     */
    public function element_form(&$mform, $formobj) {
        global $DB;

        // Question Content.
        $mform->addElement('textarea', 'question', get_string('questioncontent', 'mod_contentdesigner'),
            ['rows' => 15, 'cols' => 40]);
        $mform->addHelpButton('question', 'questioncontent', 'mod_contentdesigner');
        $mform->addRule('question', null, 'required', null, 'client');

        // Options.
        $repeatarray = [];
        $repeatarray[] = $mform->createElement('text', 'option', get_string('optionno', 'mod_contentdesigner'));
        $repeatarray[] = $mform->createElement('hidden', 'optionid', 0);

        if ($formobj->_customdata['instanceid']) {
            $repeatno = $DB->count_records($this->tablename().'_options', ['pollid' => $formobj->_customdata['instanceid']]);
        } else {
            $repeatno = 5;
        }

        $repeateloptions = [];

        $repeateloptions['option']['helpbutton'] = ['polloptions', 'mod_contentdesigner'];
        $mform->setType('option', PARAM_CLEANHTML);

        $mform->setType('optionid', PARAM_INT);

        $formobj->repeat_elements($repeatarray, $repeatno,
                    $repeateloptions, 'option_repeats', 'option_add_fields', 3, null, true);

        // Number of selectable options.
        $mform->addElement('text', 'selectoptioncount', get_string('selectoptioncount', 'mod_contentdesigner'), []);
        $mform->setType('selectoptioncount', PARAM_INT);
        $mform->addHelpButton('selectoptioncount', 'selectoptioncount', 'mod_contentdesigner');

        // Results.
        $options = [
            self::RESULTHIDDEN => get_string('hidden', 'mod_contentdesigner'),
            self::RESULTDISPLAYALWAYS  => get_string('displayalways' , 'mod_contentdesigner'),
            self::RESULTDISPLAYAFTERRATE  => get_string('displaysubmitafter', 'mod_contentdesigner'),
        ];
        $mform->addElement('select', 'resulttype', get_string('resulttype', 'mod_contentdesigner'), $options);
        $mform->setDefault('resulttype', self::RESULTHIDDEN);
        $mform->addHelpButton('resulttype', 'resulttype', 'mod_contentdesigner');

        // Update rating.
        $options = [
            self::DISABLED => get_string('disable'),
            self::ENABLED => get_string('enable'),
        ];
        $mform->addElement('select', 'updaterating', get_string('updaterating', 'mod_contentdesigner'), $options);
        $mform->setDefault('updaterating', self::DISABLED);
        $mform->addHelpButton('updaterating', 'updaterating', 'mod_contentdesigner');

        // After submission message.
        $mform->addElement('textarea', 'submissionmessage', get_string('submissionmessage', 'mod_contentdesigner'),
            ['rows' => 15, 'cols' => 30]);
        $mform->addHelpButton('submissionmessage', 'submissionmessage', 'mod_contentdesigner');

        // Mandatory.
        $options = [
            self::MANDATORYNO => get_string('no'),
            self::MANDATORYYES => get_string('yes'),
        ];
        $mform->addElement('select', 'mandatory', get_string('mandatory', 'mod_contentdesigner'), $options);
        $mform->setDefault('mandatory', self::MANDATORYNO);
        $mform->addHelpButton('mandatory', 'mandatory', 'mod_contentdesigner');

    }

    /**
     * Get the options data
     *
     * @param int $instanceid Instance ID
     * @param array $instancedata Instance data.
     * @return array
     */
    public function get_optionsdata($instanceid, &$instancedata) {
        global $DB;

        if (!empty($instanceid) && ($options = $DB->get_records_menu($this->tablename().'_options',
                ['pollid' => $instanceid], 'id', 'id,text'))) {
            $choiceids = array_keys($options);
            $options = array_values($options);
            foreach (array_keys($options) as $key) {
                $instancedata['option['.$key.']'] = $options[$key];
                $instancedata['optionid['.$key.']'] = $choiceids[$key];
            }
        }

        return $instancedata;
    }

    /**
     * Prepare the form data.
     *
     * @param int $instanceid Instance ID
     * @return object $instancedata
     */
    public function prepare_formdata($instanceid) {
        $instancedata = (array) $this->get_instance($instanceid);
        $instancedata += (array) $this->get_optionsdata($instanceid, $instancedata);
        $instancedata['cmid'] = $this->cmid;
        return (object) ($instancedata);
    }

    /**
     * Update the element instance. Override the function in elements element class to add custom rules.
     *
     * @param stdclass $data
     * @return int
     */
    public function update_instance($data) {
        global $DB;

        if ($data->instanceid == false) {
            $data->timemodified = time();
            $data->timecreated = time();
            $data->id = $DB->insert_record($this->tablename, $data);

            foreach ($data->option as $key => $value) {
                $value = trim($value);
                if (isset($value) && $value <> '') {
                    $option = new stdClass();
                    $option->pollid = $data->id;
                    $option->text = $value;
                    $option->timecreated = time();
                    $option->timemodified = time();
                    $DB->insert_record("cdelement_poll_options", $option);
                }
            }
            return $data->id;
        } else {
            $data->timecreated = time();
            $data->id = $data->instanceid;
            $data->submissionmessage = $data->submissionmessage;

            if (empty($data->option)) {
                return;
            }

            // Update, delete or insert answers.
            foreach ($data->option as $key => $value) {
                $value = trim($value);
                $option = new stdClass();
                $option->text = $value;
                $option->pollid = $data->id;
                $option->timemodified = time();
                $option->timecreated = time();
                if (isset($data->optionid[$key]) && !empty($data->optionid[$key])) { // Existing choice record.
                    $option->id = $data->optionid[$key];
                    if (isset($value) && $value <> '') {
                        $DB->update_record("cdelement_poll_options", $option);
                    } else {
                        // Remove the empty (unused) option.
                        $DB->delete_records("cdelement_poll_options", ["id" => $option->id]);
                        // Delete any answers associated with this option.
                        $DB->delete_records("cdelement_poll_options", ["id" => $option->id, "pollid" => $option->pollid]);
                    }
                } else {
                    if (isset($value) && $value <> '') {
                        $DB->insert_record("cdelement_poll_options", $option);
                    }
                }
            }

            if ($DB->update_record($this->tablename, $data)) {
                return $data->id;
            }
        }
    }

    /**
     * Analyze the poll is mantory to view upcoming then check the instance is attempted.
     *
     * @param stdclass $instance Instance data of the element.
     * @return bool True if need to stop the next instance Otherwise false if render of next elements.
     */
    public function prevent_nextelements($instance): bool {
        if (isset($instance->mandatory) && $instance->mandatory) {
            if (empty(self::poll_get_my_response($instance))) {
                return true;
            }
        }
        return false;
    }

    /**
     * Initiate the JS.
     */
    public function initiate_js() {
        global $PAGE;
        $PAGE->requires->js_call_amd('cdelement_poll/poll', 'init', []);
    }

    /**
     * Replace the element on refersh the content.
     * @return bool
     */
    public function supports_replace_onrefresh(): bool {
        return true;
    }

    /**
     * Render the view of element instance, Which is displayed in the student view.
     *
     * @param stdclass $instance
     * @return string
     */
    public function render($instance) {
        global $DB, $USER;

        $html = '';
        $cm = get_coursemodule_from_id('contentdesigner', $this->cmid);
        $cm = \cm_info::create($cm);
        $course = $DB->get_record("course", ["id" => $cm->course]);
        $poll = self::get_poll($instance->id);
        $allresponses = '';
        $resultarea = '';
        $options = [];
        $html .= html_writer::tag('h5', format_string($instance->question), ['class' => "element-poll-question"]);

        $resultclass = '';
        if (!empty($poll->option)) {
            $current = self::poll_get_my_response($poll);
            if ($current) {
                $resultclass = 'show';
            }
            $allresponses = self::get_response_data($poll);
            $options = $this->prepare_options($poll, $USER, $allresponses); // Prepare the options for this poll.
            $html .= html_writer::start_div('poll-section', [
                'data-pollid' => $options['pollid'],
                'data-selectcount' => $options['selectableoptions'],
                'data-instanceid' => $this->cm->instance,
                'data-updaterating' => $poll->updaterating,
            ]);
            $html .= $this->display_options($options, $current); // Display the poll options form.
            if (self::poll_can_view_results($poll, $current)) {
                // Display the poll results.
                $results = self::prepare_poll_show_results($poll, $course, $cm, $allresponses);
                $resultarea = self::display_results($results);
            } else {
                $resultarea = '';
            }
        }

        $html .= html_writer::end_div();

        $html .= html_writer::tag('div', $resultarea, ['class' => 'resultarea' . $resultclass]);

        return $html;
    }

    /**
     * Gets a full poll record
     *
     * @param int $pollid
     * @return object|bool The pool or false
     */
    public static function get_poll($pollid) {
        global $DB;

        if ($poll = $DB->get_record('cdelement_poll', ["id" => $pollid])) {
            if ($options = $DB->get_records("cdelement_poll_options", ["pollid" => $pollid], "id")) {
                foreach ($options as $option) {
                    $poll->option[$option->id] = $option->text;
                }
                return $poll;
            }
        }
        return false;
    }

    /**
     * Returns text string which is the answer that matches the id
     *
     * @param int $id
     * @return string
     */
    public function get_option_text($id) {
        global $DB;

        if ($result = $DB->get_record($this->tablename."_options", ["id" => $id])) {
            return $result->text;
        } else {
            return get_string("notanswered", "mod_contentdesigner");
        }
    }

    /**
     * Prepare options data.
     *
     * @param object $poll
     * @param object $user
     * @param array $allresponses
     * @return array
     */
    public function prepare_options($poll, $user, $allresponses) {
        global $DB;

        $cdisplay = ['options' => []];
        $cdisplay['pollid'] = $poll->id;
        $cdisplay['selectableoptions'] = $poll->selectoptioncount;
        $cdisplay['mandatory'] = $poll->mandatory;
        $cdisplay['updaterating'] = $poll->updaterating; // Add this to check the 'Update Rating' status.

        $selectedoptions = []; // To store the already selected options.

        // Fetch the selected options for the current user.
        $selectedoptions = $DB->get_records('cdelement_poll_answers', ['pollid' => $poll->id, 'userid' => $user->id],
            '', 'optionid');
        $selectedcount = count($selectedoptions);
        $current = self::poll_get_my_response($poll);

        // Iterate through poll options.
        foreach ($poll->option as $optionid => $text) {
            if (isset($text)) { // Make sure there are no dud entries in the DB with blank text values.
                $option = new stdClass;
                $option->attributes = new stdClass;
                $option->attributes->value = $optionid;
                $option->text = format_string($text);

                // Check if the option was previously selected by the user.
                if (array_key_exists($optionid, $selectedoptions)) {
                    $option->attributes->checked = true; // Check the box for already selected options.
                }

                if ($current) {
                    // Disable unselected checkboxes.
                    if ($poll->updaterating && !array_key_exists($optionid, $selectedoptions)) {
                        // Only disable additional checkboxes if the number of selected options equals or exceeds the limit.
                        if ($selectedcount == $poll->selectoptioncount && $poll->selectoptioncount > 1) {
                            $option->attributes->disabled = true;
                        } else {
                            unset($option->attributes->disabled); // Allow selection if not at the limit.
                        }
                    }

                    if ($poll->selectoptioncount == 0) {
                        unset($option->attributes->disabled);
                    }
                }

                $cdisplay['options'][] = $option;
            }
        }

        return $cdisplay;
    }

    /**
     * Returns HTML to display poll of option
     *
     * @param object $options
     * @param rows|null $current my poll responses
     *
     * @return string
     */
    public function display_options($options, $current) {

        $attributes = ['method' => 'POST', 'action' => 'javascript:void(0);'];

        $html = html_writer::start_tag('form', $attributes);
        $html .= html_writer::start_tag('ul', ['class' => 'poll-options list-unstyled unstyled horizontal']);

        $selectableoptions = isset($options['selectableoptions']) ? $options['selectableoptions'] : 0;
        $disabled = (($options['updaterating'] == self::ENABLED || !$current)) ? false : true;
        $pollcount = 0;
        foreach ($options['options'] as $option) {
            $pollcount++;
            $html .= html_writer::start_tag('li', ['class' => 'option mr-3']);

            // Check if multiple options can be selected.
            if ($selectableoptions == 1) {
                // Only 1 option can be selected (use radio buttons).
                $option->attributes->name = 'answer';
                $option->attributes->type = 'radio';
                $option->attributes->disabled = $disabled ? 'disabled' : null;
            } else if ($selectableoptions > 1 || $selectableoptions == 0) {
                // More than 1 option or unlimited options can be selected (use checkboxes).
                $option->attributes->name = 'answer[]';
                $option->attributes->type = 'checkbox';
                if (isset($option->attributes->checked) && !isset($option->attributes->checked)) {
                    $option->attributes->disabled = $disabled ? 'disabled' : null;
                }
            }

            // Ensure unique ID by adding poll ID and option count.
            $pollid = $options['pollid'];
            $option->attributes->id = 'poll_' . $pollid . '_option_' . $pollcount;
            $option->attributes->class = 'mx-1';

            $labeltext = $option->text;

            $html .= html_writer::empty_tag('input', (array)$option->attributes);
            $html .= html_writer::tag('label', $labeltext, ['for' => $option->attributes->id]);
            $html .= html_writer::end_tag('li');
        }

        $html .= html_writer::end_tag('ul');
        $html .= html_writer::empty_tag('input', [
            'type' => 'submit',
            'value' => get_string('submit'),
            'class' => 'submit btn btn-primary',
            'disabled' => $disabled ? 'disabled' : null,
        ]);

        $html .= html_writer::end_tag('form');
        return $html;
    }

    /**
     * Get the user responses data.
     *
     * @param object $poll
     * @return array
     */
    public static function get_response_data($poll) {
        global $DB;

        $allresponses = [];
        // Get all the recorded responses for this poll.
        $rawresponses = $DB->get_records('cdelement_poll_answers', ['pollid' => $poll->id]);
        // Use the responses to move users into the correct column.
        if ($rawresponses) {
            $answeredusers = [];
            foreach ($rawresponses as $response) {
                $allresponses[$response->optionid][$response->userid] = $response->id;
                $answeredusers[] = $response->userid;
            }
        }

        return $allresponses;
    }

    /**
     * Generate the poll result chart.
     *
     * @param stdClass $poll Poll responses object.
     * @return string the rendered chart.
     */
    public static function display_results($poll) {
        global $OUTPUT;

        $count = 0;
        $data = [];
        $numberofuser = 0;
        $totalresponses = 0;
        $percentageamount = 0;
        $html = '';

        // First, calculate the total number of responses.
        foreach ($poll->options as $optionid => $option) {
            if (!empty($option->user)) {
                $totalresponses += count($option->user);
            }
        }

        // Now calculate the percentages for each option based on total responses.
        foreach ($poll->options as $optionid => $option) {
            if (!empty($option->user)) {
                $numberofuser = count($option->user);
            }

            // Calculate percentage based on the total number of responses.
            if ($totalresponses > 0) {
                $percentageamount = ((float)$numberofuser / (float)$totalresponses) * 100.0;
            }

            // Append the percentage to the label text to show it directly.
            $data['labels'][$count] = $option->text . ' (' . format_float($percentageamount, 1) . '%)';
            $data['series'][$count] = $numberofuser;
            $data['series_labels'][$count] = $numberofuser . ' (' . format_float($percentageamount, 1) . '%)';
            $count++;
            $numberofuser = 0;
        }

        // Create the chart and set it to doughnut style.
        $chart = new \core\chart_pie();
        $chart->set_doughnut(true);

        // Add the series to the chart.
        $series = new \core\chart_series(format_string(get_string("responses", "mod_contentdesigner")), $data['series']);
        $series->set_labels($data['series_labels']);
        $chart->add_series($series);
        $chart->set_labels($data['labels']);

        if ($poll->allresponses) {
            return $OUTPUT->notification(get_string("noresponseyet", 'mod_contentdesigner'), 'info', false);
        }

        if (!empty($poll->submissionmessage)) {
            $html .= $OUTPUT->notification($poll->submissionmessage, 'info', false);
        }

        // Render the chart.
        $html .= $OUTPUT->render_chart($chart, false);

        return $html;
    }

    /**
     * Prepare the poll results.
     *
     * @param object $poll
     * @param object $course
     * @param object $cm
     * @param array $allresponses
     * @return object
     */
    public static function prepare_poll_show_results($poll, $course, $cm, $allresponses) {

        $display = clone($poll);
        $display->coursemoduleid = $cm->id;
        $display->courseid = $course->id;

        $display->options = [];
        $allusers = [];
        foreach ($poll->option as $optionid => $optiontext) {
            $display->options[$optionid] = new stdClass;
            $display->options[$optionid]->text = format_string($optiontext, true,
                ['context' => \context_module::instance($cm->id)]);

            if (array_key_exists($optionid, $allresponses)) {
                $display->options[$optionid]->user = $allresponses[$optionid];
                $allusers = array_merge($allusers, array_keys($allresponses[$optionid]));
            }
        }
        unset($display->option);

        $display->numberofuser = count(array_unique($allusers));
        $display->allresponses = empty($allresponses) ? true : false;

        return $display;
    }

    /**
     * Return true if we are allowd to view the poll results.
     *
     * @param stdClass $poll Poll record
     * @param rows|null $current my poll responses
     * @return bool true if we can view the results, false otherwise.
     */
    public static function poll_can_view_results($poll, $current = null) {

        if (empty($current)) {
            $current = self::poll_get_my_response($poll);
        }

        if (($poll->resulttype == self::RESULTDISPLAYALWAYS ) ||
            ($poll->resulttype == self::RESULTDISPLAYAFTERRATE && !empty($current))) {
            return true;
        }
        return false;
    }

    /**
     * Get responses of a given user on a given poll.
     *
     * @param stdClass $poll poll record
     * @param int $userid User id
     * @return array of poll answers records
     */
    public static function poll_get_user_response($poll, $userid) {
        global $DB;
        return $DB->get_records('cdelement_poll_answers', ['pollid' => $poll->id, 'userid' => $userid], 'optionid');
    }

    /**
     * Get my responses on a given poll.
     *
     * @param stdClass $poll Poll record
     * @return array of poll answers records
     */
    public static function poll_get_my_response($poll) {
        global $USER;
        return self::poll_get_user_response($poll, $USER->id);
    }

    /**
     * Returns HTML to display poll responses result.
     *
     * Modified from Moodle mod/chocie/renderer.php
     *
     * @param object $poll
     * @return string
     */
    public function display_publish_responses($poll) {
        global $PAGE, $OUTPUT;
        $html = '';

        $attributes = ['method' => 'POST'];
        $attributes['action'] = new moodle_url($PAGE->url);
        $attributes['id'] = 'attemptsform';

        if ($poll->viewresponsecapability) {
            $html .= html_writer::start_tag('form', $attributes);
            $html .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'id', 'value' => $poll->coursemoduleid]);
            $html .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
            $html .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'mode', 'value' => 'overview']);
        }

        $table = new \html_table();
        $table->cellpadding = 0;
        $table->cellspacing = 0;
        $table->attributes['class'] = 'results names table table-bordered';
        $table->tablealign = 'center';
        $table->data = [];

        $count = 0;
        ksort($poll->options);

        $columns = [];
        $celldefault = new \html_table_cell();
        $celldefault->attributes['class'] = 'data';

        // This extra cell is needed in order to support accessibility for screenreader. MDL-30816.
        $accessiblecell = new \html_table_cell();
        $accessiblecell->scope = 'row';
        $accessiblecell->text = get_string('polloptions', 'mod_contentdesigner');
        $columns['options'][] = $accessiblecell;

        $usernumberheader = clone($celldefault);
        $usernumberheader->header = true;
        $usernumberheader->attributes['class'] = 'header data';
        $usernumberheader->text = get_string('numberofuser', 'mod_contentdesigner');
        $columns['usernumber'][] = $usernumberheader;

        $optionsnames = [];
        foreach ($poll->options as $optionid => $options) {
            $celloption = clone($celldefault);
            $cellusernumber = clone($celldefault);

            if ($optionid == 0) {
                $headertitle = get_string('notanswered', 'mod_contentdesigner');
            } else if ($optionid > 0) {
                $headertitle = format_string($poll->options[$optionid]->text);
            }
            $celltext = $headertitle;

            // Render select/deselect all checkbox for this option.
            if ($poll->viewresponsecapability) {

                // Build the select/deselect all for this option.
                $selectallid = 'select-response-option-' . $optionid;
                $togglegroup = 'responses response-option-' . $optionid;
                $selectalltext = get_string('selectalloption', 'mod_contentdesigner', $headertitle);
                $deselectalltext = get_string('deselectalloption', 'mod_contentdesigner', $headertitle);
                $mastercheckbox = new \core\output\checkbox_toggleall($togglegroup, true, [
                    'id' => $selectallid,
                    'name' => $selectallid,
                    'value' => 1,
                    'selectall' => $selectalltext,
                    'deselectall' => $deselectalltext,
                    'label' => $selectalltext,
                    'labelclasses' => 'accesshide',
                ]);

                $celltext .= html_writer::div($OUTPUT->render($mastercheckbox));
            }
            $numberofuser = 0;
            if (!empty($options->user) && count($options->user) > 0) {
                $numberofuser = count($options->user);
            }

            $celloption->text = html_writer::div($celltext, 'text-center');
            $optionsnames[$optionid] = $celltext;
            $cellusernumber->text = html_writer::div($numberofuser, 'text-center');

            $columns['options'][] = $celloption;
            $columns['usernumber'][] = $cellusernumber;
        }

        $table->head = $columns['options'];
        $table->data[] = new \html_table_row($columns['usernumber']);

        $columns = [];

        // This extra cell is needed in order to support accessibility for screenreader. MDL-30816.
        $accessiblecell = new \html_table_cell();
        $accessiblecell->text = get_string('userchoosethisoption', 'mod_contentdesigner');
        $accessiblecell->header = true;
        $accessiblecell->scope = 'row';
        $accessiblecell->attributes['class'] = 'header data';
        $columns[] = $accessiblecell;

        foreach ($poll->options as $optionid => $options) {
            $cell = new \html_table_cell();
            $cell->attributes['class'] = 'data';

            if ($optionid > 0) {
                if (!empty($options->user)) {
                    $optionusers = '';
                    foreach ($options->user as $user) {
                        $data = '';
                        if (empty($user->imagealt)) {
                            $user->imagealt = '';
                        }

                        $userfullname = fullname($user, $poll->fullnamecapability);
                        $checkbox = '';
                        if ($poll->viewresponsecapability) {
                            $checkboxid = 'attempt-user' . $user->id . '-option' . $optionid;
                            if ($optionid > 0) {
                                $checkboxname = 'attemptid[]';
                                $checkboxvalue = $user->answerid;
                            } else {
                                $checkboxname = 'userid[]';
                                $checkboxvalue = $user->id;
                            }

                            $togglegroup = 'responses response-option-' . $optionid;
                            $slavecheckbox = new \core\output\checkbox_toggleall($togglegroup, false, [
                                'id' => $checkboxid,
                                'name' => $checkboxname,
                                'classes' => 'mr-1',
                                'value' => $checkboxvalue,
                                'label' => $userfullname . ' ' . $options->text,
                                'labelclasses' => 'accesshide',
                            ]);
                            $checkbox = $OUTPUT->render($slavecheckbox);
                        }
                        $userimage = $OUTPUT->user_picture($user, ['courseid' => $poll->courseid, 'link' => false]);
                        $profileurl = new moodle_url('/user/view.php', ['id' => $user->id, 'course' => $poll->courseid]);
                        $profilelink = html_writer::link($profileurl, $userimage . $userfullname);
                        $data .= html_writer::div($checkbox . $profilelink, 'mb-1');

                        $optionusers .= $data;
                    }
                    $cell->text = $optionusers;
                }
            }
            $columns[] = $cell;
            $count++;
        }
        $row = new \html_table_row($columns);
        $table->data[] = $row;

        $html .= html_writer::tag('div', html_writer::table($table), ['class' => 'response']);

        $actiondata = '';
        if ($poll->viewresponsecapability) {
            // Build the select/deselect all for all of options.
            $selectallid = 'select-all-responses';
            $togglegroup = 'responses';
            $selectallcheckbox = new \core\output\checkbox_toggleall($togglegroup, true, [
                'id' => $selectallid,
                'name' => $selectallid,
                'value' => 1,
                'label' => get_string('selectall'),
                'classes' => 'btn-secondary mr-1',
            ], true);
            $actiondata .= $OUTPUT->render($selectallcheckbox);

            $actionurl = new moodle_url($PAGE->url,
                    ['sesskey' => sesskey(), 'action' => 'delete_confirmation()']);
            $actionoptions = ['delete' => get_string('delete')];
            foreach ($poll->options as $optionid => $option) {
                if ($optionid > 0) {
                    $actionoptions['choose_'.$optionid] = get_string('chooseoption', 'mod_contentdesigner', $option->text);
                }
            }
            $selectattributes = [
                'data-action' => 'toggle',
                'data-togglegroup' => 'responses',
                'data-toggle' => 'action',
            ];
            $selectnothing = ['' => get_string('chooseaction', 'mod_contentdesigner')];
            $select = new \single_select($actionurl, 'action', $actionoptions, null, $selectnothing, 'attemptsform');
            $select->set_label(get_string('withselected', 'mod_contentdesigner'));
            $select->disabled = true;
            $select->attributes = $selectattributes;

            $actiondata .= $OUTPUT->render($select);
        }
        $html .= html_writer::tag('div', $actiondata, ['class' => 'responseaction']);

        if ($poll->viewresponsecapability) {
            $html .= html_writer::end_tag('form');
        }

        return $html;
    }

    /**
     * Prepare poll show responses.
     *
     * Modified from Moodle mod/chocie/lib.php
     *
     * @param object $instance
     * @param object $course
     * @param object $cm
     * @param array $allresponses
     * @return object|bool
     */
    public function prepare_poll_show_responses($instance, $course, $cm, $allresponses) {
        global $OUTPUT;

        $poll = self::get_poll($instance->id);
        $display = clone($poll);
        $display->coursemoduleid = $cm->id;
        $display->courseid = $course->id;

        // Remove from the list of non-respondents the users who do not have access to this activity.
        if ($allresponses[0]) {
            $info = new \core_availability\info_module(\cm_info::create($cm));
            $allresponses[0] = $info->filter_user_list($allresponses[0]);
        }

        // Overwrite options value.
        $display->options = [];
        $allusers = [];
        foreach ($poll->option as $optionid => $optiontext) {
            $display->options[$optionid] = new stdClass;
            $display->options[$optionid]->text = format_string($optiontext, true,
                ['context' => \context_module::instance($cm->id)]);

            if (array_key_exists($optionid, $allresponses)) {
                $display->options[$optionid]->user = $allresponses[$optionid];
                $allusers = array_merge($allusers, array_keys($allresponses[$optionid]));
            }
        }
        unset($display->option);

        $display->numberofuser = count(array_unique($allusers));
        $context = \context_module::instance($cm->id);
        $display->viewresponsecapability = has_capability('mod/contentdesigner:viewcontenteditor', $context);
        $display->fullnamecapability = has_capability('moodle/site:viewfullnames', $context);

        if (empty($allresponses)) {
            echo $OUTPUT->heading(get_string("nousersyet"), 3, null);
            return false;
        }

        return $display;
    }

    /**
     * Get the response data for a poll.
     *
     * Modified from Moodle mod/chocie/lib.php.
     *
     * @param object $poll
     * @param object $cm
     * @param int $groupmode
     * @param bool $onlyactive Whether to get response data for active users only.
     * @return array
     */
    public function poll_get_response_data($poll, $cm, $groupmode, $onlyactive) {
        global $DB;

        $context = \context_module::instance($cm->id);

        // Get the current group.
        if ($groupmode > 0) {
            $currentgroup = groups_get_activity_group($cm);
        } else {
            $currentgroup = 0;
        }

        $allresponses = [];

        // First get all the users who have access here.
        // To start with we assume they are all "unanswered" then move them later.
        // TODO Does not support custom user profile fields (MDL-70456).
        $userfieldsapi = \core_user\fields::for_identity($context, false)->with_userpic();
        $userfields = $userfieldsapi->get_sql('u', false, '', '', false)->selects;
        $allresponses[0] = get_enrolled_users($context, '', $currentgroup,
                $userfields, null, 0, 0, $onlyactive);

        // Get all the recorded responses for this poll.
        $rawresponses = $DB->get_records('cdelement_poll_answers', ['pollid' => $poll->id]);

        // Use the responses to move users into the correct column.
        if ($rawresponses) {
            $answeredusers = [];
            foreach ($rawresponses as $response) {
                if (isset($allresponses[0][$response->userid])) {   // This person is enrolled and in correct group.
                    $allresponses[0][$response->userid]->timemodified = $response->timemodified;
                    $allresponses[$response->optionid][$response->userid] = clone($allresponses[0][$response->userid]);
                    $allresponses[$response->optionid][$response->userid]->answerid = $response->id;
                    $answeredusers[] = $response->userid;
                }
            }
            foreach ($answeredusers as $answereduser) {
                unset($allresponses[0][$answereduser]);
            }
        }
        return $allresponses;
    }

    /**
     * Delete responses for a given poll element answers.
     *
     * Modified from Moodle mod/chocie/lib.php.
     *
     * @param array $attemptids
     * @param object $poll Poll main table row
     * @param object $cm Course-module object
     * @param object $course Course object
     * @return bool
     */
    public function poll_delete_responses($attemptids, $poll, $cm, $course) {
        global $DB;

        if (!is_array($attemptids) || empty($attemptids)) {
            return false;
        }

        foreach ($attemptids as $num => $attemptid) {
            if (empty($attemptid)) {
                unset($attemptids[$num]);
            }
        }

        foreach ($attemptids as $attemptid) {
            if ($todelete = $DB->get_record('cdelement_poll_answers', ['pollid' => $poll->id, 'id' => $attemptid])) {
                $DB->delete_records('cdelement_poll_answers', ['pollid' => $poll->id, 'id' => $attemptid]);
            }
        }

        return true;
    }

    /**
     * Modifies responses of other users adding the option $newoptionid to them
     *
     * Modified from Moodle mod/chocie/lib.php.
     *
     * @param array $userids list of users to add option to (must be users without any answers yet)
     * @param array $answerids list of existing attempt ids of users.
     * @param int $newoptionid
     * @param stdClass $instance instance object.
     * @param stdClass $cm
     * @param stdClass $course
     */
    public function poll_modify_responses($userids, $answerids, $newoptionid, $instance, $cm, $course) {
        // Get all existing responses and the list of non-respondents.
        $groupmode = groups_get_activity_groupmode($cm);
        $allresponses = $this->poll_get_response_data($instance, $cm, $groupmode, true);

        $poll = self::get_poll($instance->id);

        // Check that the option value is valid.
        if (!$newoptionid || !isset($poll->option[$newoptionid])) {
            return;
        }

        // First add responses for users who did not make any poll yet.
        foreach ($userids as $userid) {
            if (isset($allresponses[0][$userid])) {
                $this->poll_user_submit_response($newoptionid, $poll, $userid, $course, $cm);
            }
        }

        // Create the list of all options already selected by each user.
        $optionsbyuser = []; // Mapping userid=>array of chosen poll options.
        $usersbyanswer = []; // Mapping answerid=>userid (which answer belongs to each user).
        foreach ($allresponses as $optionid => $responses) {
            if ($optionid > 0) {
                foreach ($responses as $userid => $userresponse) {
                    $optionsbyuser += [$userid => []];
                    $optionsbyuser[$userid][] = $optionid;
                    $usersbyanswer[$userresponse->answerid] = $userid;
                }
            }
        }

        // Go through the list of submitted attemptids and find which users answers need to be updated.
        foreach ($answerids as $answerid) {
            if (isset($usersbyanswer[$answerid])) {
                $userid = $usersbyanswer[$answerid];
                if (!in_array($newoptionid, $optionsbyuser[$userid])) {
                    $options = array_merge($optionsbyuser[$userid], [$newoptionid]);
                    $this->poll_user_submit_response($options, $poll, $userid, $course, $cm);
                }
            }
        }
    }

    /**
     * Process user submitted answers for a poll,
     * and either updating them or saving new answers.
     *
     * Modified from Moodle mod/chocie/lib.php.
     *
     * @param int|array $formanswer the id(s) of the user submitted poll options.
     * @param object $poll the selected poll.
     * @param int $userid user identifier.
     * @param object $course current course.
     * @param object $cm course context.
     * @return void
     */
    public function poll_user_submit_response($formanswer, $poll, $userid, $course, $cm) {
        global $DB, $PAGE;

        if (empty($formanswer)) {
            throw new \moodle_exception('atleastoneoption', 'contentdesigner', $PAGE->url);
        }

        if (is_array($formanswer)) {
            $formanswers = $formanswer;
        } else {
            $formanswers = [$formanswer];
        }

        $options = $DB->get_records('cdelement_poll_options', ['pollid' => $poll->id], '', 'id');
        foreach ($formanswers as $key => $val) {
            if (!isset($options[$val])) {
                throw new \moodle_exception('cannotsubmit', 'contentdesigner', $PAGE->url);
            }
        }

        $current = $DB->get_records('cdelement_poll_answers', ['pollid' => $poll->id, 'userid' => $userid]);

        // Array containing [answerid => optionid] mapping.
        $existinganswers = array_map(function($answer) {
            return $answer->optionid;
        }, $current);

        $countanswers = [];
        foreach ($formanswers as $val) {
            $countanswers[$val] = 0;
        }

        if ($current) {
            // Update an existing answer.
            foreach ($current as $c) {
                if (in_array($c->optionid, $formanswers)) {
                    $DB->set_field('cdelement_poll_answers', 'timemodified', time(), ['id' => $c->id]);
                } else {
                    $DB->delete_records('cdelement_poll_answers', ['id' => $c->id]);
                }
            }

            // Add new ones.
            foreach ($formanswers as $f) {
                if (!in_array($f, $existinganswers)) {
                    $newanswer = new stdClass();
                    $newanswer->optionid = $f;
                    $newanswer->pollid = $poll->id;
                    $newanswer->userid = $userid;
                    $newanswer->timemodified = time();
                    $newanswer->id = $DB->insert_record("cdelement_poll_answers", $newanswer);
                }
            }
        } else {
            // Add new answer.
            foreach ($formanswers as $answer) {
                $newanswer = new stdClass();
                $newanswer->pollid = $poll->id;
                $newanswer->userid = $userid;
                $newanswer->optionid = $answer;
                $newanswer->timemodified = time();
                $newanswer->id = $DB->insert_record("cdelement_poll_answers", $newanswer);
            }

        }
    }

    /**
     * Delete the poll element settings.
     *
     * @param int $instanceid
     * @return bool $status
     */
    public function delete_element($instanceid) {
        global $DB;
        try {
            $transaction = $DB->start_delegated_transaction();

            // Delete the element settings.
            if ($this->get_instance($instanceid)) {
                $DB->delete_records($this->tablename(), ['id' => $instanceid]);
                $DB->delete_records('cdelement_poll_answers', ['pollid' => $instanceid]);
                $DB->delete_records('cdelement_poll_options', ['pollid' => $instanceid]);
            }

            if ($this->get_instance_options($instanceid)) {
                // Delete the element general settings.
                $DB->delete_records('contentdesigner_options', ['element' => $this->element_id(),
                    'instance' => $instanceid]);
            }

            $transaction->allow_commit();
        } catch (\Exception $e) {
            // Extra cleanup steps.
            $transaction->rollback($e); // Rethrows exception.
            throw new \moodle_exception('pollnotdeleted', 'cdelement_poll');
        }
        return true;
    }

    /**
     * Prepare data for the duplicate element.
     *
     * @param stdClass $record
     * @return stdClass
     */
    public function prepare_duplicatedata($record) {
        global $DB;

        if ($options = $DB->get_records("cdelement_poll_options", ["pollid" => $record->id], "id")) {
            foreach ($options as $option) {
                $data[$option->id] = $option->text;
            }
            $record->option = $data;
        }

        return $record;
    }
}
