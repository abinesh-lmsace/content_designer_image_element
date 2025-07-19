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
 * Question element - Common functions.
 *
 * @package   cdelement_question
 * @copyright 2024 bdecent gmbh <https://bdecent.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Returns the question element plugin file.
 *
 * @param stdClass $course The course object.
 * @param stdClass $context The context object.
 * @param string $component The component name.
 * @param string $filearea The file area.
 * @param int $qubaid The question usage by activity ID.
 * @param string $slot The slot name.
 * @param array $args The arguments.
 * @param bool $forcedownload Whether to force download.
 * @param array $fileoptions The file options.
 *
 * @return void
 */
function cdelement_question_question_pluginfile($course, $context, $component, $filearea, $qubaid, $slot,
    $args, $forcedownload, $fileoptions) {
    global $USER;

    list($context, $course, $cm) = get_context_info_array($context->id);
        require_login($course, false, $cm);

    $quba = question_engine::load_questions_usage_by_activity($qubaid);

    $helper = \cdelement_question\helper::instance($context->instanceid);

    if (!$helper->find_usage_by_uniqueid($USER->id, $qubaid) && !has_capability('cdelement/question:grade', $context)) {
        send_file_not_found();
    }

    $options = new \cdelement_question\display_options();
    if (!$quba->check_file_access($slot, $options, $component,
            $filearea, $args, $forcedownload)) {
        send_file_not_found();
    }

    $fs = get_file_storage();
    $relativepath = implode('/', $args);
    $fullpath = "/{$context->id}/{$component}/{$filearea}/{$relativepath}";
    if (!($file = $fs->get_file_by_hash(sha1($fullpath))) || $file->is_directory()) {
        send_file_not_found();
    }

    send_stored_file($file, 0, 0, $forcedownload, $fileoptions);
}
