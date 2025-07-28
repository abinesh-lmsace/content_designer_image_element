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

namespace cdelement_rating\reportbuilder\datasource;

use core_reportbuilder\datasource;
use core_reportbuilder\local\entities\course;
use core_reportbuilder\local\entities\user;
use core_course\reportbuilder\local\entities\course_category;
use core_cohort\reportbuilder\local\entities\cohort;
use cdelement_rating\local\entities\ratingelement;
use cdelement_rating\local\entities\variables;
use cdelement_rating\local\entities\user_responses;
use cdelement_rating\local\entities\statistics;
use cdelement_rating\local\entities\scaleitems;
use core_cohort\reportbuilder\local\entities\cohort_member;

/**
 * Rating datasource definition for the list of schedules.
 */
class rating extends datasource {

    /**
     * Return user friendly name of the datasource
     *
     * @return string
     */
    public static function get_name(): string {
        return get_string('pluginname', 'cdelement_rating');
    }

    /**
     * Initialise report
     */
    protected function initialise(): void {


        // Basic rating entity which is main table in this datasource.
        $ratingelement = new ratingelement();
        // List of table alias used here.
        $contentdesigneralias = $ratingelement->get_table_alias('contentdesigner');
        $ratingelementalias = $ratingelement->get_table_alias('cdelement_rating');
        $responsesalias = $ratingelement->get_table_alias('cdelement_rating_responses');
        $variablesalias = $ratingelement->get_table_alias('cdelement_rating_variables');

        $ratingelement->initialise();

        // Setup the main table for this datasource. cdelement_rating is the main table.
        $this->set_main_table('cdelement_rating', 'cdr');
        $this->add_entity($ratingelement);

        $scaleitems = new scaleitems();
        $scaleitems->add_joins($ratingelement->get_joins());
        $this->add_entity($scaleitems);

        $userresponsesentity = new user_responses();
        $responsesalias = $userresponsesentity->get_table_alias('cdelement_rating_responses');
        $joins['responses'] = "LEFT JOIN {cdelement_rating_responses} {$responsesalias} ON {$responsesalias}.ratingid = cdr.id";
        $userresponsesentity->add_joins([$joins['responses']]);
        $this->add_entity($userresponsesentity->add_joins([$joins['responses']]));
        $this->add_join($joins['responses']);

        // Add core user join.
        $userentity = new user();
        $useralias = $userentity->get_table_alias('user');
        $joins['user'] = "LEFT JOIN {user} {$useralias} ON {$useralias}.id = {$responsesalias}.userid";
        $this->add_entity($userentity->add_join($joins['user']));

        $cohortmementity = new cohort_member();
        // Update the cohort memeber table alias, It uses cm as alias same as course_modules.
        $cohortmementity = $cohortmementity->set_table_alias('cohort_members', 'chtm');
        $cohortmemalias = $cohortmementity->get_table_alias('cohort_members');
        $cohortentity = new cohort();
        $cohortentity = $cohortentity->set_table_alias('cohort', 'cht');
        $cohortalias = $cohortentity->get_table_alias('cohort');
        $joins['cohort'] = "LEFT JOIN {cohort_members} {$cohortmemalias} ON {$cohortmemalias}.userid = {$responsesalias}.userid
        LEFT JOIN {cohort} {$cohortalias} ON {$cohortalias}.id = {$cohortmemalias}.cohortid";

        $ratingelement->add_join($joins['cohort']);
        $cohortentity->add_join($joins['user']);
        $this->add_entity($cohortentity->add_join($joins['cohort']));

        // Add core course entity.
        $coursentity = new course();
        $coursealias = $coursentity->get_table_alias('course');
        $joins['course'] = "LEFT JOIN {contentdesigner} {$contentdesigneralias} ON
            {$contentdesigneralias}.id = cdr.contentdesignerid
            LEFT JOIN {course} {$coursealias} ON {$coursealias}.id = {$contentdesigneralias}.course";
        $this->add_entity($coursentity->add_join($joins['course']));

        // Add core category entity.
        $categoryentity = new course_category();
        // Set the table alias for course categories.
        $categoryentity->set_table_alias('course_categories', 'cc');
        $categoryalias = $categoryentity->get_table_alias('course_categories');
        $joins['category'] = "LEFT JOIN {course_categories} {$categoryalias} ON {$categoryalias}.id = {$coursealias}.category";
        $this->add_entity($categoryentity->add_joins([$joins['course'], $joins['category']]));

        $variables = new variables();
        $variablesalias = $variables->get_table_alias('cdelement_rating_variables');
        $joins['variables'] = "LEFT JOIN {cdelement_rating_variables} {$variablesalias}
                        ON cdr.variables LIKE CONCAT('%\"', {$variablesalias}.id, '\"%')";
        $this->add_entity($variables->add_joins([$joins['variables']]));

        $stats = new statistics();
        $stats->add_joins($ratingelement->get_joins());

        $this->add_entity($stats);

        array_map([$this, 'add_join'], $joins);

        if (method_exists($this, 'add_all_from_entities')) {
            $this->add_all_from_entities();
        } else {
            // Add all the entities used in rating datasource.
            $this->include_all_from_entity($ratingelement->get_entity_name());
            $this->include_all_from_entity($userentity->get_entity_name());
            $this->include_all_from_entity($coursentity->get_entity_name());
            $this->include_all_from_entity($categoryentity->get_entity_name());
            $this->include_all_from_entity($variables->get_entity_name());
            $this->include_all_from_entity($userresponsesentity->get_entity_name());
            $this->include_all_from_entity($stats->get_entity_name());
            $this->include_all_from_entity($scaleitems->get_entity_name());
        }
    }

    /**
     * Adds all columns/filters/conditions from the given entity to the report at once
     *
     * @param string $entityname
     */
    protected function include_all_from_entity(string $entityname): void {
        $this->add_columns_from_entity($entityname);
        $this->add_filters_from_entity($entityname);
        $this->add_conditions_from_entity($entityname);
    }
    /**
     * Return the columns that will be added to the report once is created.
     *
     * @return string[]
     */
    public function get_default_columns(): array {
        return [
            'user:fullname',
            'course:fullname',
            'ratingelement:title',
            'ratingelement:parentactivity',
        ];
    }

    /**
     * Return the filters that will be added to the report once is created
     *
     * @return array
     */
    public function get_default_filters(): array {
        return [];
    }

    /**
     * Return the conditions that will be added to the report once is created
     *
     * @return array
     */
    public function get_default_conditions(): array {

        return [];
    }

}
