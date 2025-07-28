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
 * Extended class of elements for Image.
 *
 * @package    cdelement_image
 * @copyright  2025 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace cdelement_image;

use mod_contentdesigner\editor;

/**
 * Image element instance extend the contentdesigner/elements base.
 */
class element extends \mod_contentdesigner\elements {

    /**
     * Shortname of the element.
     */
    const SHORTNAME = 'image';

    /**
     * Lightbox options - Disabled.
     * @var int
     */
    const LIGHTBOX_DISABLED = 0;

    /**
     * Lightbox options - Enabled.
     * @var int
     */
    const LIGHTBOX_ENABLED = 1;

    /**
     * Hard-coded value for the 'maxfiles' option.
     */
    const EDITOR_UNLIMITED_FILES = -1;

    /**
     * Element name which is visbile for the users
     *
     * @return string
     */
    public function element_name() {
        return get_string('pluginname', 'cdelement_image');
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
        return $output->pix_icon('i/messagecontentimage', get_string('pluginname', 'cdelement_image'));
    }

    /**
     * List of areafiles which is used the mod_contentdesigner as component.
     *
     * @return array
     */
    public function areafiles() {
        return ['contentimages'];
    }

    /**
     * Search area definition.
     *
     * @return array Table and fields to search.
     */
    public function search_area_list(): array {
        return ['cdelement_image' => 'caption'];
    }

    /**
     * Element form element definition.
     *
     * @param moodle_form $mform
     * @param genreal_element_form $formobj
     * @return void
     */
    public function element_form(&$mform, $formobj) {

        $editoroptions = $this->editor_options($formobj->_customdata['context']);
        $mform->addElement('filemanager', 'contentimages',
            get_string('contentimages', 'mod_contentdesigner'), null, $editoroptions);
        $mform->addRule('contentimages', null, 'required');
        $mform->addHelpButton('contentimages', 'contentimages', 'mod_contentdesigner');

        $mform->addElement('text', 'caption', get_string('caption', 'mod_contentdesigner'), ['size' => '64']);
        $mform->setType('caption', PARAM_TEXT);
        $mform->addHelpButton('caption', 'caption', 'mod_contentdesigner');

        $mform->addElement('select', 'lightbox', get_string('lightbox', 'mod_contentdesigner'), [
            self::LIGHTBOX_DISABLED => get_string('no'),
            self::LIGHTBOX_ENABLED => get_string('yes'),
        ]);
        $mform->addHelpButton('lightbox', 'lightbox', 'mod_contentdesigner');

    }

    /**
     * Render the view of element instance, Which is displayed in the student view.
     *
     * @param stdclass $data
     * @return string HTML
     */
    public function render($data) {
        global $PAGE;

        // Include the lightbox modal listeners.
        $PAGE->requires->js_call_amd('cdelement_image/element', 'init', ['instanceid' => $data->id]);

        if (!isset($data->id)) {
            return '';
        }

        $context = $this->get_context();
        $files = editor::get_editor($data->cmid)
            ->get_element_areafiles('cdelement_image_contentimages', $data->id, 'mod_contentdesigner', $context, true);

        $href = 'element-image-' . $data->id;
        $html = \html_writer::start_div('element-image carousel slide',
            ['data-ride' => 'carousel', 'id' => $href, 'data-interval' => 'false']);

        // Carousel indicators.
        if (count($files) > 1) {
            $html .= \html_writer::start_tag('ol', ['class' => 'carousel-indicators']);
            $active = true;
            foreach ($files as $index => $fileurl) {
                $activeclass = $active ? 'active' : '';
                $html .= \html_writer::tag('li', '', [
                    'class' => "carousel-indicator $activeclass",
                    'data-slide-to' => $index,
                    'data-target' => "#$href",
                    'aria-label' => get_string('slide', 'mod_contentdesigner', $index + 1),
                ]);
                $active = false; // Only the first item should be active.
            }
            $html .= \html_writer::end_tag('ol');
        }

        // Carousel inner with images.
        $html .= \html_writer::start_div('element-image-content carousel-inner');
        $active = true;
        foreach ($files as $fileurl) {

            $img = \html_writer::img($fileurl, $data->caption ?? '', [
                'class' => 'img-fluid element-image-img responsive-img d-block w-100',
                'data-lightbox' => $data->lightbox ? 'image-gallery' : '',
            ]);

            $activeclass = $active ? 'active' : '';
            $html .= \html_writer::tag('div', $img, [
                'class' => "image-container carousel-item $activeclass",
                'data-modal' => $data->lightbox ? 'lightbox' : '',
                'data-modal-title' => $data->caption ?? '',
                'data-modal-content' => $img,
            ]);

            $active = false; // Only the first item should be active.
        }
        $html .= \html_writer::end_div();

        // Carousel navigation controls.
        // Only show if there are multiple images.
        if (count($files) > 1) {
            $prev = \html_writer::tag('span', '', ['class' => 'carousel-control-prev-icon', 'aria-hidden' => 'true']);
            $next = \html_writer::tag('span', '', ['class' => 'carousel-control-next-icon', 'aria-hidden' => 'true']);

            $html .= \html_writer::link("#$href", $prev,
                ['class' => 'carousel-control-prev', 'role' => 'button', 'data-slide' => 'prev']);
            $html .= \html_writer::link("#$href", $next,
                ['class' => 'carousel-control-next', 'role' => 'button', 'data-slide' => 'next']);
        }

        $html .= \html_writer::end_div();

        if (!empty($data->caption)) {
            $html .= \html_writer::tag('div', format_text($data->caption, FORMAT_HTML), ['class' => 'element-image-caption']);
        }

        return \html_writer::div($html, 'contentdesigner-element element-image');
    }

    /**
     * Save the area files data after the element instance moodle_form submittted.
     *
     * @param stdclas $data Submitted moodle_form data.
     */
    public function save_areafiles($data) {
        parent::save_areafiles($data);

        file_save_draft_area_files(
            $data->contentimages, $data->contextid, 'mod_contentdesigner', 'cdelement_image_contentimages', $data->instance
        );
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
            $draftitemid = file_get_submitted_draft_itemid('contentimages');
            file_prepare_draft_area($draftitemid, $this->context->id, 'mod_contentdesigner', 'cdelement_image_contentimages',
                $formdata->instance, ['subdirs' => 0, 'maxfiles' => 1]);
            $formdata->contentimages = $draftitemid;
        }
        return $formdata;
    }
}
