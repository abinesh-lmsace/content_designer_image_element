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
 * Element videotime services defined.
 *
 * @package    cdelement_videotime
 * @copyright  2024 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$functions = [

    'cdelement_videotime_get_instance_menu' => [
        'classname'     => 'cdelement_videotime\external',
        'methodname'    => 'get_instance_menu',
        'description'   => 'Get the videotime instance for the selected course.',
        'type'          => 'write',
        'capabilities'  => 'mod/contentdesigner:addinstance',
        'ajax'          => true,
    ],

    'cdelement_videotime_verify_completion' => [
        'classname'     => 'cdelement_videotime\external',
        'methodname'    => 'verify_completion',
        'description'   => 'Verify the completion of video time instance.',
        'type'          => 'write',
        'capabilities'  => 'mod/contentdesigner:view',
        'ajax'          => true,
    ],
];
