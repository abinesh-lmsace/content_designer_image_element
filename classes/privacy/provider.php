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
 * Privacy implementation for contentdesigner module
 *
 * @package   mod_contentdesigner
 * @copyright 2022, bdecent gmbh bdecent.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_contentdesigner\privacy;

use stdClass;
use context;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\helper;
use core_privacy\local\request\transform;
use core_privacy\local\request\writer;
use core_privacy\manager;

/**
 * The contentdesigner module stores user completion and invitation notified details.
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\core_userlist_provider,
    \core_privacy\local\request\plugin\provider {

    /**
     * List of used data fields summary meta key.
     *
     * @param collection $collection
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {

        // Module Completion table fields meta summary.
        $completionmetadata = [
            'contentdesignerid' => 'privacy:metadata:completion:contentdesignerid',
            'userid' => 'privacy:metadata:completion:userid',
            'completion' => 'privacy:metadata:completion:completion',
            'timecreated' => 'privacy:metadata:completion:timecreated',
        ];
        $collection->add_database_table('contentdesigner_completion',
            $completionmetadata, 'privacy:metadata:contentdesignercompletion');

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param  int         $userid    The user to search.
     * @return contextlist $contextlist The list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new \core_privacy\local\request\contextlist();
        // User completions.
        $sql = "SELECT c.id
                FROM {context} c
                INNER JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
                INNER JOIN {modules} m ON m.id = cm.module AND m.name = :modname
                INNER JOIN {contentdesigner} p ON p.id = cm.instance
                LEFT JOIN {contentdesigner_completion} pc ON pc.contentdesignerid = p.id
                WHERE pc.userid = :userid";
        $params = [
            'modname' => 'contentdesigner',
            'contextlevel' => CONTEXT_MODULE,
            'userid' => $userid,
        ];
        $contextlist->add_from_sql($sql, $params);

        return $contextlist;
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        if (!$context instanceof \context_module) {
            return;
        }

        $params = [
            'instanceid'    => $context->instanceid,
            'modulename'    => 'contentdesigner',
        ];

        // Discussion authors.
        $sql = "SELECT d.userid
        FROM {course_modules} cm
        JOIN {modules} m ON m.id = cm.module AND m.name = :modulename
        JOIN {contentdesigner} f ON f.id = cm.instance
        JOIN {contentdesigner_completion} d ON d.contentdesignerid = f.id
        WHERE cm.id = :instanceid";
        $userlist->add_from_sql('userid', $sql, $params);

        // Handle the 'contentdesigner' subplugin.
        manager::plugintype_class_callback(
            'contentdesignerelements',
            contentdesignerelements_provider::class,
            'get_users_in_context',
            [$userlist]
        );
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        $context = $userlist->get_context();
        $cm = $DB->get_record('course_modules', ['id' => $context->instanceid]);
        $contentdesigner = $DB->get_record('contentdesigner', ['id' => $cm->instance]);

        list($userinsql, $userinparams) = $DB->get_in_or_equal($userlist->get_userids(), SQL_PARAMS_NAMED);
        $params = array_merge(['contentdesignerid' => $contentdesigner->id], $userinparams);
        $sql = "contentdesignerid = :contentdesignerid AND userid {$userinsql}";
        $DB->delete_records_select('contentdesigner_completion', $sql, $params);

        // Handle the 'contentdesigner' subplugin.
        manager::plugintype_class_callback(
            'contentdesignerelements',
            contentdesignerelements_provider::class,
            'delete_data_for_users',
            [$userlist]
        );

    }

    /**
     * Delete user completion data for multiple context.
     *
     * @param approved_contextlist $contextlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            $instanceid = $DB->get_field('course_modules', 'instance', ['id' => $context->instanceid], MUST_EXIST);
            $DB->delete_records('contentdesigner_completion', ['contentdesignerid' => $instanceid, 'userid' => $userid]);
        }

        // Handle the 'contentdesigner' subplugin.
        manager::plugintype_class_callback(
            'contentdesignerelements',
            contentdesignerelements_provider::class,
            'delete_data_for_user',
            [$contextlist]
        );
    }

    /**
     * Delete all completion data for all users in the specified context.
     *
     * @param context $context Context to delete data from.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        if ($context->contextlevel != CONTEXT_MODULE) {
            return;
        }

        $cm = get_coursemodule_from_id('contentdesigner', $context->instanceid);
        if (!$cm) {
            return;
        }
        $DB->delete_records('contentdesigner_completion', ['contentdesignerid' => $cm->instance]);

        // Handle the 'quizaccess' subplugin.
        manager::plugintype_class_callback(
            'contentdesignerelements',
            contentdesignerelements_provider::class,
            'delete_subplugin_data_for_all_users_in_context',
            [$context]
        );
    }

    /**
     * Export all user data for the specified user, in the specified contexts, using the supplied exporter instance.
     *
     * @param   approved_contextlist    $contextlist    The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }
        // Context user.
        $user = $contextlist->get_user();
        list($contextsql, $contextparams) = $DB->get_in_or_equal($contextlist->get_contextids(), SQL_PARAMS_NAMED);

        $sql = "SELECT pc.id AS completionid, cm.id AS cmid, c.id AS contextid,
            p.id AS pid, p.course AS pcourse, pc.completion AS completion, pc.timecreated AS timecreated, pc.userid AS userid
            FROM {context} c
            INNER JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
            INNER JOIN {modules} m ON m.id = cm.module AND m.name = :modname
            INNER JOIN {contentdesigner} p ON p.id = cm.instance
            INNER JOIN {contentdesigner_completion} pc ON pc.contentdesignerid = p.id AND pc.userid = :userid
            WHERE c.id {$contextsql}
            ORDER BY cm.id, pc.id ASC";

        $params = [
            'modname' => 'contentdesigner',
            'contextlevel' => CONTEXT_MODULE,
            'userid' => $contextlist->get_user()->id,
        ];
        $completions = $DB->get_records_sql($sql, $params + $contextparams);

        self::export_contentdesigner_completions(
            array_filter(
                $completions,
                function(stdClass $completion) use ($contextlist): bool {
                    return $completion->userid == $contextlist->get_user()->id;
                }
            ),
            $user
        );

        list($contextsql, $contextparams) = $DB->get_in_or_equal($contextlist->get_contextids(), SQL_PARAMS_NAMED);

        $sql = "SELECT cm.id AS cmid, c.id AS contextid,
            p.id AS pid, p.course AS pcourse
            FROM {context} c
            INNER JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
            INNER JOIN {modules} m ON m.id = cm.module AND m.name = :modname
            INNER JOIN {contentdesigner} p ON p.id = cm.instance
            WHERE c.id {$contextsql}
            ORDER BY cm.id";

        $params = [
            'modname' => 'contentdesigner',
            'contextlevel' => CONTEXT_MODULE,
        ];
        $instances = $DB->get_records_sql($sql, $params + $contextparams);

        $contentdesignerids = array_column((array) $instances, 'pid');
        $components = \core_component::get_plugin_list('cdelement');
        $exportparams = [
            $contentdesignerids,
            $user,
        ];
        foreach (array_keys($components) as $component) {
            $classname = manager::get_provider_classname_for_component("cdelement_$component");
            if (class_exists($classname) && is_subclass_of($classname, contentdesignerelements_provider::class)) {
                $results = component_class_callback($classname, 'export_element_user_data', $exportparams);
                $instances = self::group_by_property($results, 'contentdesignerid');

                foreach ($instances as $instanceid => $element) {
                    if (empty($element)) {
                        continue;
                    }
                    $cm = get_coursemodule_from_instance('contentdesigner', $element[0]->contentdesignerid);
                    $context = \context_module::instance($cm->id);
                    // Fetch the generic module data for the questionnaire.
                    $contextdata = helper::get_context_data($context, $user);
                    unset($element['contentdesignerid']);
                    $contextdata = (object)array_merge((array)$contextdata, $element);
                    writer::with_context($context)->export_data(
                        [get_string('privacy:'.$component, 'cdelement_'.$component)],
                        $contextdata
                    );
                }
            }
        }
    }

    /**
     * Helper function to export completions.
     *
     * The array of "completions" is actually the result returned by the SQL in export_user_data.
     * It is more of a list of sessions. Which is why it needs to be grouped by context id.
     *
     * @param array $completions Array of completions to export the logs for.
     * @param stdclass $user User record object.
     */
    private static function export_contentdesigner_completions(array $completions, $user) {

        $completionsbycontextid = self::group_by_property($completions, 'contextid');

        foreach ($completionsbycontextid as $contextid => $completion) {
            $context = context::instance_by_id($contextid);
            $completionsbyid = self::group_by_property($completion, 'completionid');
            foreach ($completionsbyid as $completionid => $completions) {
                $completiondata = array_map(function($completion) use ($user) {
                    return [
                        'completed' => (($completion->completion == 1) ? get_string('yes') : get_string('no')),
                        'completedtime' => $completion->timecreated ? transform::datetime($completion->timecreated) : '-',
                    ];

                }, $completions);
                if (!empty($completiondata)) {
                    $context = context::instance_by_id($contextid);
                    // Fetch the generic module data for the questionnaire.
                    $contextdata = helper::get_context_data($context, $user);
                    $contextdata = (object)array_merge((array)$contextdata, $completiondata);
                    writer::with_context($context)->export_data(
                        [get_string('privacy:completion', 'contentdesigner').' '.$completionid],
                        $contextdata
                    );
                }
            };
        }

    }

    /**
     * Helper function to group an array of stdClasses by a common property.
     *
     * @param array $classes An array of classes to group.
     * @param string $property A common property to group the classes by.
     * @return array list of element seperated by given property.
     */
    private static function group_by_property(array $classes, string $property): array {
        return array_reduce(
            $classes,
            function (array $classes, stdClass $class) use ($property): array {
                $classes[$class->{$property}][] = $class;
                return $classes;
            },
            []
        );
    }

}
