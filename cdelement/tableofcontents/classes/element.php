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
 * Extended class of elements for Table of contents.
 *
 * @package   cdelement_tableofcontents
 * @copyright  2024 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace cdelement_tableofcontents;

use stdClass;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir.'/formslib.php');

/**
 * Table of contents element instance extended the base elements.
 */
class element extends \mod_contentdesigner\elements {

    /**
     * Shortname of the element.
     */
    const SHORTNAME = 'tableofcontents';

    /**
     * Disabled.
     */
    const DISABLED = 0;

    /**
     * Enabled.
     */
    const ENABLED = 1;

    /**
     * Scroll up.
     */
    const STICKYSCROLLUP = 2;

    /**
     * Visibility.
     */
    const VISIBLE = 1;

    /**
     * HIDDEN.
     */
    const HIDDEN = 0;

    /**
     * Hidden on mobile.
     */
    const HIDDENONMOBILE = 2;
    /**
     * Element name which is visbile for the users
     *
     * @return string
     */
    public function element_name() {
        return get_string('pluginname', 'cdelement_tableofcontents');
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
        $icon = ($CFG->branch >= 405) ? "i/section" : "i/log";
        return $output->pix_icon($icon, get_string('pluginname', 'cdelement_tableofcontents'));
    }

    /**
     * List of areafiles which is used the mod_contentdesigner as component.
     *
     * @return array
     */
    public function areafiles() {
        return ['intro'];
    }

    /**
     * Search area definition.
     *
     * @return array Table and fields to search.
     */
    public function search_area_list(): array {
        return ['cdelement_tableofcontents' => 'intro, introformat'];
    }

    /**
     * Initiate the JS.
     */
    public function initiate_js() {
        global $PAGE;
        $PAGE->requires->js_call_amd('cdelement_tableofcontents/tableofcontents', 'init', []);
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
     * Is the element supports the multiple instance for one activity instance.
     *
     * @return bool
     */
    public function supports_multiple_instance() {
        return false;
    }

    /**
     * Verify the element is display top of the contents. ie(cdelement_tableofcontents)
     *
     * @return bool
     */
    public function supports_top_instance() {
        return true;
    }

    /**
     * Element form element definition.
     *
     * @param moodleform $mform
     * @param genreal_element_form $formobj
     * @return void
     */
    public function element_form(&$mform, $formobj) {
        $introdefault = get_config('cdelement_tableofcontents', 'intro');
        $introhtml = file_rewrite_pluginfile_urls($introdefault, 'pluginfile.php',
            $formobj->_customdata['context']->id,
            'cdelement_tableofcontents',
            'cdelement_tableofcontents_intro', 0);
        $introhtml = format_text($introhtml, FORMAT_HTML, ['trusted' => true, 'noclean' => true]);

        // Intro text editor.
        $editoroptions = $this->editor_options($formobj->_customdata['context']);
        $intro = $mform->addElement('editor', 'intro_editor', get_string('introtext', 'mod_contentdesigner'), null, $editoroptions);
        $mform->setType('intro_editor', PARAM_RAW);
        $mform->addHelpButton('intro_editor', 'introtext', 'mod_contentdesigner');
        $intro->setValue(['text' => $introhtml ?? '', 'format' => 1]);

        // Call to action.
        $options = [
            self::DISABLED => get_string('disable'),
            self::ENABLED => get_string('enable'),
        ];
        $mform->addElement('select', 'actiontstatus', get_string('actiontstatus', 'mod_contentdesigner'), $options);
        $default = get_config('cdelement_tableofcontents', 'actiontstatus');
        $mform->setDefault('actiontstatus', $default ?: 0);
        $mform->addHelpButton('actiontstatus', 'actiontstatus', 'mod_contentdesigner');

        // Sticky table of contents.
        $options = [
            self::DISABLED => get_string('disable'),
            self::ENABLED => get_string('enable'),
            self::STICKYSCROLLUP => get_string('scrollup', 'mod_contentdesigner'),
        ];
        $mform->addElement('select', 'stickytype', get_string('stickytype', 'mod_contentdesigner'), $options);
        $default = get_config('cdelement_tableofcontents', 'stickytype');
        $mform->setDefault('stickytype', $default ?: 0);
        $mform->addHelpButton('stickytype', 'stickytype', 'mod_contentdesigner');

        // Chapter title in sticky state.
        $visibleoptions = [
            self::VISIBLE => get_string('visible'),
            self::HIDDEN => get_string('hidden', 'mod_contentdesigner'),
            self::HIDDENONMOBILE => get_string('hiddenonmobile', 'mod_contentdesigner'),
        ];
        $mform->addElement('select', 'chaptervisible', get_string('sticky:chaptervisible', 'mod_contentdesigner'),
            $visibleoptions);
        $default = get_config('cdelement_tableofcontents', 'chaptervisible');
        $mform->setDefault('chaptervisible', $default ?: 1);
        $mform->addHelpButton('chaptervisible', 'sticky:chaptervisible', 'mod_contentdesigner');

        // Activity title on sticky state.
        $modvisibleoptions = [
            self::VISIBLE => get_string('visible'),
            self::HIDDEN => get_string('hidden', 'mod_contentdesigner'),
            self::HIDDENONMOBILE => get_string('hiddenonmobile', 'mod_contentdesigner'),
        ];
        $mform->addElement('select', 'modtitlevisible', get_string('sticky:modtitlevisible', 'mod_contentdesigner'),
            $modvisibleoptions);
        $default = get_config('cdelement_tableofcontents', 'modtitlevisible');
        $mform->setDefault('chaptervisible', $default ?: 1);
        $mform->addHelpButton('modtitlevisible', 'sticky:modtitlevisible', 'mod_contentdesigner');

    }

    /**
     * Render the view of element instance, Which is displayed in the student view.
     *
     * @param stdclass $data
     * @return string
     */
    public function render($data) {
        global $OUTPUT;
        $templatecontext = $this->get_tableofcontents($data);
        return $OUTPUT->render_from_template('cdelement_tableofcontents/tableofcontents', $templatecontext);
    }

    /**
     * Create basic table of content instance for the first time.
     *
     * @return int
     */
    public function create_basic_tableofcontent_instance() {
        global $DB;

        $introdefault = get_config('cdelement_tableofcontents', 'intro');
        $introhtml = file_rewrite_pluginfile_urls(
            $introdefault,
            'pluginfile.php',
            \context_module::instance($this->cmid)->id,
            'cdelement_tableofcontents',
            'cdelement_tableofcontents_intro',
            0,
        );
        $introhtml = format_text($introhtml, FORMAT_HTML, ['trusted' => true, 'noclean' => true]);

        $record = [
            'contentdesignerid' => $this->cm->instance,
            'introformat' => FORMAT_HTML,
            'intro' => $introhtml ?: '',
            'actiontstatus' => get_config('cdelement_tableofcontents', 'actiontstatus') ?: 0,
            'stickytype' => get_config('cdelement_tableofcontents', 'stickytype') ?: 0,
            'chaptervisible' => get_config('cdelement_tableofcontents', 'chaptervisible') ?: 1,
            'modtitlevisible' => get_config('cdelement_tableofcontents', 'modtitlevisible') ?: 1,
            'timecreated' => time(),
            'timemodified' => time(),
        ];

        if ($this->is_table_exists()) {
            $lastelement = (int) $DB->get_field_sql('SELECT max(position) from {cdelement_chapter}
                WHERE contentdesignerid = ?', [$this->cm->instance]
            );
            $record['position'] = $lastelement ? $lastelement + 1 : 1;

            $result = $DB->insert_record($this->tablename, $record);
            if ($result) {
                $data = [];
                $fields = $this->get_options_fields();
                foreach ($fields as $field) {
                    $globalvalues = get_config('mod_contentdesigner', $field);
                    $data[$field] = $globalvalues ?? '';
                }

                $data['element'] = $this->elementid;
                $data['instance'] = $result;
                $data['timecreated'] = time();

                if (!$DB->record_exists('contentdesigner_options', ['instance' => $result,
                    'element' => $this->elementid])) {
                    $DB->insert_record('contentdesigner_options', $data);
                }

            }
            return $result;
        } else {
            throw new \moodle_exception('tablenotfound', 'contentdesigner');
        }
    }

    /**
     * Get the table of contents editor data.
     *
     * @return string
     */
    public function get_editor_data() {
        global $DB, $OUTPUT;
        $element = \mod_contentdesigner\editor::get_element($this->element_shortname(), $this->cm->id);
        $instance = $DB->get_field('cdelement_tableofcontents', 'id',
            ['contentdesignerid' => $this->cm->instance]);

        if (!$instance) {
            $instance = $this->create_basic_tableofcontent_instance();
        }

        $instancedata = $element->get_instance($instance);
        $editurl = new \moodle_url('/mod/contentdesigner/element.php', [
            'cmid' => $this->cm->id,
            'element' => $element->shortname,
            'id' => $instancedata->id,
            'sesskey' => sesskey(),
        ]);
        return $OUTPUT->render_from_template('mod_contentdesigner/elementbox', [
            'info' => $element->info(),
            'instancedata' => $instancedata,
            'editurl' => $editurl,
            'hidemove' => true,
            'hidedelete' => true,
            'hideduplicate' => true,
        ]);
    }

    /**
     * Render the module tableofcontents element display top on the contents.
     *
     * @return array Rendered element box view of the tableofcontents.
     */
    public function render_module_topcontents() {
        global $DB;
        $element = \mod_contentdesigner\editor::get_element($this->element_shortname(), $this->cm->id);
        if ($instance = $DB->get_record('cdelement_tableofcontents', ['contentdesignerid' => $this->cm->instance])) {
            $instance = $element->get_instance($instance->id, $instance->visible);
            $editor = \mod_contentdesigner\editor::get_editor($this->cmid);
            $option = $editor->get_option($instance->id, $element->elementid);
            $this->load_option_classes($instance, $option);
            // Verify this element supports replace on refresh.
            $instance->replaceonrefresh = $element->supports_replace_onrefresh();
            $instancedata = $element->prepare_formdata($instance->id);
            $data = [
                'id' => $instance->id,
                'contents' => $element->render($instancedata),
                'instancedata' => $instance,
                'element' => $element->elementid,
                'info' => $element->info(),
            ];
            return $data;
        }
        return [];
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
        $formdata->introformat = $formdata->intro_editor['format'];
        $formdata->intro = $formdata->intro_editor['text'];
        if ($formdata->instanceid == false) {
            // Insert table of content data.
            $formdata->timemodified = time();
            $formdata->timecreated = time();
            return $DB->insert_record($this->tablename, $formdata);

        } else {
            // Update table of content data.
            $formdata->timecreated = time();
            $formdata->id = $formdata->instanceid;
            if ($DB->update_record($this->tablename, $formdata)) {
                return $formdata->id;
            }

        }
    }

    /**
     * Prepare the form editor elements file data before render the elemnent form.
     *
     * @param stdclass $data
     * @return stdclass
     */
    public function prepare_standard_file_editor(&$data) {
        $data = parent::prepare_standard_file_editor($data);
        $context = \context_module::instance($this->cmid);
        $editoroptions = $this->editor_options($context);

        if (!isset($data->id)) {
            $data->id = null;
            $data->introformat = FORMAT_HTML;
            $data->intro = '';
        }
        file_prepare_standard_editor(
            $data,
            'intro',
            $editoroptions,
            $context,
            'mod_contentdesigner',
            'cdelement_tableofcontents_intro',
            $data->id,
        );
        return $data;
    }

    /**
     * Save the area files data after the element instance moodle_form submittted.
     *
     * @param stdclas $data Submitted moodle_form data.
     */
    public function save_areafiles($data) {
        global $DB;
        parent::save_areafiles($data);
        if (isset($data->contextid)) {
            $context = \context::instance_by_id($data->contextid, MUST_EXIST);
        }
        $editoroptions = $this->editor_options($context);
        if (isset($data->instance)) {
            $itemid = $data->intro_editor['itemid'];
            $data->introformat = $data->intro_editor['format'];
            $data = file_postupdate_standard_editor(
                $data,
                'intro',
                $editoroptions,
                $context,
                'mod_contentdesigner',
                'cdelement_tableofcontents_intro',
                $data->instance
            );
            $updatedata = (object) ['id' => $data->instance, 'introformat' => $data->introformat, 'intro' => $data->intro];
            $DB->update_record('cdelement_tableofcontents', $updatedata);
        }
    }

    /**
     * Options used in the editor defined.
     *
     * @param context_module $context
     * @return array Filemanager options.
     */
    public function editor_options($context) {
        global $CFG;
        return [
            'subdirs' => 1,
            'maxbytes' => $CFG->maxbytes,
            'accepted_types' => '*',
            'context' => $context,
            'maxfiles' => EDITOR_UNLIMITED_FILES,
        ];
    }

    /**
     * Get the completd chapters count data.
     *
     * @return int
     */
    public function get_completed_chapter_count() {
        global $DB, $USER;
        $sql = "SELECT ec.*, ecc.completion FROM {cdelement_chapter} ec
            LEFT JOIN {cdelement_chapter_completion} ecc ON ec.id = ecc.instance AND ecc.userid=:userid
            WHERE ec.contentdesignerid=:contentdesignerid AND ec.visible = 1 AND ecc.completion = 1 AND ec.id IN (
                SELECT chapter FROM {contentdesigner_content} cc
            )";
        $records = $DB->get_records_sql($sql, ['userid' => $USER->id, 'contentdesignerid' => $this->cm->instance]);
        return count($records);
    }

    /**
     * Get the incompleted chpaters data.
     *
     * @return int
     */
    public function incompleted_chapters_data() {
        global $DB, $USER;

        // Fetch all chapters.
        $chapters = $DB->get_records_sql('
        SELECT ec.*
        FROM {cdelement_chapter} ec
        WHERE ec.contentdesignerid = ? AND ec.visible = 1
        ORDER BY ec.position', [$this->cm->instance]);

        // Fetch all completed chapters for the user.
        $completedchapters = $DB->get_records('cdelement_chapter_completion', ['userid' => $USER->id], '', 'instance');

        // Create an array of completed chapter IDs.
        $completedids = array_column($completedchapters, 'instance');

        // Filter to get not completed chapters.
        $notcompletedchapters = array_filter($chapters, function($chapter) use ($completedids) {
            return !in_array($chapter->id, $completedids);
        });

        if (!empty($notcompletedchapters)) {
            $currentchapter = reset($notcompletedchapters); // Get the first record.
            $chapterid = $currentchapter->id;   // Access the 'id' of the first record.
        }

        return $chapterid;
    }

    /**
     * Return the table of contents data.
     *
     * @param stdclass $data instance data.
     *
     * @return array $templatecontext
     */
    public function get_tableofcontents($data) {
        global $DB, $USER;

        $context = $this->get_context();
        $intro = file_rewrite_pluginfile_urls(
            $data->intro, 'pluginfile.php', $context->id, 'mod_contentdesigner',
            'cdelement_tableofcontents_intro', $data->instance);
        $intro = format_text($intro, $data->introformat, ['context' => $context->id]);

        $condition = ['contentdesignerid' => $this->cm->instance, 'visible' => 1];
        $chapters = $DB->get_records('cdelement_chapter', $condition, 'position ASC');

        $list = [];
        $templatecontext = [];
        $chaptertitleclasss = '';
        $modtitlerestrictclass = '';
        $stickyclass = (!empty($data->stickytype) && $data->stickytype > 1) ? 'scrollup-stickycontent' :
            'stickycontent'; // Sticky class.
        if ($data->stickytype != 0) {
            if ($data->chaptervisible > 1) {
                $chaptertitleclasss = 'hideonmobile';
            } else if ($data->chaptervisible == 0) {
                $chaptertitleclasss = 'hideonsticky';
            }

            if ($data->modtitlevisible > 1) {
                $modtitlerestrictclass = 'hideonmobile';
            } else if ($data->modtitlevisible == 0) {
                $modtitlerestrictclass = 'hideonsticky';
            }
        }

        $completedcount = $this->get_completed_chapter_count();
        $chapterscount = count($chapters);

        $buttontext = '';
        $condition = ['contentdesignerid' => $this->cm->instance, 'visible' => 1, 'position' => 1];
        $firstchapter = $DB->get_record('cdelement_chapter', $condition);
        $buttonid = !empty($firstchapter) ? $firstchapter->id : 0;
        if ($data->actiontstatus == self::ENABLED) {
            if ($completedcount == 0) {
                $buttontext = get_string('startnow', 'mod_contentdesigner');
            } else if ($completedcount > 0 && $completedcount < $chapterscount) {
                $buttontext = get_string('resume', 'mod_contentdesigner');
                $buttonid = $this->incompleted_chapters_data();
            } else if ($completedcount == $chapterscount) {
                $buttontext = get_string('review', 'mod_contentdesigner');
            }
        }

        foreach ($chapters as $chapter) {
            $content = $DB->record_exists('contentdesigner_content', ['chapter' => $chapter->id]);
            $completion = $DB->get_record('cdelement_chapter_completion', ['instance' => $chapter->id, 'userid' => $USER->id]);
            $list[] = [
                'id' => $chapter->id,
                'title' => $chapter->title,
                'completed' => isset($completion->completion) && $completion->completion ? true : false,
                'chapterstate' => $content ?? false,
            ];
        }

        $templatecontext = [
            'chapterblock' => !empty($chapters) ? true : false,
            'chapters' => $list,
            'modname' => $this->cm->name,
            'stickyclass' => !empty($data->stickytype) ? $stickyclass : '',
            'titlehideclass' => $chaptertitleclasss,
            'modtitlehideclass' => $modtitlerestrictclass,
            'buttontext' => $buttontext,
            'buttonid' => $buttonid,
            'intro' => $intro,
            'actionstatus' => ($data->actiontstatus == self::ENABLED) ? true : false,
        ];

        return $templatecontext;
    }

}
