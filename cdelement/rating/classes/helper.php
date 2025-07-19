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
 * Helper class of elements for rating.
 *
 * @package   cdelement_rating
 * @copyright  2025 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace cdelement_rating;

/**
 * Rating element instance extend the contentdesigner/elements base.
 *
 * @package   cdelement_rating
 */
class helper {

    /**
     * Manage the variables add form submitted data. Create new instance or update the existing instance.
     *
     * @param stdClass $formdata
     * @return void
     */
    public static function manage_instance($formdata) {
        global $DB, $PAGE;

        // Verfiy the current user has capability to manage variables.
        require_capability('cdelement/rating:managevariables', \context_system::instance());

        $record = clone $formdata;
        $record->categories = json_encode($record->categories);

        // Start the database transaction.
        $transaction = $DB->start_delegated_transaction();

        if ($formdata->status == 2) {
            $record->timearchived = time();
        } else {
            $record->timearchived = null;
        }

        if (isset($formdata->id) && $formdata->id != '' && $DB->record_exists('cdelement_rating_variables',
            ['id' => $formdata->id])) {

            $variableid = $formdata->id;

            // Verify the identity key is exists.
            $identitysql = 'SELECT * FROM {cdelement_rating_variables} WHERE shortname =:shortname AND id <> :variableid';

            // Record exists then stop inserting and redirect with error message.
            if ($DB->record_exists_sql($identitysql, ['variableid' => $variableid, 'shortname' => $record->shortname])) {
                redirect($PAGE->url, get_string('error:identityexists', 'mod_contentdesigner'));
            }

            $record->timemodified = time();

            // Update the variables record.
            $DB->update_record('cdelement_rating_variables', $record);

        } else {

            $record->timecreated = time();

            // Record exists then stop inserting and redirect with error message.
            if ($DB->record_exists('cdelement_rating_variables', ['shortname' => $record->shortname])) {
                // Redirect to the current page with error message.
                redirect($PAGE->url, get_string('error:identityexists', 'mod_contentdesigner'));
            }

            // Insert the record of the new variables.
            $variableid = $DB->insert_record('cdelement_rating_variables', $record);
        }

        // Allow the query changes to the DB.
        $transaction->allow_commit();

        return $variableid ?? false;
    }

    /**
     * Get the variable data.
     *
     * @param int $id
     */
    public static function get_data($id) {
        global $DB;

        if ($record = $DB->get_record('cdelement_rating_variables', ['id' => $id], '*', IGNORE_MULTIPLE)) {
            // Clone the variables record.
            $data = clone $record;
            // Decode the categories.
            $data->categories = $data->categories ? json_decode($data->categories) : [];

            return $data;
        }
        return false;
    }

    /**
     * Get variables for an course category. Retrieves variables for a given course,
     * filtering based on categories, status, and visibility.
     *
     * @param int|null $courseid The ID of the course. Defaults to null.
     *
     * @return array Associative array of variables.
     */
    public static function get_current_coursecat_variables($courseid=null) {
        global $DB;
        // Get course information.
        $course = get_course($courseid);
        // Generate the SQL LIKE condition for categories.
        $like = $DB->sql_like('categories', ':value');
        // Construct the SQL query.
        $sql = "SELECT * FROM {cdelement_rating_variables}
            WHERE (categories = '[]' OR categories = '' OR $like) AND status = 1";
        $params = ['value' => '%"'.$course->category.'"%'];
        // Retrieve records from the database.
        $records = $DB->get_records_sql_menu($sql, $params);
        // Format string values in the result.
        array_walk($records, function(&$val) {
            $val = format_string($val);
        });

        return $records;
    }

    /**
     * Get the most selected response for a rating.
     *
     * @param int $rateid Rating id.
     * @return int|null
     */
    public static function get_most_selected_response_count($rateid) {
        global $DB;

        // Fetch all responses for this rating element.
        $responses = $DB->get_records('cdelement_rating_responses', ['ratingid' => $rateid], '', 'userid, response');

        if (empty($responses)) {
            return null;
        }

        // Convert responses to a simple array.
        $responsevalues = array_map(function ($record) {
            return $record->response;
        }, $responses);

        // Count occurrences of each rating.
        $counts = array_count_values($responsevalues);

        // Find the most selected response.
        $mostselected = array_search(max($counts), $counts);

        return $mostselected ?? null;
    }

    /**
     * Get the average response for a rating with generate a dynamic message based on the user's response.
     *
     * @param int $rateid Rating id.
     * @return string
     */
    public static function get_average_response($rateid) {
        global $DB, $USER;

        $averageresponse = self::get_numeric_average_value($rateid);

        // Get the user's response.
        $currentresponse = $DB->get_field('cdelement_rating_responses', 'response', ['ratingid' => $rateid, 'userid' => $USER->id],
            '*', IGNORE_MULTIPLE);

        // Generate dynamic message.
        if ($currentresponse !== false) {
            $difference = round($currentresponse - $averageresponse, 1);
            if ($difference > 0) {
                $message = get_string('numeric:lowresponsestr', 'mod_contentdesigner', ['averageresponse' => $averageresponse,
                    'differance' => abs($difference), 'currentresponse' => $currentresponse]);
            } else if ($difference < 0) {
                $message = get_string('numeric:highresponsestr', 'mod_contentdesigner', ['averageresponse' => $averageresponse,
                    'differance' => abs($difference), 'currentresponse' => $currentresponse]);
            } else {
                $message = get_string('numeric:sameresponsestr', 'mod_contentdesigner', ['averageresponse' => $averageresponse]);
            }
        } else {
            $message = get_string('beforeratingstr', 'mod_contentdesigner');;
        }

        return $message;
    }

    /**
     * Get the numeric average value of a rating.
     *
     * @param int $rateid Rating id.
     * @return float
     */
    public static function get_numeric_average_value($rateid) {
        global $DB;

        // Fetch all responses for this rating element.
        $responses = $DB->get_records('cdelement_rating_responses', ['ratingid' => $rateid], '', 'userid, response');

        // Calculate the average rating.
        $total = count($responses);
        $sumresponses = array_sum(array_column($responses, 'response'));
        $average = ($total > 0) ? round($sumresponses / $total, 1) : 0;

        return $average;
    }

    /**
     * Get the count of responses for a rating.
     *
     * @param stdClass $data Rating element data.
     * @param int $response Response count.
     * @return string
     */
    public static function get_count_response($data, $response) {
        global $DB, $USER;

        $scale = $DB->get_record('scale', ['id' => $data->scale]);
        $scales = explode(",", $scale->scale);

        $responsescount = self::get_most_selected_response_count($data->id);
        $scalename = $scales[$responsescount - 1];

        if ($response == $responsescount) {
            $result = get_string('sameresponse', 'mod_contentdesigner', ['scalename' => trim($scalename)]);
        } else {
            $result = get_string('differentresponse', 'mod_contentdesigner', ['scalename' => trim($scalename)]);
        }

        return $result ?? '';
    }

    /**
     * Archive the variables.
     *
     * @param int $id
     * @return void
     */
    public static function archive_variables($id) {
        global $DB;
        $DB->update_record('cdelement_rating_variables', ['id' => $id, 'status' => 2, 'timearchived' => time()]);
    }

    /**
     * Activate the variables.
     *
     * @param int $id
     * @return void
     */
    public static function active_variables($id) {
        global $DB;
        $DB->update_record('cdelement_rating_variables', ['id' => $id, 'status' => 1, 'timearchived' => null]);
    }

    /**
     * Delete the variables.
     *
     * @param int $id
     * @return void
     */
    public static function delete_variables($id) {
        global $DB;
        if ($DB->record_exists('cdelement_rating_variables', ['id' => $id])) {
            $DB->delete_records('cdelement_rating_variables', ['id' => $id]);
            return true;
        }
        return false;
    }

    /**
     * Get the variable type for the given variable id.
     *
     * @param int $recordid Variable id
     * @return string
     */
    public static function get_variable_type($recordid) {
        global $DB;

        // Fetch the record from the database.
        $record = $DB->get_record('cdelement_rating_variables', ['id' => $recordid], 'type', IGNORE_MISSING);

        if (!$record || !isset($record->type)) {
            return get_string('notspecified', 'mod_contentdesigner'); // Default fallback.
        }

        // Define role options from the course context.
        $courseroles = get_roles_for_contextlevels(CONTEXT_COURSE);
        list($insql, $inparams) = $DB->get_in_or_equal(array_values($courseroles));
        $roles = $DB->get_records_sql("SELECT * FROM {role} WHERE id $insql", $inparams);
        $rolesoptions = role_fix_names($roles, null, ROLENAME_ALIAS, true);

        $types = [0 => get_string('notspecified', 'mod_contentdesigner')];
        $types = array_merge($types, $rolesoptions);

        for ($i = 1; $i <= 10; $i++) {
            $types[] = get_string('customcategory' . $i, 'mod_contentdesigner');
        }

        // Return the corresponding type name if it exists, otherwise return a default value.
        return isset($types[$record->type]) ? $types[$record->type] : get_string('notspecified', 'mod_contentdesigner');
    }

    /**
     * Get the count of elements in a variable.
     *
     * @param int $id Variable id.
     * @return int
     */
    public static function get_variables_elements_count($id) {
        global $DB;

        // Generate the SQL LIKE condition for variables.
        $like = $DB->sql_like('variables', ':value');

        // Construct the SQL query.
        $sql = "SELECT * FROM {cdelement_rating} WHERE ($like)";
        $params = ['value' => '%"'.$id.'"%'];

        // Retrieve records from the database.
        $records = $DB->get_records_sql_menu($sql, $params);

        return !empty($records) ? count($records) : 0;
    }

    /**
     * Get the total responses count for use given a variable.
     *
     * @param int $id Variable id.
     * @return int
     */
    public static function get_variables_responses_count($id) {
        global $DB;

        $like = $DB->sql_like('variables', ':value');
        $sql = "SELECT * FROM {cdelement_rating} WHERE ($like)";
        $params = ['value' => '%"'.$id.'"%'];
        $records = $DB->get_records_sql($sql, $params);

        $totalresponses = 0;
        foreach ($records as $record) {
            if ($DB->record_exists('cdelement_rating_responses', ['ratingid' => $record->id])) {
                $responses = $DB->get_records('cdelement_rating_responses', ['ratingid' => $record->id], '', 'userid, response');
                $totalresponses += count($responses);
            }
        }

        return $totalresponses;
    }

    /**
     * Get the parent activity name for the report source.
     *
     * @param int $id Content designer moduel id.
     * @return string
     */
    public static function get_parentactivity($id) {
        global $DB;
        $record = $DB->get_record('contentdesigner', ['id' => $id]);
        return (!empty($record->name)) ? $record->name : '';
    }

    /**
     * Get the parent activity link for the report source.
     *
     * @param int $id Content designe module id
     * @return string
     */
    public static function get_parentactivity_link($id) {
        global $DB;
        $record = $DB->get_record('contentdesigner', ['id' => $id]);
        $cm = get_coursemodule_from_instance('contentdesigner', $id);
        return \html_writer::link(new \moodle_url("/mod/contentdesigner/view.php", ['id' => $cm->id]), $record->name);
    }

    /**
     * Get the scales from the rating element.
     *
     * @param int $elementid Rating element id.
     * @return string
     */
    public static function get_scale($elementid) {
        global $DB;
        if ($record = $DB->get_record('cdelement_rating', ['id' => $elementid])) {
            if ($record->scale != 0) {
                $scale = $DB->get_record('scale', ['id' => $record->scale]);
                return !empty($scale->name) ? $scale->name : '';
            } else {
                return get_string('numeric', 'mod_contentdesigner');
            }
        }
        return '';
    }

    /**
     * Get the scale items name from the rating element.
     *
     * @param int $elementid Rating element id.
     * @return string
     */
    public static function get_scale_item_name($elementid) {
        global $DB;

        if ($record = $DB->get_record('cdelement_rating', ['id' => $elementid])) {
            if ($record->scale != 0) {
                // Fetch the scale record.
                $scale = $DB->get_record('scale', ['id' => $record->scale], 'scale');
                if ($scale) {
                    $items = explode(',', $scale->scale);
                    $scaleitems = array_map('trim', $items);
                    return implode(', ', $scaleitems);
                }
            } else {
                // Numeric scale: Generate numbers from 1 to $record->numericcount.
                $scalenum = (int) $record->numericcount;
                if ($scalenum > 0) {
                    return implode(', ', range(1, $scalenum));
                }
            }
        }
        return '';
    }

    /**
     * Get the scale items value from the rating element.
     *
     * @param int $elementid Rating element id.
     * @return string
     */
    public static function get_scale_items_with_values($elementid) {
        global $DB;

        // Get the rating element record.
        if ($record = $DB->get_record('cdelement_rating', ['id' => $elementid])) {
            if ($record->scale != 0) {
                $scale = $DB->get_record('scale', ['id' => $record->scale], 'scale');
                if (!$scale) {
                    return '';
                }
                $items = array_map('trim', explode(',', $scale->scale));
                $scaleitems = [];
                foreach ($items as $index => $name) {
                    $scaleitems[] = $name . ': <b>' . ($index + 1) ."</b><br>";
                }
                return implode('', $scaleitems);
            } else {
                // Numeric scale: Generate values from 1 to numericcount.
                $scalenum = (int) $record->numericcount;
                if ($scalenum > 0) {
                    $scaleitems = [];
                    for ($i = 1; $i <= $scalenum; $i++) {
                        $scaleitems[] = $i;
                    }
                    return implode(', ', $scaleitems);
                }
            }
        }
        return '';
    }

    /**
     * Get the responses value for the given rating element.
     *
     * @param int $id Rating element id.
     * @param int $response Rating element response.
     * @return string
     */
    public static function get_response_value_name($id, $response) {
        global $DB;

        // Get the rating element record.
        if ($record = $DB->get_record('cdelement_rating', ['id' => $id])) {
            if ($record->scale != 0) {
                $scale = $DB->get_record('scale', ['id' => $record->scale], 'scale');
                if ($scale) {
                    $items = array_map('trim', explode(',', $scale->scale));
                    return $items[$response - 1];
                }
            } else {
                return $response;
            }
        }
        return '';
    }

    /**
     * Get the average value of the element for the numerical values.
     *
     * @param int $id rating element id
     * @return string
     */
    public static function get_average($id) {
        global $DB;

        if ($record = $DB->get_record('cdelement_rating', ['id' => $id])) {
            if ($record->scale == 0 && $record->resulttype != 0) {

                $responses = $DB->get_records('cdelement_rating_responses', ['ratingid' => $record->id]);
                // Calculate the average rating.
                $total = count($responses);
                $sumresponses = array_sum(array_column($responses, 'response'));
                $average = ($total > 0) ? round($sumresponses / $total, 1) : 0;
                return "Average is: ". $average;
            }
        }
        return '';
    }

    /**
     * Get the count value of the element for the non numerical values.
     *
     * @param int $id rating element id
     * @return string
     */
    public static function get_count($id) {
        global $DB;
        if ($record = $DB->get_record('cdelement_rating', ['id' => $id])) {
            if ($record->scale != 0 && $record->resulttype != 0) {
                $responses = $DB->get_records('cdelement_rating_responses', ['ratingid' => $record->id]);

                if (empty($responses)) {
                    return null;
                }

                // Convert responses to a simple array.
                $responsevalues = array_map(function ($record) {
                    return $record->response;
                }, $responses);

                // Count occurrences of each rating.
                $counts = array_count_values($responsevalues);

                // Find the most selected response.
                $mostselected = array_search(max($counts), $counts);

                $scale = $DB->get_record('scale', ['id' => $record->scale]);
                $scales = explode(",", $scale->scale);
                $scalename = $scales[$mostselected - 1];
                return get_string('differentresponse', 'mod_contentdesigner', ['scalename' => trim($scalename)]);
            }
        }
        return '';
    }

    /**
     * Get the distinct user count from the reponses table.
     *
     * @param int $id rating element id
     * @return string
     */
    public static function get_distinctuser_count($id) {
        global $DB;
        if ($record = $DB->get_record('cdelement_rating', ['id' => $id])) {
            $responses = $DB->get_records('cdelement_rating_responses', ['ratingid' => $record->id]);
            if (!empty($responses)) {
                $count = $DB->count_records_sql("
                    SELECT COUNT(DISTINCT userid)
                    FROM {cdelement_rating_responses}
                    WHERE ratingid = :ratingid
                ", ['ratingid' => $id]);
                return  get_string('noofdistinctuser', 'mod_contentdesigner', ['count' => $count]);
            }
        }
        return '';
    }

    /**
     * Get the variable type options for display on the rating element variable tpye filter.
     *
     * @return array
     */
    public static function variabletypeoptions() {
        global $DB;
        $types = [ 0 => get_string('notspecified', 'mod_contentdesigner')];

        $courseroles = get_roles_for_contextlevels(CONTEXT_COURSE);
        list($insql, $inparams) = $DB->get_in_or_equal(array_values($courseroles));
        $roles = $DB->get_records_sql("SELECT * FROM {role} WHERE id $insql", $inparams);
        $rolesoptions = role_fix_names($roles, null, ROLENAME_ALIAS, true);
        $types = array_merge($types, $rolesoptions);

        for ($i = 1; $i <= 10; $i++) {
            $types[] = get_string('customcategory'. $i, 'mod_contentdesigner');
        }
        return $types;
    }
}
