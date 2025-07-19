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
 * Cdelement rating entities for report builder.
 *
 * @package   cdelement_rating
 * @copyright  2025 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace cdelement_rating\local\entities;

use lang_string;

use core_reportbuilder\local\entities\base;
use core_reportbuilder\local\report\{column, filter};
use core_reportbuilder\local\filters\date;
use cdelement_rating\helper;

/**
 * Cdelement rating user responses entity base for report source.
 */
class user_responses extends base {

    /**
     * Database tables that this entity uses and their default aliases
     *
     * @return array
     */
    protected function get_default_tables(): array {
        return ['user', 'cdelement_rating_responses'];
    }

    /**
     * The default title for this entity
     *
     * @return lang_string
     */
    protected function get_default_entity_title(): lang_string {
        return new lang_string('userresponses', 'mod_contentdesigner');
    }

    /**
     * Initialize the entity
     *
     * @return base
     */
    public function initialise(): base {
        $columns = $this->get_all_columns();
        foreach ($columns as $column) {
            $this->add_column($column);
        }

        list($filters, $conditions) = $this->get_all_filters();
        foreach ($filters as $filter) {
            $this->add_filter($filter);
        }

        foreach ($conditions as $condition) {
            $this->add_condition($condition);
        }

        return $this;
    }

    /**
     * Returns list of all available columns
     *
     * @return array
     */
    protected function get_all_columns(): array {

        $responsestablealias = $this->get_table_alias('cdelement_rating_responses');

        $columns = [];
        $columns[] = $this->get_response_value_numeric_column($responsestablealias);
        $columns[] = $this->get_response_value_name_column($responsestablealias);
        $columns[] = $this->get_response_timemodified_column($responsestablealias);
        $columns[] = $this->get_response_timecreated_column($responsestablealias);

        return $columns;
    }

    /**
     * Return user response value numeric column.
     *
     * @param string $tablealias
     * @return \core_reportbuilder\local\report\column
     */
    protected function get_response_value_numeric_column(string $tablealias): \core_reportbuilder\local\report\column {
        // User response value numeric.
        return (new column(
            'numericvalue',
            new lang_string('numericvalue', 'mod_contentdesigner'),
            $this->get_entity_name()
        ))
        ->set_is_sortable(true)
        ->add_joins($this->get_joins())
        ->add_field("{$tablealias}.response", 'response')
        ->add_callback(static function ($value, $row): string {
            return $value ?: 0;
        });
    }

    /**
     * Return user response value name column.
     *
     * @param string $tablealias
     * @return \core_reportbuilder\local\report\column
     */
    protected function get_response_value_name_column(string $tablealias): \core_reportbuilder\local\report\column {
        // User response value name.
        return (new column(
            'namevalue',
            new lang_string('namevalue', 'mod_contentdesigner'),
            $this->get_entity_name()
        ))
        ->set_is_sortable(true)
        ->add_joins($this->get_joins())
        ->add_field("{$tablealias}.ratingid", 'ratingid')
        ->add_field("{$tablealias}.response", 'response')
        ->add_callback(static function ($value, $row) {
            return helper::get_response_value_name($row->ratingid, $row->response);
        });
    }

    /**
     * Return user response time timecreated column.
     *
     * @param string $tablealias
     * @return \core_reportbuilder\local\report\column
     */
    protected function get_response_timecreated_column(string $tablealias): \core_reportbuilder\local\report\column {
        // User response time modified.
        return (new column(
            'timecreated',
            new lang_string('timecreated', 'mod_contentdesigner'),
            $this->get_entity_name()
        ))
        ->set_is_sortable(true)
        ->add_joins($this->get_joins())
        ->add_field("{$tablealias}.timecreated", 'timecreated')
        ->add_callback(static function ($value, $row): string {
            return $value ? userdate($value, get_string('strftimedatetime', 'langconfig')) : '-';
        });
    }

    /**
     * Return user response time modified column.
     *
     * @param string $tablealias
     * @return \core_reportbuilder\local\report\column
     */
    protected function get_response_timemodified_column(string $tablealias): \core_reportbuilder\local\report\column {
        // User response time modified.
        return (new column(
            'timemodified',
            new lang_string('timemodified', 'mod_contentdesigner'),
            $this->get_entity_name()
        ))
        ->set_is_sortable(true)
        ->add_joins($this->get_joins())
        ->add_field("{$tablealias}.timemodified", 'timemodified')
        ->add_callback(static function ($value, $row): string {
            return $value ? userdate($value, get_string('strftimedatetime', 'langconfig')) : '-';
        });
    }

    /**
     * Defined filters for the notification entities.
     *
     * @return array
     */
    protected function get_all_filters(): array {
        $filters = [];
        $conditions = [];

        $tablealias = $this->get_table_alias('cdelement_rating_responses');

        // Time created filter.
        $timeresponded = (new filter(
            date::class,
            'timeresponded',
            new lang_string('timeresponded', 'mod_contentdesigner'),
            $this->get_entity_name(),
            "{$tablealias}.timecreated"
        ))
        ->add_joins($this->get_joins());;

        $filters[] = $timeresponded;
        $conditions[] = $timeresponded;

        return [$filters, $conditions];;
    }

}
