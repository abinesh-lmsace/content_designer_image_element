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
use cdelement_rating\helper;

/**
 * Cdelement rating statistics entity base for report source.
 */
class statistics extends base {

    /**
     * Database tables that this entity uses and their default aliases
     *
     * @return array
     */
    protected function get_default_tables(): array {
        return ['cdelement_rating', 'contentdesigner'];
    }

    /**
     * The default title for this entity
     *
     * @return lang_string
     */
    protected function get_default_entity_title(): lang_string {
        return new lang_string('statistics', 'mod_contentdesigner');
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

        $this->set_table_alias('cdelement_rating', 'cdr');
        $tablealias = $this->get_table_alias('cdelement_rating');

        $columns = [];
        $columns[] = $this->get_average_column($tablealias);
        $columns[] = $this->get_count_column($tablealias);
        $columns[] = $this->get_distinctuser_column($tablealias);
        return $columns;
    }

    /**
     * Return average column.
     *
     * @param string $tablealias
     * @return \core_reportbuilder\local\report\column
     */
    protected function get_average_column(string $tablealias): \core_reportbuilder\local\report\column {
        return (new column(
            'average',
            new lang_string('average', 'mod_contentdesigner'),
            $this->get_entity_name()
        ))
        ->set_is_sortable(true)
        ->add_joins($this->get_joins())
        ->add_field("{$tablealias}.id", 'id')
        ->add_callback(static function ($value, $row): string {
            return helper::get_average($value);
        });
    }

    /**
     * Return count column.
     *
     * @param string $tablealias
     * @return \core_reportbuilder\local\report\column
     */
    protected function get_count_column(string $tablealias): \core_reportbuilder\local\report\column {
        // Rating result count.
        return (new column(
            'count',
            new lang_string('count', 'mod_contentdesigner'),
            $this->get_entity_name()
        ))
        ->set_is_sortable(true)
        ->add_joins($this->get_joins())
        ->add_field("{$tablealias}.id", 'id')
        ->add_callback(static function ($value, $row): string {
            return helper::get_count($value);
        });
    }

    /**
     * Return distinctuser count column.
     *
     * @param string $tablealias
     * @return \core_reportbuilder\local\report\column
     */
    protected function get_distinctuser_column(string $tablealias): \core_reportbuilder\local\report\column {
        // Distinctusers count.
        return (new column(
            'distinctuser',
            new lang_string('distinctusers', 'mod_contentdesigner'),
            $this->get_entity_name()
        ))
        ->set_is_sortable(true)
        ->add_joins($this->get_joins())
        ->add_field("{$tablealias}.id", 'id')
        ->add_callback(static function ($value, $row): string {
            return helper::get_distinctuser_count($value);
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
        return [$filters, $conditions];
    }

}
