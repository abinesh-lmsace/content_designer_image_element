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
 * Participants table filterset.
 *
 * @package   cdaddon_report
 * @copyright 2024, bdecent gmbh bdecent.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

declare(strict_types=1);

namespace cdaddon_report\table;

use core_table\local\filter\integer_filter;

/**
 * Activity report table filterset.
 *
 * @package   cdaddon_report
 * @copyright 2024, bdecent gmbh bdecent.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/
class activity_report_filterset extends  \core_user\table\participants_filterset {
    // For dynamic tables, tables should have its own filterset.
    /**
     * Get the required filters.
     *
     * The only required filter is the courseid filter.
     *
     * @return array.
     */
    public function get_required_filters(): array {
        return [
            'courseid' => integer_filter::class,
        ];
    }

    /**
     * Get the optional filters.
     *
     * These are:
     * - attemptmethod;
     * - attemptnumber;
     *
     * @return array
     */
    public function get_optional_filters(): array {
        return [
            'attemptmethod' => integer_filter::class,
            'attemptnumber' => integer_filter::class,
        ];
    }
}
