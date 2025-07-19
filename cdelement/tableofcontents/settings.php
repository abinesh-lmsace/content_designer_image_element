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
 * Table of contents element settings.
 *
 * @package   cdelement_tableofcontents
 * @copyright 2024, bdecent gmbh bdecent.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;
use cdelement_tableofcontents\element as tableofcontents;

// Intro.
$name = 'cdelement_tableofcontents/intro';
$title = get_string('introtext', 'mod_contentdesigner');
$description = get_string('introtext_help', 'mod_contentdesigner');
$setting = new admin_setting_confightmleditor($name, $title, $description, '');
$page->add($setting);

// Call to action (CTA).
$actionoptions = [
    tableofcontents::DISABLED => get_string('disable'),
    tableofcontents::ENABLED => get_string('enable'),
];
$name = 'cdelement_tableofcontents/actiontstatus';
$title = get_string('actiontstatus', 'mod_contentdesigner');
$description = get_string('actiontstatus_help', 'mod_contentdesigner');
$setting = new admin_setting_configselect($name, $title, $description, tableofcontents::DISABLED, $actionoptions);
$page->add($setting);

// Sticky table of contents.
$stickyoptions = [
    tableofcontents::DISABLED => get_string('disable'),
    tableofcontents::ENABLED => get_string('enable'),
    tableofcontents::STICKYSCROLLUP => get_string('scrollup', 'mod_contentdesigner'),
];
$name = 'cdelement_tableofcontents/stickytype';
$title = get_string('stickytype', 'mod_contentdesigner');
$description = get_string('stickytype_help', 'mod_contentdesigner');
$setting = new admin_setting_configselect($name, $title, $description, tableofcontents::DISABLED, $stickyoptions);
$page->add($setting);

// Chapter title in sticky state.
$visibleoptions = [
    tableofcontents::VISIBLE => get_string('visible'),
    tableofcontents::HIDDEN => get_string('hidden', 'mod_contentdesigner'),
    tableofcontents::HIDDENONMOBILE => get_string('hiddenonmobile', 'mod_contentdesigner'),
];
$name = 'cdelement_tableofcontents/chaptervisible';
$title = get_string('sticky:chaptervisible', 'mod_contentdesigner');
$description = get_string('sticky:chaptervisible_help', 'mod_contentdesigner');
$setting = new admin_setting_configselect($name, $title, $description, tableofcontents::VISIBLE, $visibleoptions);
$page->add($setting);

// Activity title on sticky state.
$modvisibleoptions = [
    tableofcontents::VISIBLE => get_string('visible'),
    tableofcontents::HIDDEN => get_string('hidden', 'mod_contentdesigner'),
    tableofcontents::HIDDENONMOBILE => get_string('hiddenonmobile', 'mod_contentdesigner'),
];
$name = 'cdelement_tableofcontents/modtitlevisible';
$title = get_string('sticky:modtitlevisible', 'mod_contentdesigner');
$description = get_string('sticky:modtitlevisible_help', 'mod_contentdesigner');
$setting = new admin_setting_configselect($name, $title, $description, tableofcontents::VISIBLE, $visibleoptions);
$page->add($setting);
