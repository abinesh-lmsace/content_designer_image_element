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
 * Element h5p services defined.
 *
 * @package   cdelement_h5p
 * @copyright  2022 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$functions = [

    'cdelement_h5p_store_result' => [
        'classname'     => 'cdelement_h5p\external',
        'methodname'    => 'store_result_data',
        'description'   => 'Store the user attempt result of H5P',
        'type'          => 'write',
        'capabilities'  => 'mod/contentdesigner:view',
        'ajax'          => true,
    ],
    'cdelement_h5p_store_maxscore' => [
        'classname'     => 'cdelement_h5p\external',
        'methodname'    => 'store_max_score',
        'description'   => 'Store the max score of H5P',
        'type'          => 'write',
        'capabilities'  => 'mod/contentdesigner:view',
        'ajax'          => true,
    ],
];
