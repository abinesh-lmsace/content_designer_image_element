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
 * Cdelement rating cohort filter for report builder.
 *
 * @package   cdelement_rating
 * @copyright  2025 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace cdelement_rating\reportbuilder\filters;
/**
 * Constructs an SQL filter for cohort-based filtering.
 *
 * This method generates an SQL condition and its associated parameters
 * to filter records based on specified cohort IDs. If no cohort IDs
 * are provided, it returns an empty condition.
 *
 * @param array $values An associative array containing cohort IDs to filter by.
 * @return array An array containing the SQL condition string and the parameters.
 */
class cohort extends \core_reportbuilder\local\filters\cohort {

    /**
     * Return filter SQL
     *
     * @param array $values
     * @return array
     */
    public function get_sql_filter(array $values): array {
        global $DB;

        $fieldsql = $this->filter->get_field_sql();
        $params = $this->filter->get_field_params();

        $cohortids = $values["{$this->name}_values"] ?? [];
        if (empty($cohortids)) {
            return ['', []];
        }

        foreach ($cohortids as $key => $cohortid) {
            $likesql[] = $DB->sql_like("{$fieldsql}", ":cohortid$key", false, false);
            $cohortparams["cohortid$key"] = '%"' . $cohortid . '"%';
        }

        $cohortselect = '(' . implode(' OR ', $likesql) . ')';

        return ["$cohortselect", array_merge($params, $cohortparams)];
    }
}
