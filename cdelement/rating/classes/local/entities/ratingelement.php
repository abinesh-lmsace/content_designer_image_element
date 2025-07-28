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
use core_reportbuilder\local\filters\boolean_select;
use core_reportbuilder\local\filters\date;
use core_reportbuilder\local\filters\cohort as cohort_filter;
use core_reportbuilder\local\filters\course_selector;
use cdelement_rating\reportbuilder\filters\mycohort;
use core_course\reportbuilder\local\entities\course_category;
use core_reportbuilder\local\filters\category;
use cdelement_rating\helper;

/**
 * Cdelement rating entity base for report source.
 */
class ratingelement extends base {

    /**
     * Database tables that this entity uses and their default aliases
     *
     * @return array
     */
    protected function get_default_tables(): array {

        return [
            'user',
            'course',
            'course_categories',
            'contentdesigner',
            'cdelement_rating',
            'cdelement_rating_responses',
            'cdelement_rating_variables',
            'cohort',
        ];
    }

    /**
     * The default title for this entity
     *
     * @return lang_string
     */
    protected function get_default_entity_title(): lang_string {
        return new lang_string('pluginname', 'cdelement_rating');
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
        $this->set_table_alias('course_categories', 'cc');

        $tablealias = $this->get_table_alias('cdelement_rating');
        $contentdesigneralias = $this->get_table_alias('contentdesigner');

        $columns = [];
        $columns[] = $this->get_parent_activity_column($tablealias);
        $columns[] = $this->get_parent_activity_link_column($tablealias);
        $columns[] = $this->get_title_column($tablealias);
        $columns[] = $this->get_scale_column($tablealias);
        $columns[] = $this->get_content_column($tablealias);
        $columns[] = $this->get_changerating_column($tablealias);
        $columns[] = $this->get_label_column($tablealias);
        $columns[] = $this->get_mandatory_column($tablealias);

        return $columns;
    }

    /**
     * Return parent activity column.
     *
     * @param string $tablealias
     * @return \core_reportbuilder\local\report\column
     */
    protected function get_parent_activity_column(string $tablealias): \core_reportbuilder\local\report\column {

        // Element parent activity.
        return (new column(
            'parentactivity',
            new lang_string('parentactivity', 'mod_contentdesigner'),
            $this->get_entity_name()
        ))
        ->set_is_sortable(true)
        ->add_field("{$tablealias}.contentdesignerid", 'contentdesignerid')
        ->add_callback(static function ($value, $row): string {
            return helper::get_parentactivity($value);
        });
    }

    /**
     * Return parent activity link column.
     *
     * @param string $tablealias
     * @return \core_reportbuilder\local\report\column
     */
    protected function get_parent_activity_link_column(string $tablealias): \core_reportbuilder\local\report\column {

        // Element parent activity link.
        return (new column(
            'parentactivitylink',
            new lang_string('parentactivitylink', 'mod_contentdesigner'),
            $this->get_entity_name()
        ))
        ->set_is_sortable(true)
        ->add_field("{$tablealias}.contentdesignerid", 'contentdesignerid')
        ->add_callback(static function ($value, $row): string {
            return helper::get_parentactivity_link($value);
        });
    }

    /**
     * Return element title column.
     *
     * @param string $tablealias
     * @return \core_reportbuilder\local\report\column
     */
    protected function get_title_column(string $tablealias): \core_reportbuilder\local\report\column {

        // Element title.
        return (new column(
            'title',
            new lang_string('elementtitle', 'mod_contentdesigner'),
            $this->get_entity_name()
        ))
        ->set_is_sortable(true)
        ->add_field("{$tablealias}.title", 'title')
        ->add_callback(static function ($value, $row): string {
            $val = $row->title ?: '';
            return format_string($val);
        });
    }

    /**
     * Return element scale name column.
     *
     * @param string $tablealias
     * @return \core_reportbuilder\local\report\column
     */
    protected function get_scale_column(string $tablealias): \core_reportbuilder\local\report\column {

        // Element scale.
        return (new column(
            'scale',
            new lang_string('scale', 'mod_contentdesigner'),
            $this->get_entity_name()
        ))
        ->set_is_sortable(true)
        ->add_field("{$tablealias}.id", 'id')
        ->add_callback(static function ($value, $row): string {
            return helper::get_scale($value);
        });
    }

    /**
     * Return element content column.
     *
     * @param string $tablealias
     * @return \core_reportbuilder\local\report\column
     */
    protected function get_content_column(string $tablealias): \core_reportbuilder\local\report\column {

        // Element content.
        return (new column(
            'content',
            new lang_string('content', 'mod_contentdesigner'),
            $this->get_entity_name()
        ))
        ->set_is_sortable(true)
        ->add_field("{$tablealias}.content", 'content')
        ->add_callback(static function ($value, $row): string {
            return format_text($value);
        });
    }

    /**
     * Return element change rating column.
     *
     * @param string $tablealias
     * @return \core_reportbuilder\local\report\column
     */
    protected function get_changerating_column(string $tablealias): \core_reportbuilder\local\report\column {

        // Element change rating.
        return (new column(
            'changerating',
            new lang_string('changerating', 'mod_contentdesigner'),
            $this->get_entity_name()
        ))
        ->set_is_sortable(true)
        ->add_field("{$tablealias}.changerating", 'changerating')
        ->add_callback(static function ($value, $row): string {
            return ($value == 1) ? get_string('enable') : get_string('disable');
        });
    }

    /**
     * Return element label column.
     *
     * @param string $tablealias
     * @return \core_reportbuilder\local\report\column
     */
    protected function get_label_column(string $tablealias): \core_reportbuilder\local\report\column {

        // Element label.
        return (new column(
            'label',
            new lang_string('label', 'mod_contentdesigner'),
            $this->get_entity_name()
        ))
        ->set_is_sortable(true)
        ->add_field("{$tablealias}.label", 'label')
        ->add_callback(static function ($value, $row): string {
            $val = $row->label ?: '';
            return format_string($val);
        });
    }

    /**
     * Return element mandatory column.
     *
     * @param string $tablealias
     * @return \core_reportbuilder\local\report\column
     */
    protected function get_mandatory_column(string $tablealias): \core_reportbuilder\local\report\column {
        // Element mandatory.
        return (new column(
            'mandatory',
            new lang_string('mandatory', 'mod_contentdesigner'),
            $this->get_entity_name()
        ))
        ->set_is_sortable(true)
        ->add_field("{$tablealias}.mandatory", 'mandatory')
        ->add_callback(static function ($value, $row): string {
            return ($value == 1) ? get_string('yes') : get_string('no');
        });
    }

    /**
     * Defined filters for the notification entities.
     *
     * @return array
     */
    protected function get_all_filters(): array {
        global $USER;

        $filters = [];
        $conditions = [];

        $ratingelementalias = $this->get_table_alias('cdelement_rating');
        $contentdesigneralias = $this->get_table_alias('contentdesigner');
        $categoryalias = $this->get_table_alias('course_categories');
        $cohortalias = $this->get_table_alias('cohort');

        // Query for get the current user cohorts id list.
        $cohortsql = "SELECT c.id FROM {cohort} c
        JOIN {cohort_members} cm ON c.id = cm.cohortid WHERE cm.userid = :cohortuserid AND c.visible = 1";

        // Time created filter.
        $timecreated = (new filter(
            date::class,
            'timecreated',
            new lang_string('timecreated'),
            $this->get_entity_name(),
            "{$ratingelementalias}.timecreated"
        ))
        ->add_joins($this->get_joins());;

        $filters[] = $timecreated;
        $conditions[] = $timecreated;

        // Course category filter.
        $categoryfilter = (new filter(
            category::class,
            'category',
            new lang_string('categoryselect', 'core_reportbuilder'),
            $this->get_entity_name(),
            "{$categoryalias}.id"
        ))
            ->add_joins($this->get_joins())
            ->set_options([
                'requiredcapabilities' => 'moodle/category:viewcourselist',
            ]);

        $filters[] = $categoryfilter;
        $conditions[] = $categoryfilter;

        // We add our own custom course selector filter.
        $coursefilter = (new filter(
            course_selector::class,
            'courseselector',
            new lang_string('courseselect', 'core_reportbuilder'),
            $this->get_entity_name(),
            "{$contentdesigneralias}.course"
        ))
            ->add_joins(["LEFT JOIN {contentdesigner} ON {$contentdesigneralias}.id = {$ratingelementalias}.contentdesignerid"]);

        $filters[] = $coursefilter;
        $conditions[] = $coursefilter;

        // Cohort condition.
        $cohortcondition = (new filter(
            cohort_filter::class,
            'cohortselect',
            new lang_string('memberofcohorts', 'mod_contentdesigner'),
            $this->get_entity_name(),
            "cht.id",
        ))
        ->add_joins($this->get_joins());

        $conditions[] = $cohortcondition;

        // Current user same cohorts.
        $mycohort = (new filter(
            boolean_select::class,
            'usercohort',
            new lang_string('memberinmycohort', 'mod_contentdesigner'),
            $this->get_entity_name(),
            "cht.id IN ($cohortsql)",
            ['cohortuserid' => $USER->id]
        ))
        ->set_options([boolean_select::CHECKED => new lang_string('yes')])
        ->add_joins($this->get_joins());

        $conditions[] = $mycohort;

        return [$filters, $conditions];;
    }

}
