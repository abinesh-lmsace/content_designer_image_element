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
 * Extended class of elements for rating.
 *
 * @package   cdelement_rating
 * @copyright  2025 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace cdelement_rating;

/**
 * Rating element instance extend the contentdesigner/elements base.
 */
class element extends \mod_contentdesigner\elements {

    /**
     * Shortname of the element.
     */
    const SHORTNAME = 'rating';

    /**
     * Result type - Disabled.
     */
    const RESULTDISABLED = 0;

    /**
     * Result type - Average.
     */
    const RESULTAVERAGE = 1;

    /**
     * Result type - Count.
     */
    const RESULTCOUNT = 2;

    /**
     * Scale type - Numeric.
     */
    const SCALENUMERIC = 0;

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
        return get_string('pluginname', 'cdelement_rating');
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
        return \html_writer::tag('i', '', ['class' => 'fa fa-solid fa-ranking-star icon pluginicon']);
    }

    /**
     * Search area definition.
     *
     * @return array Table and fields to search.
     */
    public function search_area_list(): array {
        return ['cdelement_rating' => 'content'];
    }

    /**
     * Element form element definition.
     *
     * @param moodle_form $mform
     * @param genreal_element_form $formobj
     * @return void
     */
    public function element_form(&$mform, $formobj) {
        global $DB, $PAGE;

        $response = $DB->record_exists('cdelement_rating_responses', ['ratingid' => $formobj->_customdata['instanceid']]);

        // Scale.
        $scales = [self::SCALENUMERIC => get_string('numeric', 'mod_contentdesigner')] + $this->get_scales();
        $mform->addElement('select', 'scale', get_string('scale'), $scales);
        $mform->addHelpButton('scale', 'scale', 'mod_contentdesigner');
        $mform->addRule('scale', null, 'required');

        // Scale numeric.
        $numbers = array_combine(range(1, 10), range(1, 10));
        $mform->addElement('select', 'numericcount', get_string('numericcount', 'mod_contentdesigner'), $numbers);
        $mform->addHelpButton('numericcount', 'numericcount', 'mod_contentdesigner');
        $mform->hideIf('numericcount', 'scale', 'neq', 0);

        // Rating content.
        $mform->addElement('textarea', 'content', get_string('content'), ['rows' => 15, 'cols' => 30]);

        // Change rating.
        $mform->addElement('advcheckbox', 'changerating', get_string('changerating', 'mod_contentdesigner'));
        $mform->setDefault('changerating', 0);
        $mform->addHelpButton('changerating', 'changerating', 'mod_contentdesigner');

        // Label.
        $mform->addElement('text', 'label', get_string('label', 'mod_contentdesigner'));
        $mform->addHelpButton('label', 'label', 'mod_contentdesigner');
        $mform->setType('label', PARAM_ALPHANUM);
        $mform->setDefault('label', '');

        // List of variables.
        $varibales = [0 => ''] + \cdelement_rating\helper::get_current_coursecat_variables($this->course->id);
        $varibale = $mform->addElement('autocomplete', 'variables', get_string('variables', 'mod_contentdesigner'), $varibales);
        $varibale->setMultiple(true);
        $mform->addHelpButton('variables', 'variables', 'mod_contentdesigner');

        // Results.
        $options = [
            self::RESULTDISABLED => get_string('disabled', 'mod_contentdesigner'),
            self::RESULTAVERAGE  => get_string('average', 'mod_contentdesigner'),
            self::RESULTCOUNT  => get_string('count', 'mod_contentdesigner'),
        ];
        $mform->addElement('select', 'resulttype', get_string('ratingresulttype', 'mod_contentdesigner'), $options);
        $mform->setDefault('resulttype', self::RESULTDISABLED);
        $mform->addRule('resulttype', null, 'required');
        $mform->addHelpButton('resulttype', 'ratingresulttype', 'mod_contentdesigner');

        // Disable scale selection if responses exist.
        if ($response) {
            // Prevent changes after responses exist.
            $mform->freeze('scale');
            $mform->freeze('numericcount');
        }

        // Mandatory.
        $options = [
            self::MANDATORYNO => get_string('no'),
            self::MANDATORYYES => get_string('yes'),
        ];
        $mform->addElement('select', 'mandatory', get_string('mandatory', 'mod_contentdesigner'), $options);
        $mform->setDefault('mandatory', self::MANDATORYNO);
        $mform->addHelpButton('mandatory', 'mandatory', 'mod_contentdesigner');

        // Initiate the js for the element form.
        $PAGE->requires->js_call_amd('cdelement_rating/rating', 'disbleResulttypefield', []);

    }

    /**
     * Get all scales.
     *
     * @return bool
     */
    public function get_scales() {
        global $DB;
        $scales = $DB->get_records_menu('scale', null, '', 'id, name');
        return $scales;
    }

    /**
     * Render the view of element instance, Which is displayed in the student view.
     *
     * @param stdclass $data
     * @return string
     */
    public function render($data) {
        global $OUTPUT;
        $templatecontext = $this->get_ratingcontents($data);
        return $OUTPUT->render_from_template('cdelement_rating/rating', $templatecontext);
    }

    /**
     * Initiate the element js for the view page.
     *
     * @return void
     */
    public function initiate_js() {
        global $PAGE;
        $PAGE->requires->js_call_amd('cdelement_rating/rating', 'init', []);
    }

    /**
     * Replace the element on refersh the content.
     *
     * @return bool
     */
    public function supports_replace_onrefresh(): bool {
        return true;
    }

    /**
     * Process the update of element instance and genreal options.
     *
     * @param stdclass $data Submitted element moodle form data
     * @return int|bool
     */
    public function update_instance($data) {
        global $DB;
        $formdata = clone $data;
        $formdata->variables = (!empty($formdata->variables)) ? json_encode($formdata->variables) : '';

        if ($formdata->instanceid == false) {
            // Insert rating data.
            $formdata->timemodified = time();
            $formdata->timecreated = time();
            return $DB->insert_record($this->tablename, $formdata);
        } else {
            // Update rating data.
            $formdata->timecreated = time();
            $formdata->id = $formdata->instanceid;
            if ($DB->update_record($this->tablename, $formdata)) {
                return $formdata->id;
            }

        }
    }

    /**
     * Prepare data for the element moodle form.
     *
     * @param int $instanceid Element instance id.
     * @return stdclass
     */
    public function prepare_formdata($instanceid) {

        $instancedata = parent::prepare_formdata($instanceid);

        // Include the selected video time related courseid.
        if (!empty($instancedata->variables)) {
            $instancedata->variables = $instancedata->variables ? json_decode($instancedata->variables) : [];
        }

        return ($instancedata);
    }

    /**
     * Get the rating contents.
     *
     * @param stdclass $data
     * @return array
     */
    public function get_ratingcontents($data) {
        global $DB, $USER;

        $scalelist = [];
        $templatecontext = [];
        $responsescount = null;
        $average = null;

        $result = get_string('beforeratingstr', 'mod_contentdesigner');
        $resonsestatus = $DB->record_exists('cdelement_rating_responses', ['ratingid' => $data->id, 'userid' => $USER->id]);
        $response = $DB->get_record('cdelement_rating_responses', ['ratingid' => $data->id, 'userid' => $USER->id], "*",
            IGNORE_MULTIPLE);

        if ($data->scale != self::SCALENUMERIC) {

            if ($data->scale && $DB->record_exists('scale', ['id' => $data->scale])) {
                $scale = $DB->get_record('scale', ['id' => $data->scale]);
                $scales = explode(",", $scale->scale);

                if ($resonsestatus && ($data->resulttype == self::RESULTCOUNT)) {
                    $result = \cdelement_rating\helper::get_count_response($data, $response->response);
                    $responsescount = \cdelement_rating\helper::get_most_selected_response_count($data->id);
                }

                $i = 0;
                foreach ($scales as $scaleoption) {
                    $i++;
                    $scalelist[] = [
                        'value' => $i,
                        'label' => $scaleoption,
                        'responseclass' => ($response && $response->response == $i) ? 'selected' : '',
                        'averageclass' => ($responsescount && $responsescount == $i) ? 'average' : '',
                    ];
                }
            }

        } else {

            if ($resonsestatus && ($data->resulttype == self::RESULTAVERAGE)) {
                $result = \cdelement_rating\helper::get_average_response($data->id);
                $average = \cdelement_rating\helper::get_numeric_average_value($data->id);
            }

            $decimel = 0.1;
            $scalenum = $data->numericcount;
            for ($i = 1; $i <= $scalenum; $i += $decimel) {
                $roundedvalue = round($i, 1);
                $wholenumber = (int)$roundedvalue;
                $scalelist[] = [
                    'value' => $roundedvalue,
                    'label' => ($roundedvalue == $wholenumber) ? $wholenumber : '',
                    'averageclass' => ($average && round($average, 1) == round($i, 1)) ? 'average' : '',
                    'numericclass' => ($roundedvalue == $wholenumber) ? 'average-numeric' : '',
                    'responseclass' => ($response && $response->response == $roundedvalue) ? 'selected' : '',
                ];
            }
        }

        $templatecontext += [
            'rateid' => $data->id,
            'scales' => $scalelist,
            'total' => count($scalelist),
            'changerating' => $data->changerating,
            'contentdesignerid' => $data->contentdesignerid,
            'response' => ($response && !empty($response->response)) ? 1 : 0,
            'content' => (!empty($data->content)) ? $data->content : '',
            'result' => ($data->resulttype != self::RESULTDISABLED) ? $result :
                (!$response ? get_string('beforeratingstr', 'mod_contentdesigner') : ''),
            'ratingtypeclass' => ($data->scale == self::SCALENUMERIC) ? 'numeric-block' : '',
        ];

        return $templatecontext;
    }

    /**
     * Prepare data for the duplicate element.
     *
     * @param stdClass $record
     * @return stdClass
     */
    public function prepare_duplicatedata($record) {
        global $DB;
        $ratedata = $DB->get_record('cdelement_rating', ['id' => $record->id], '*', IGNORE_MULTIPLE);

        if (!empty($ratedata->variables)) {
            $record->variables = $ratedata->variables ? json_decode($ratedata->variables) : [];
        }

        return $record;
    }

    /**
     * Analyze the rating is mantory to view upcoming then check the instance is attempted.
     *
     * @param stdclass $instance Instance data of the element.
     * @return bool True if need to stop the next instance Otherwise false if render of next elements.
     */
    public function prevent_nextelements($instance): bool {
        global $DB, $USER;
        if (isset($instance->mandatory) && $instance->mandatory) {
            return !$DB->record_exists('cdelement_rating_responses', ['ratingid' => $instance->id, 'userid' => $USER->id]);
        }
        return false;
    }

}
