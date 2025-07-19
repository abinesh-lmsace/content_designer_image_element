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
 * Extended class of elements for Videotime.
 *
 * @package   cdelement_videotime
 * @copyright  2024 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace cdelement_videotime;

use html_writer;
use cm_info;
use mod_videotime\videotime_instance;
use moodle_url;

/**
 * Videotime element instance extended the base elements.
 */
class element extends \mod_contentdesigner\elements {

    /**
     * Shortname of the element.
     */
    const SHORTNAME = 'videotime';

    /**
     * Element name which is visbile for the users
     *
     * @return string
     */
    public function element_name() {
        return get_string('pluginname', 'cdelement_videotime');
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
        return $output->pix_icon('monologo', get_string('pluginname', 'cdelement_videotime'), 'videotime');
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

        $sql = '
            SELECT id, fullname
            FROM {course}
            WHERE id IN (
                SELECT course FROM {videotime}
            )';
        $options = $DB->get_records_sql_menu($sql, []);

        // Filter the course by capability.
        $options = array_filter($options, function($coursename, $courseid) {
            return has_capability('moodle/course:update', \context_course::instance($courseid));
        }, ARRAY_FILTER_USE_BOTH);

        array_walk($options, fn(&$option) => format_string($option));

        // Videotime courses.
        // Include the selected video time related courseid.

        $videocourse = $mform->addElement('autocomplete', 'videotimecourseid', get_string('course', 'core'), $options);
        $mform->addRule('videotimecourseid', null, 'required');
        $mform->setDefault('videotimecourseid', $PAGE->course->id);

        // Cmid.
        $options = [
            'ajax' => 'cdelement_videotime/form-videotime-selector',
        ];

        // Add the selected videotime in the options list.
        if (!empty($formobj->_customdata['instanceid'])) {
            $videotimeelement = $DB->get_record('cdelement_videotime', ['id' => $formobj->_customdata['instanceid']]);
            $videotimename = $DB->get_field('videotime', 'name', ['id' => $videotimeelement->videotimeid]);
            $videotimeoptions = [$videotimeelement->videotimeid => $videotimename];
        }
        $mform->addElement('autocomplete', 'videotimeid', get_string('videotimemodules', 'mod_contentdesigner'),
            $videotimeoptions ?? [], $options);
        $mform->addRule('videotimeid', null, 'required');
        $mform->addHelpButton('videotimeid',  'videotimemodules',  'mod_contentdesigner');

        // Mandatory options.
        $options = [
            self::DISBLE_MANDATORY => get_string('no'),
            self::ENABLE_MANDATORY => get_string('yes'),
        ];
        $mform->addElement('select', 'mandatory', get_string('mandatory', 'mod_contentdesigner'), $options);
        $mform->addHelpButton('mandatory',  'mandatory',  'mod_contentdesigner');

        // Form video time selector.
        $PAGE->requires->js_call_amd('cdelement_videotime/form-videotime-selector', 'updateCourseID',
            ['videotimeid', 'videotimecourseid']);
    }

    /**
     * Prepare data for the element moodle form.
     *
     * @param int $instanceid Element instance id.
     * @return stdclass
     */
    public function prepare_formdata($instanceid) {
        global $DB;

        $instancedata = parent::prepare_formdata($instanceid);

        // Include the selected video time related courseid.
        if (!empty($instancedata->videotimeid)) {
            $course = $DB->get_field('videotime', 'course', ['id' => $instancedata->videotimeid]);
            $videotimename = $DB->get_field('videotime', 'name', ['id' => $instancedata->videotimeid]);

            $instancedata->videotimecourseid = $course;
            $instancedata->videotimeoptions = [$instancedata->videotimeid => $videotimename];
        }

        return ($instancedata);
    }

    /**
     * Render the view of element instance, Which is displayed in the student view.
     *
     * @param stdclass $instance
     * @return string HTML
     */
    public function render($instance) {
        $content = $this->get_videotime_content($instance->videotimeid);
        return html_writer::tag('div', $content, ['class' => "element-videotime"]);
    }

    /**
     * Analyze the Videotime is mantory to view upcoming then check the instance is attempted.
     *
     * If this is manadatory, then includes the verification module to verify the completion of element.
     *
     * @param stdclass $instance Instance data of the element.
     * @return bool True if need to stop the next instance Otherwise false if render of next elements.
     */
    public function prevent_nextelements($instance): bool {
        global $USER, $PAGE;

        $result = false;
        if (isset($instance->mandatory) && $instance->mandatory) {
            list($course, $cm) = get_course_and_cm_from_instance($instance->videotimeid, 'videotime');
            // Get completion state.
            $completion = new \completion_info($course);
            if ($completion->is_enabled($cm)) {
                $current = $completion->get_data($cm, false, $USER->id);
                $result = $current->completionstate == COMPLETION_COMPLETE ? false : true;
            } else {
                $result = false;
            }

            // Module not exist.
            if (empty($cm)) {
                $result = false;
            }
        }

        if ($result) {
            $PAGE->requires->js_call_amd(
                'cdelement_videotime/form-videotime-selector', 'updateNextElements',
                [$instance->videotimeid, $instance->instance, $instance->element]);
        }

        return $result ?: false;
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
     * Get the fallback message for the users to display the access denied message.
     *
     * @param int $courseid
     *
     * @return string
     */
    protected function get_fallback_message(int $courseid) {
        $enrolinstances = enrol_get_instances($courseid, true);
        $self = in_array('self', array_column((array) $enrolinstances, 'enrol'));

        if ($self) {
            $string = get_string('selfenroltoaccess', 'mod_contentdesigner');
            $courseurl = new moodle_url('/enrol/index.php', ['id' => $courseid]);
            $string .= html_writer::link($courseurl, get_string('enrolme', 'enrol_self'), ['class' => 'btn btn-primary ml-4']);
        } else {
            $string = get_string('fallbackmessage', 'mod_contentdesigner');
        }

        return $string;
    }

    /**
     * Get the video time embeded content,
     *
     * Modified from original filter_videotime/filter.
     *
     * @param int $videotimeid Videotime instance id.
     *
     * @return string
     */
    protected function get_videotime_content(int $videotimeid) {
        global $PAGE, $OUTPUT, $USER;

        // Video time renderer.
        $renderer = $PAGE->get_renderer('mod_videotime');

        try {
            if (!$cm = get_coursemodule_from_instance('videotime', $videotimeid)) {
                $content = $OUTPUT->notification(get_string('vimeo_url_missing', 'videotime'));
            } else {
                $context = \context_course::instance($cm->course);

                // Check the guest access is enabled for this instance course.
                $enrolinstances = enrol_get_instances($cm->course, true);
                $guest = in_array('guest', array_column((array) $enrolinstances, 'enrol'));

                $canviewcourse = has_capability('moodle/course:view', $context) ||
                    is_enrolled(\context_course::instance($cm->course), $USER, '', true) || $guest;

                // Check if user can access activity.
                if (!$canviewcourse || !cm_info::create($cm)->uservisible) {
                    $content = $this->get_fallback_message($cm->course);

                } else if (get_config('filter_videotime', 'disableifediting') && $PAGE->user_is_editing()) {

                    $content = $OUTPUT->notification(
                        get_string('videodisabled', 'filter_videotime', $cm->name),
                        \core\output\notification::NOTIFY_SUCCESS
                    );

                } else {
                    $instance = videotime_instance::instance_by_id($cm->instance);
                    $instance->set_embed(true);

                    $defaultrenderer = $renderer;

                    // Allow any subplugin to override video time instance output.
                    foreach (\core_component::get_component_classes_in_namespace(
                        null,
                        'videotime\\instance'
                    ) as $fullclassname => $classpath) {
                        if (is_subclass_of($fullclassname, videotime_instance::class)) {
                            if ($override = $fullclassname::get_instance($instance->id)) {
                                $instance = $override;
                            }
                            if ($override = $fullclassname::get_renderer($instance->id)) {
                                $renderer = $override;
                            }
                        }
                    }

                    $content = $renderer->render($instance);

                    // Set renderer back to default if override was used.
                    $renderer = $defaultrenderer;
                }
            }
        } catch (\Exception $e) {
            $content = $OUTPUT->notification(get_string('parsingerror', 'filter_videotime') . '<br>' . $e->getMessage());
        }

        return $content;
    }

}
