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
 * List of content designer activity report.
 *
 * @package   cdaddon_report
 * @copyright 2024, bdecent gmbh bdecent.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace cdaddon_report\table;

use context;
use context_helper;
use core_table\local\filter\filterset;
use stdClass;
use core_user\fields;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/user/lib.php');

/**
 * Class used to fetch participants based on a filterset.
 **/
class activity_report_search extends \core_user\table\participants_search {

    /**
     * @var filterset $filterset The filterset describing which participants to include in the search.
     */
    protected $filterset;

    /**
     * @var stdClass $course The course being searched.
     */
    protected $course;

    /**
     * @var \context_course $context The course context being searched.
     */
    protected $context;

    /**
     * @var string[] $userfields Names of any extra user fields to be shown when listing users.
     */
    protected $userfields;

    /**
     * Pulse instance data
     *
     * @var stdclass
     */
    public $contentdesigner;

    /**
     * Course module info.
     *
     * @var stdclass
     */
    public $cm;

    /**
     * Class constructor.
     *
     * @param stdClass $course The course being searched.
     * @param context $context The context of the search.
     * @param filterset $filterset The filterset used to filter the participants in a course.
     * @param int $instanceid pulse instnace id.
     */
    public function __construct(stdClass $course, context $context, filterset $filterset, int $instanceid) {
        global $PAGE, $DB;
        parent::__construct($course, $context, $filterset);
        $this->contentdesigner = $DB->get_record('contentdesigner', ['id' => $instanceid]);
        $this->cm = $PAGE->cm;
    }

    /**
     * Prepare SQL and associated parameters for users enrolled in the course.
     *
     * @return array SQL query data in the format ['sql' => '', 'forcedsql' => '', 'params' => []].
     */
    protected function get_enrolled_sql(): array {
        global $USER;

        $isfrontpage = ($this->context->instanceid == SITEID);
        $prefix = 'eu_';
        $filteruid = "{$prefix} u.id";
        $sql = '';
        $joins = [];
        $wheres = [];
        $params = [];
        // It is possible some statements must always be included (in addition to any filtering).
        $forcedprefix = "f{$prefix}";
        $forceduid = "{$forcedprefix}u.id";
        $forcedsql = '';
        $forcedjoins = [];
        $forcedwhere = "{$forcedprefix}u.deleted = 0";

        if (!$isfrontpage) {
            // Prepare any enrolment method filtering.
            [
                'joins' => $methodjoins,
                'where' => $wheres[],
                'params' => $methodparams,
            ] = $this->get_enrol_method_sql($filteruid);

            // Prepare any status filtering.
            [
                'joins' => $statusjoins,
                'where' => $statuswhere,
                'params' => $statusparams,
                'forcestatus' => $forcestatus,
            ] = $this->get_status_sql($filteruid, $forceduid, $forcedprefix);

            if ($forcestatus) {
                // Force filtering by active participants if user does not have capability to view suspended.
                $forcedjoins = array_merge($forcedjoins, $statusjoins);
                $statusjoins = [];
                $forcedwhere .= " AND ({$statuswhere})";
            } else {
                $wheres[] = $statuswhere;
            }

            $joins = array_merge($joins, $methodjoins, $statusjoins);
            $params = array_merge($params, $methodparams, $statusparams);
        }

        $groupids = [];

        if ($this->filterset->has_filter('groups')) {
            $groupids = $this->filterset->get_filter('groups')->get_filter_values();
        }

        // Force additional groups filtering if required due to lack of capabilities.
        // Note: This means results will always be limited to allowed groups, even if the user applies their own groups filtering.
        $canaccessallgroups = has_capability('moodle/site:accessallgroups', $this->context);
        $forcegroups = ($this->course->groupmode == SEPARATEGROUPS && !$canaccessallgroups);

        if ($forcegroups) {
            $allowedgroupids = array_keys(groups_get_all_groups($this->course->id, $USER->id));

            // Users not in any group in a course with separate groups mode should not be able to access the participants filter.
            if (empty($allowedgroupids)) {
                // The UI does not support this, so it should not be reachable unless someone is trying to bypass the restriction.
                throw new \coding_exception('User must be part of a group to filter by participants.');
            }

            $forceduid = "{$forcedprefix}u.id";
            $forcedjointype = $this->get_groups_jointype(\core_table\local\filter\filter::JOINTYPE_ANY);
            $forcedgroupjoin = groups_get_members_join($allowedgroupids, $forceduid, $this->context, $forcedjointype);

            $forcedjoins[] = $forcedgroupjoin->joins;
            $forcedwhere .= " AND ({$forcedgroupjoin->wheres})";

            $params = array_merge($params, $forcedgroupjoin->params);

            // Remove any filtered groups the user does not have access to.
            $groupids = array_intersect($allowedgroupids, $groupids);
        }

        // Prepare any user defined groups filtering.
        if ($groupids) {
            $groupjoin = groups_get_members_join($groupids, $filteruid, $this->context, $this->get_groups_jointype());

            $joins[] = $groupjoin->joins;
            $params = array_merge($params, $groupjoin->params);
            if (!empty($groupjoin->wheres)) {
                $wheres[] = $groupjoin->wheres;
            }
        }

        // Combine the relevant filters and prepare the query.
        $joins = array_filter($joins);
        if (!empty($joins)) {
            $joinsql = implode("\n", $joins);

            $sql = "SELECT DISTINCT {$prefix}u.id
                               FROM {user} {$prefix}u
                                    {$joinsql}
                              WHERE {$prefix}u.deleted = 0";
        }

        $wheres = array_filter($wheres);
        if (!empty($wheres)) {
            if ($this->filterset->get_join_type() === $this->filterset::JOINTYPE_ALL) {
                $wheresql = '(' . implode(') AND (', $wheres) . ')';
            } else {
                $wheresql = '(' . implode(') OR (', $wheres) . ')';
            }

            $sql .= " AND ({$wheresql})";
        }

        // Prepare any SQL that must be applied.
        if (!empty($forcedjoins)) {
            $forcedjoinsql = implode("\n", $forcedjoins);
            $forcedsql = "SELECT DISTINCT {$forcedprefix}u.id
                                     FROM {user} {$forcedprefix}u
                                          {$forcedjoinsql}
                                    WHERE {$forcedwhere}";
        }

        return [
            'sql' => $sql,
            'forcedsql' => $forcedsql,
            'params' => $params,
        ];
    }

    /**
     * Prepare SQL and associated parameters for participants in the course.
     *
     * @param string $additionalwhere Additional WHERE clause to apply.
     * @param array $additionalparams Additional parameters to apply.
     * @return array SQL query data in the format.
     */
    protected function get_participants_sql(string $additionalwhere, array $additionalparams): array {
        global $CFG;

        $isfrontpage = ($this->course->id == SITEID);
        $accesssince = 0;
        // Whether to match on users who HAVE accessed since the given time (ie false is 'inactive for more than x').
        $matchaccesssince = false;

        // The alias for the subquery that fetches all distinct course users.
        $usersubqueryalias = 'targetusers';
        // The alias for {user} within the distinct user subquery.
        $inneruseralias = 'udistinct';
        // Inner query that selects distinct users in a course who are not deleted.
        // Note: This ensures the outer (filtering) query joins on distinct users, avoiding the need for GROUP BY.
        $innerselect = "SELECT DISTINCT {$inneruseralias}.id";
        $innerselect .= ', cda.id AS cdattemptid, cda.attempt, cda.grade, cda.timefinish';
        $innerjoins = ["{user} {$inneruseralias}"];
        $innerwhere = "WHERE {$inneruseralias}.deleted = 0 AND {$inneruseralias}.id <> :siteguest";
        $params = ['siteguest' => $CFG->siteguest];

        $outerjoins = ["JOIN {user} u ON u.id = {$usersubqueryalias}.id"];
        $wheres = [];

        if ($this->filterset->has_filter('accesssince')) {
            $accesssince = $this->filterset->get_filter('accesssince')->current();

            // Last access filtering only supports matching or not matching, not any/all/none.
            $jointypenone = $this->filterset->get_filter('accesssince')::JOINTYPE_NONE;
            if ($this->filterset->get_filter('accesssince')->get_join_type() === $jointypenone) {
                $matchaccesssince = true;
            }
        }

        [
            // SQL that forms part of the filter.
            'sql' => $esql,
            // SQL for enrolment filtering that must always be applied (eg due to capability restrictions).
            'forcedsql' => $esqlforced,
            'params' => $eparams,
        ] = $this->get_enrolled_sql();
        $params = array_merge($params, $eparams);

        // Get the fields for all contexts because there is a special case later where it allows
        // matches of fields you can't access if they are on your own account.
        $userfields = fields::for_identity(null)->with_userpic();
        ['selects' => $userfieldssql, 'joins' => $userfieldsjoin, 'params' => $userfieldsparams, 'mappings' => $mappings] =
                (array)$userfields->get_sql('u', true);
        if ($userfieldsjoin) {
            $outerjoins[] = $userfieldsjoin;
            $params = array_merge($params, $userfieldsparams);
        }

        // Include any compulsory enrolment SQL (eg capability related filtering that must be applied).
        if (!empty($esqlforced)) {
            $outerjoins[] = "JOIN ({$esqlforced}) fef ON fef.id = u.id";
        }

        // Include any enrolment related filtering.
        if (!empty($esql)) {
            $outerjoins[] = "LEFT JOIN ({$esql}) ef ON ef.id = u.id";
            $wheres[] = 'ef.id IS NOT NULL';
        }

        if ($isfrontpage) {
            $outerselect = "SELECT u.lastaccess $userfieldssql";
            if ($accesssince) {
                $wheres[] = user_get_user_lastaccess_sql($accesssince, 'u', $matchaccesssince);
            }
        } else {
            $outerselect = "SELECT DISTINCT  COALESCE(ul.timeaccess, 0) AS lastaccess $userfieldssql";
            // Not everybody has accessed the course yet.
            $outerjoins[] = 'LEFT JOIN {user_lastaccess} ul ON (ul.userid = u.id AND ul.courseid = :courseid2)';
            $params['courseid2'] = $this->course->id;
            if ($accesssince) {
                $wheres[] = user_get_course_lastaccess_sql($accesssince, 'ul', $matchaccesssince);
            }

            // Make sure we only ever fetch users in the course (regardless of enrolment filters).
            $innerjoins[] = "JOIN {user_enrolments} ue ON ue.userid = {$inneruseralias}.id";
            $innerjoins[] = 'JOIN {enrol} e ON e.id = ue.enrolid
                                      AND e.courseid = :courseid1';
            $innerjoins[] = 'LEFT JOIN {contentdesigner_attempts} cda ON cda.userid = ue.userid
                   AND cda.contentdesignerid = :cdid';
            $params['courseid1'] = $this->course->id;
            $params['cdid'] = $this->contentdesigner->id;
        }

        // Performance hacks - we preload user contexts together with accounts.
        $ccselect = ', ' . context_helper::get_preload_record_columns_sql('ctx');
        $ccjoin = 'LEFT JOIN {context} ctx ON (ctx.instanceid = u.id AND ctx.contextlevel = :contextlevel)';
        $params['contextlevel'] = CONTEXT_USER;
        $outerselect .= $ccselect;
        $outerselect .= ', cda2.id AS attemptid, cda2.attempt, cda2.grade, cda2.timefinish';
        $outerjoins[] = $ccjoin;
        $outerjoins[] = 'LEFT JOIN {contentdesigner_attempts} cda2 ON cda2.userid = u.id AND cda2.contentdesignerid = :cdid2';
        $params['cdid2'] = $this->contentdesigner->id;
        // Apply any role filtering.
        if ($this->filterset->has_filter('roles')) {
            [
                'where' => $roleswhere,
                'params' => $rolesparams,
            ] = $this->get_roles_sql();

            if (!empty($roleswhere)) {
                $wheres[] = "({$roleswhere})";
            }

            if (!empty($rolesparams)) {
                $params = array_merge($params, $rolesparams);
            }
        }

        // Apply any country filtering.
        if ($this->filterset->has_filter('country')) {
            [
                'where' => $countrywhere,
                'params' => $countryparams,
            ] = $this->get_country_sql();

            if (!empty($countrywhere)) {
                $wheres[] = "($countrywhere)";
            }

            if (!empty($countryparams)) {
                $params = array_merge($params, $countryparams);
            }
        }

        // Apply any keyword text searches.
        if ($this->filterset->has_filter('keywords')) {
            [
                'where' => $keywordswhere,
                'params' => $keywordsparams,
            ] = $this->get_keywords_search_sql($mappings);

            if (!empty($keywordswhere)) {
                $wheres[] = "({$keywordswhere})";
            }

            if (!empty($keywordsparams)) {
                $params = array_merge($params, $keywordsparams);
            }
        }

        // Add any supplied additional forced WHERE clauses.
        if (!empty($additionalwhere)) {
            $innerwhere .= " AND ({$additionalwhere})";
            $params = array_merge($params, $additionalparams);
        }

        // Prepare final values.
        $outerjoinsstring = implode("\n", $outerjoins);
        $innerjoinsstring = implode("\n", $innerjoins);
        if ($wheres) {
            switch ($this->filterset->get_join_type()) {
                case $this->filterset::JOINTYPE_ALL:
                    $wherenot = '';
                    $wheresjoin = ' AND ';
                    break;
                case $this->filterset::JOINTYPE_NONE:
                    $wherenot = ' NOT ';
                    $wheresjoin = ' AND NOT ';

                    // Some of the $where conditions may begin with `NOT` which results in `AND NOT NOT ...`.
                    // To prevent this from breaking on Oracle the inner WHERE clause is wrapped in brackets, making it
                    // `AND NOT (NOT ...)` which is valid in all DBs.
                    $wheres = array_map(function($where) {
                        return "({$where})";
                    }, $wheres);

                    break;
                default:
                    // Default to 'Any' jointype.
                    $wherenot = '';
                    $wheresjoin = ' OR ';
                    break;
            }

            $outerwhere = 'WHERE ' . $wherenot . implode($wheresjoin, $wheres);
        } else {
            $outerwhere = '';
        }

        if ($this->filterset->has_filter('attemptmethod')) {
            $attemptmethod = $this->filterset->get_filter('attemptmethod')->get_filter_values();
            if (!empty($attemptmethod)) {
                switch ($attemptmethod[0]) {
                    case 1:
                        $outerwhere = " INNER JOIN (
                                SELECT userid, contentdesignerid, MAX(grade) AS maxgrade
                                FROM {contentdesigner_attempts}
                                WHERE contentdesignerid = :cd1
                                GROUP BY userid, contentdesignerid
                            ) highestattempt
                            ON cda2.userid = highestattempt.userid
                            AND cda2.contentdesignerid = highestattempt.contentdesignerid
                            AND cda2.grade = highestattempt.maxgrade
                            WHERE cda2.contentdesignerid = :contentdesignerid";
                        $params['contentdesignerid'] = $this->cm->instance;
                        $params['cd1'] = $this->cm->instance;
                        break;
                    case 2:
                        $outerwhere = " INNER JOIN (
                            SELECT userid, MAX(id) AS lastattemptid
                            FROM {contentdesigner_attempts}
                            WHERE contentdesignerid = :cd2
                            GROUP BY userid
                            ) latestattempts ON cda2.userid = latestattempts.userid AND cda2.id = latestattempts.lastattemptid
                            WHERE cda2.contentdesignerid = :contentdesignerid";
                        $params['contentdesignerid'] = $this->cm->instance;
                        $params['cd2'] = $this->cm->instance;
                        break;
                    case 3:
                        if ($this->filterset->has_filter('attemptnumber')) {
                            $attemptnumber = $this->filterset->get_filter('attemptnumber')->get_filter_values();
                            if (!empty($attemptnumber)) {
                                $outerwhere = ' WHERE cda2.attempt = :attemptnumber ';
                                $params['attemptnumber'] = $attemptnumber[0];
                            }
                        }
                        break;
                }
            }
        }

        $return = [
            'subqueryalias' => $usersubqueryalias,
            'outerselect' => $outerselect,
            'innerselect' => $innerselect,
            'outerjoins' => $outerjoinsstring,
            'innerjoins' => $innerjoinsstring,
            'outerwhere' => $outerwhere,
            'innerwhere' => $innerwhere,
            'params' => $params,
        ];

        return $return;
    }
}
