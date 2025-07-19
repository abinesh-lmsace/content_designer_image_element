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
 * poll element libarary methods defined.
 *
 * @package   cdelement_poll
 * @copyright  2024 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/contentdesigner/cdelement/poll/classes/element.php');
 /**
  * Update the poll result area.
  *
  * @param array $args list of parameters such as cmid and poll id.
  * @return string Html of poll chart.
  */
function cdelement_poll_output_fragment_update_pollchart($args) {
    global $DB, $OUTPUT;

    if (isset($args['pollid'])) {

        $pollid = $args['pollid'];
        $cmid = $args['cmid'];

        $cm = get_coursemodule_from_id('contentdesigner', $cmid);
        $cm = \cm_info::create($cm);
        $course = $DB->get_record("course", ["id" => $cm->course]);

        $poll = \cdelement_poll\element::get_poll($pollid);
        $current = \cdelement_poll\element::poll_get_my_response($poll);
        $allresponses = \cdelement_poll\element::get_response_data($poll);

        $html = '';
        if (!empty($poll->submissionmessage)) {
            $html .= $OUTPUT->notification($poll->submissionmessage, 'info', false);
        }

        if (\cdelement_poll\element::poll_can_view_results($poll, $current)) {
            $results = \cdelement_poll\element::prepare_poll_show_results($poll, $course, $cm, $allresponses);
            $html .= (\cdelement_poll\element::display_results($results));
        }

        return $html;
    }
}
