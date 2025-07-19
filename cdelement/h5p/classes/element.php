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
 * Extended class of elements for chapter. it contains major part of editor element content
 *
 * @package    cdelement_h5p
 * @copyright  2022 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace cdelement_h5p;

use mod_contentdesigner\editor;
use core_h5p\player;
use core_h5p\factory;

/**
 * Element h5p definition.
 */
class element extends \mod_contentdesigner\elements {

    /**
     * Shortname of the element.
     */
    const SHORTNAME = 'h5p';

    /**
     * Element name which is visbile for the users
     *
     * @return string
     */
    public function element_name() {
        return get_string('pluginname', 'cdelement_h5p');
    }

    /**
     * Verify the element is supports the grade.
     *
     * @return bool
     */
    public function supports_grade() {
        return true;
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
        global $CFG;
        return (file_exists($CFG->dirroot.'/mod/h5pactivity/pix/monologo.png'))
            ? $output->pix_icon('monologo', '', 'mod_h5pactivity', ['class' => 'icon pluginicon'])
            : $output->pix_icon('icon', '', 'mod_h5pactivity', ['class' => 'icon pluginicon']);
    }

    /**
     * List of areafiles which is used the mod_contentdesigner as component.
     *
     * @return array
     */
    public function areafiles() {
        return ['package'];
    }

    /**
     * Save the area files data after the element instance moodle_form submittted.
     *
     * @param stdclas $data Submitted moodle_form data.
     */
    public function save_areafiles($data) {
        parent::save_areafiles($data);
        file_save_draft_area_files($data->package, $data->contextid, 'cdelement_h5p', 'package', $data->instance);
    }

    /**
     * Prepare the form editor elements file data before render the elemnent form.
     *
     * @param stdclass $formdata
     * @return stdclass
     */
    public function prepare_standard_file_editor(&$formdata) {
        $formdata = parent::prepare_standard_file_editor($formdata);

        if (isset($formdata->instance)) {
            $draftitemid = file_get_submitted_draft_itemid('package');
            file_prepare_draft_area($draftitemid, $this->context->id, 'cdelement_h5p', 'package', $formdata->instance,
                ['subdirs' => 0, 'maxfiles' => 1]);
            $formdata->package = $draftitemid;
        }
        return $formdata;
    }

    /**
     * Analyze the H5P is mantory to view upcoming then check the instance is attempted.
     *
     * @param stdclass $instance Instance data of the element.
     * @return bool True if need to stop the next instance Otherwise false if render of next elements.
     */
    public function prevent_nextelements($instance): bool {
        global $USER, $DB;
        if (isset($instance->mandatory) && $instance->mandatory) {
            return !$DB->record_exists('cdelement_h5p_completion', [
                'instance' => $instance->id, 'userid' => $USER->id, 'cdattemptid' => $this->cdattempt->id, 'completion' => true,
            ]);
        }
        return false;
    }

    /**
     * Element form element definition.
     *
     * @param moodle_form $mform
     * @param genreal_element_form $formobj
     * @return void
     */
    public function element_form(&$mform, $formobj) {
        $options = [
            'accepted_types' => ['.h5p'],
            'maxbytes' => 0,
            'maxfiles' => 1,
            'subdirs' => 0,
        ];

        $mform->addElement('filemanager', 'package', get_string('package', 'mod_h5pactivity'), null, $options);
        $mform->addHelpButton('package', 'package', 'mod_h5pactivity');
        $mform->addRule('package', null, 'required');

        $options = [
            0 => get_string('no'),
            1 => get_string('yes'),
        ];
        $default = get_config('cdelement_h5p', 'mandatory');
        $mform->addElement('select', 'mandatory', get_string('mandatory', 'mod_contentdesigner'), $options);
        $mform->addHelpButton('mandatory', 'mandatory', 'mod_contentdesigner');
        $mform->setDefault('mandatory', $default ?: 0);
    }

    /**
     * Render the view of element instance, Which is displayed in the student view.
     *
     * @param stdclass $data
     * @return string
     */
    public function render($data) {
        global $PAGE;

        if (!isset($data->id)) {
            return '';
        }

        $enablesavestate = get_config('cdelement_h5p', 'enablesavestate') ? true : false;
        $savestatefreq = get_config('cdelement_h5p', 'savestatefreq');

        set_config('enablesavestate', $enablesavestate, 'cdelement_h5p');
        set_config('savestatefreq', $savestatefreq, 'cdelement_h5p');

        $file = editor::get_editor($data->cmid)->get_element_areafiles('package', $data->id, 'cdelement_h5p');
        $PAGE->requires->js_call_amd('cdelement_h5p/h5p', 'init', ['instance' => $data->instance,
            'cdattemptid' => $this->cdattempt->id]);

        $completiontable = $this->generate_completion_table($data);
        // Convert display options to a valid object.
        $factory = new factory();
        $core = $factory->get_core();
        $config = \core_h5p\helper::decode_display_options($core, false);
        $config->export = false;
        $config->embed = false;
        $config->copyright = false;

        return \html_writer::div(
            player::display($file, $config, true, 'cdelement_h5p', false),
            'h5p-element-instance', ['data-instanceid' => $data->instance]
        );
    }

    /**
     * Generate the result table to display the user atempts to user. It display the highest grade of the user attempt.
     *
     * @param stdclass $data Instance data of the element.
     * @return string
     */
    public function generate_completion_table($data) {
        global $USER, $DB, $OUTPUT;
        $instance = isset($data->instance) ? $data->instance : '';
        $params = ['instance' => $instance, 'cdattemptid' => $this->cdattempt->id];
        if (!has_capability('cdelement/h5p:viewstudentrecords', $this->get_context())) {
            $params['userid'] = $USER->id;
        }
        $results = $DB->get_records('cdelement_h5p_completion', $params);
        if (!empty($results)) {
            $strings = (array) get_strings(['score', 'maxscore', 'completion'], 'mod_h5pactivity');
            $table = new \html_table();
            $table->head = array_merge(['#', get_string('date')], $strings, [get_string('success')]);
            foreach ($results as $record) {
                $table->data[] = [
                    $record->id,
                    userdate($record->timecreated, get_string('strftimedatefullshort', 'core_langconfig')),
                    $record->score,
                    json_decode($record->scoredata)->max,
                    ($record->completion ? $OUTPUT->pix_icon('e/tick', 'core') : $OUTPUT->pix_icon('t/dockclose', 'core')),
                    ($record->success ? $OUTPUT->pix_icon('e/tick', 'core') : $OUTPUT->pix_icon('t/dockclose', 'core') ),
                ];
            }
            return \html_writer::tag('h3', get_string('highestgrade', 'cdelement_h5p')).\html_writer::table($table);
        }

    }

    /**
     * Retrieves an H5P instance record from the database.
     *
     * @param int $instanceid The ID of the H5P instance to retrieve.
     * @return stdClass The database record of the H5P instance.
     */
    public static function get_h5p_instance($instanceid) {
        global $DB;
        return $DB->get_record('cdelement_h5p', ['id' => $instanceid], '*', MUST_EXIST);
    }

    /**
     * Delete the element settings.
     *
     * @param int $instanceid
     * @return bool $status
     */
    public function delete_element($instanceid) {
        global $DB;
        parent::delete_element($instanceid);
        try {
            $transaction = $DB->start_delegated_transaction();

            // Delete the element settings.
            if ($this->get_instance($instanceid)) {
                $DB->delete_records($this->tablename(), ['id' => $instanceid]);
                $DB->delete_records('cdelement_h5p_completion', ['instance' => $instanceid,
                    'cdattemptid' => $this->cdattempt->id]);
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
            throw new \moodle_exception('h5pnotdeleted', 'cdelement_h5p');
        }
        return true;
    }

    /**
     * Summary of pre_process_grade.
     *
     * @return bool
     */
    public function pre_process_grade() {
        return false;
    }

    /**
     * Calculate the grade for this H5P element.
     *
     * @param stdClass $instance The instance of the H5P element.
     * @return int
     */
    public function get_mark($instance) {
        global $DB;
        $h5pcompletion = $DB->get_record('cdelement_h5p_completion',
        ['cdattemptid' => $this->cdattempt->id, 'instance' => $instance->id]);
        if ($h5pcompletion) {
            return $h5pcompletion->score;
        }
        return 0;
    }

    /**
     * Get the maximum mark for this H5P element.
     *
     * @param stdClass $instance The instance of the H5P element.
     * @param int $userid The ID of the user.
     * @return int The maximum mark.
     */
    public function get_max_mark($instance, $userid = null) {
        global $DB;
        $h5pcompletion = $DB->get_record('cdelement_h5p_completion',
        ['cdattemptid' => $this->cdattempt->id, 'instance' => $instance->id]);
        $h5pinstance = $DB->get_record('cdelement_h5p', ['id' => $instance->id]);
        if ($h5pcompletion) {
            return $h5pcompletion->score;
        }
        return $h5pinstance->maxscore;
    }

    /**
     * Get the maximum grade possible for this H5P element.
     *
     * @param stdClass $instance The instance of the H5P element.
     * @return float The maximum grade.
     */
    public function get_max_grade($instance) {
        return $instance->maxgrade * ($instance->weight / 100);
    }

    /**
     * Update the grade for this H5P element when an H5P activity is completed.
     *
     * @param stdClass $instance The instance of the H5P element.
     * @param int $userid The ID of the user.
     * @param float $grade The new grade to set.
     * @return bool True if the update was successful, false otherwise.
     */
    public function update_grade($instance, $userid, $grade) {
        global $DB;

        $record = $DB->get_record('cdelement_h5p_results', ['instanceid' => $instance->id, 'userid' => $userid]);

        if ($record) {
            $record->grade = $grade;
            $record->timemodified = time();
            $result = $DB->update_record('cdelement_h5p_results', $record);
        } else {
            $record = new \stdClass();
            $record->instanceid = $instance->id;
            $record->userid = $userid;
            $record->grade = $grade;
            $record->timecreated = $record->timemodified = time();
            $result = $DB->insert_record('cdelement_h5p_results', $record);
        }

        if ($result) {
            // Trigger grade updated event.
            $cm = get_coursemodule_from_instance('contentdesigner', $instance->contentdesignerid);
            $context = \context_module::instance($cm->id);
            $event = \mod_contentdesigner\event\h5p_grade_updated::create([
                'objectid' => $instance->id,
                'context' => $context,
                'userid' => $userid,
                'other' => [
                    'grade' => $grade,
                ],
            ]);
            $event->trigger();

            // Update the overall contentdesigner grade.
            $contentdesigner = $DB->get_record('contentdesigner', ['id' => $instance->contentdesignerid]);
            contentdesigner_update_grade($contentdesigner, $userid);

            return true;
        }

        return false;
    }

    /**
     * Define the each h5p instance as different column for the grade report.
     *
     * @param array $columns
     * @param array $headers
     * @param stdClass $table
     * @return void
     */
    public function define_report_columns(array &$columns, array &$headers, $table) {
        global $DB;

        $records = $DB->get_records('cdelement_h5p',
            ['contentdesignerid' => $this->cm->instance, 'visible' => self::STATUS_VISIBLE]);

        $i = 1;
        foreach ($records as $id => $record) {
            $columns[] = 'cdelement_h5p_' . $id;
            $headers[] = get_string('h5pnumber', 'mod_contentdesigner', $i);
            $table->no_sorting('cdelement_h5p_' . $id);
            $i++;
        }
    }

    /**
     * Get the grade for this question element.
     * This function is used to display the grade in the contentdesigner report.
     * The grade is calculated by the calculate_grade function.
     *
     * @param string $colname The name of the column.
     * @param stdClass $data The data of the column.
     * @return string
     */
    public function col_report_field($colname, $data) {
        global $DB;
        if (str_starts_with($colname, 'cdelement_h5p_')) {
            $h5pelementid = str_replace('cdelement_h5p_', '', $colname);
            $h5pcompletion = $DB->get_record('cdelement_h5p_completion', ['cdattemptid' => $data->attemptid, 'userid' => $data->id,
                'instance' => $h5pelementid]);
            $record = $DB->get_record('cdelement_h5p', ['id' => $h5pelementid]);

            $maxscore = 0;
            $score = 0;
            $h5pgrade = '';

            if ($data->attemptid !== null) {
                $h5pgrade = 0;
            }

            if ($record && $h5pcompletion) {
                $h5pgrade = $this->calculate_grade($h5pcompletion->score, $record->maxscore, $data->id);
                $score += $h5pcompletion->score;
                $maxscore += $record->maxscore;
            }

            $stateclass = $this->h5p_graded_state_class($score, $maxscore);
            if (is_numeric($h5pgrade)) {
                $gradehtml = \html_writer::tag('h6', $score . ' / '. $h5pgrade . '%', ['class' => 'h5p-gradestate ' . $stateclass]);
                $output = \html_writer::tag('div', $gradehtml, ['class' => 'h5p-grade']);
            }
            return $output ?? '-';
        }
    }

    /**
     * Get the grade class for this H5P element.
     *
     * @param int $h5pscore The score of the H5P element.
     * @param int $maxgrade The maximum grade of the H5P element.
     *
     * @return string The class of the grade.
     */
    public static function h5p_graded_state_class($h5pscore, $maxgrade) {
        if ($h5pscore <= 0) {
            return 'text-danger';
        } else if ($h5pscore >= $maxgrade) {
            return 'text-success';
        } else {
            return 'text-warning';
        }
    }
}
