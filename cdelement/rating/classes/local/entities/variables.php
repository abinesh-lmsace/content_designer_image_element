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
use core_reportbuilder\local\filters\select;
use core_reportbuilder\local\filters\text;
use cdelement_rating\helper;

/**
 * Cdelement rating variables entity base for report source.
 */
class variables extends base {

    /**
     * Database tables that this entity uses and their default aliases
     *
     * @return array
     */
    protected function get_default_tables(): array {
        return ['cdelement_rating_variables'];
    }

    /**
     * The default title for this entity
     *
     * @return lang_string
     */
    protected function get_default_entity_title(): lang_string {
        return new lang_string('variables', 'mod_contentdesigner');
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

        $tablealias = $this->get_table_alias('cdelement_rating_variables');

        $columns = [];
        $columns[] = $this->get_variable_fullname_column($tablealias);
        $columns[] = $this->get_variable_shortname_column($tablealias);
        $columns[] = $this->get_variable_type_column($tablealias);
        $columns[] = $this->get_variable_description_column($tablealias);
        $columns[] = $this->get_variable_course_catagories_column($tablealias);
        $columns[] = $this->get_variable_status_column($tablealias);

        return $columns;
    }

    /**
     * Return variable fullname column.
     *
     * @param string $tablealias
     * @return \core_reportbuilder\local\report\column
     */
    protected function get_variable_fullname_column(string $tablealias): \core_reportbuilder\local\report\column {
        // Element mandatory.
        return (new column(
            'fullname',
            new lang_string('fullname', 'mod_contentdesigner'),
            $this->get_entity_name()
        ))
        ->set_is_sortable(true)
        ->add_joins($this->get_joins())
        ->add_field("{$tablealias}.fullname", 'fullname')
        ->add_callback(static function ($value, $row): string {
            $val = $row->fullname ?: '';
            return format_string($val);
        });
    }

    /**
     * Return variable shortname column.
     *
     * @param string $tablealias
     * @return \core_reportbuilder\local\report\column
     */
    protected function get_variable_shortname_column(string $tablealias): \core_reportbuilder\local\report\column {
        // Element mandatory.
        return (new column(
            'shortname',
            new lang_string('shortname', 'mod_contentdesigner'),
            $this->get_entity_name()
        ))
        ->set_is_sortable(true)
        ->add_joins($this->get_joins())
        ->add_field("{$tablealias}.shortname", 'shortname')
        ->add_callback(static function ($value, $row): string {
            $val = $row->shortname ?: '';
            return format_string($val);
        });
    }

    /**
     * Return variable type column.
     *
     * @param string $tablealias
     * @return \core_reportbuilder\local\report\column
     */
    protected function get_variable_type_column(string $tablealias): \core_reportbuilder\local\report\column {
        // Element mandatory.
        return (new column(
            'type',
            new lang_string('type', 'mod_contentdesigner'),
            $this->get_entity_name()
        ))
        ->set_is_sortable(true)
        ->add_joins($this->get_joins())
        ->add_field("{$tablealias}.id", 'id')
        ->add_callback(static function ($value, $row): string {
            return helper::get_variable_type($value);
        });
    }

    /**
     * Return variable type column.
     *
     * @param string $tablealias
     * @return \core_reportbuilder\local\report\column
     */
    protected function get_variable_description_column(string $tablealias): \core_reportbuilder\local\report\column {
        // Element mandatory.
        return (new column(
            'description',
            new lang_string('description', 'mod_contentdesigner'),
            $this->get_entity_name()
        ))
        ->set_is_sortable(true)
        ->add_joins($this->get_joins())
        ->add_field("{$tablealias}.description", 'description')
        ->add_callback(static function ($value, $row): string {
            return format_text($value);
        });
    }

    /**
     * Return variable type column.
     *
     * @param string $tablealias
     * @return \core_reportbuilder\local\report\column
     */
    protected function get_variable_course_catagories_column(string $tablealias): \core_reportbuilder\local\report\column {
        // Element mandatory.
        return (new column(
            'categories',
            new lang_string('coursecategories', 'mod_contentdesigner'),
            $this->get_entity_name()
        ))
        ->set_is_sortable(true)
        ->add_joins($this->get_joins())
        ->add_field("{$tablealias}.categories", 'categories')
        ->add_callback(static function ($value, $row): string {
            $categories = !empty($value) ? json_decode($value) : [];
            $list = \core_course_category::get_many($categories);
            $list = array_map(fn($cate) => $cate->get_formatted_name(), $list);
            $categorylist = implode(', ', $list);
            return $categorylist ?? '';
        });
    }

    /**
     * Return variable type column.
     *
     * @param string $tablealias
     * @return \core_reportbuilder\local\report\column
     */
    protected function get_variable_status_column(string $tablealias): \core_reportbuilder\local\report\column {
        // Element mandatory.
        return (new column(
            'status',
            new lang_string('status', 'mod_contentdesigner'),
            $this->get_entity_name()
        ))
        ->set_is_sortable(true)
        ->add_joins($this->get_joins())
        ->add_field("{$tablealias}.status", 'status')
        ->add_callback(static function ($value, $row): string {
            return ($value == 1) ? get_string('active') : get_string('archived', 'mod_contentdesigner');
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

        // Variant filter.
        $typefilter = (new filter(
            select::class,
            'type',
            new lang_string('vartype', 'mod_contentdesigner'),
            $this->get_entity_name(),
            'type'
        ))
            ->add_joins($this->get_joins())
            ->set_options([
                helper::variabletypeoptions(),
            ]);

        $filters[] = $typefilter;
        $conditions[] = $typefilter;

        $fullnamefilter = (new filter(
            text::class,
            'fullname',
            new lang_string('varfullname', 'mod_contentdesigner'),
            $this->get_entity_name(),
            'fullname'
        ));

        $filters[] = $fullnamefilter;
        $conditions[] = $fullnamefilter;

        $shortnamefilter = (new filter(
            text::class,
            'shortname',
            new lang_string('varshortname', 'mod_contentdesigner'),
            $this->get_entity_name(),
            'shortname'
        ));

        $filters[] = $shortnamefilter;
        $conditions[] = $shortnamefilter;

        return [$filters, $conditions];
    }

}
