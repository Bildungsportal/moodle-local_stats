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
 * @package    local_stats
 * @copyright  2024 Austrian Federal Ministry of Education
 * @author     GTN solutions
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_stats\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\deletion_criteria;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

defined('MOODLE_INTERNAL') || die;

class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider,
    \core_privacy\local\request\core_userlist_provider,
    \core_privacy\local\request\user_preference_provider {

    // METHODS FOR \core_privacy\local\metadata\provider

    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table(
            'local_stats',
            [
                'userid' => 'privacy:metadata:local_stats:userid',
                'contextid' => 'privacy:metadata:local_stats:contextid',
                'lang' => 'privacy:metadata:local_stats:lang',
                'path' => 'privacy:metadata:local_stats:path',
                'param' => 'privacy:metadata:local_stats:param',
                'timecreated' => 'privacy:metadata:local_stats:timecreated',
            ],
            'privacy:metadata:local_stats'
        );

        return $collection;
    }

    // METHODS FOR \core_privacy\local\request\plugin\provider

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param int $userid The user to search.
     * @return  contextlist   $contextlist  The list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new \core_privacy\local\request\contextlist();
        $sql = "SELECT c.id
                  FROM {context} c
                  JOIN {local_stats} ls ON ls.contextid = c.id
                  WHERE ls.userid = :userid";
        $params = [
            'userid' => $userid,
        ];
        $contextlist->add_from_sql($sql, $params);
        return $contextlist;
    }

    /**
     * Export all user data for the specified user, in the specified contexts, using the supplied exporter instance.
     *
     * @param approved_contextlist $contextlist The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            writer::with_context($context)->export_data(
                [
                    get_string('pluginname', 'local_stats'),
                    get_string('privacy:export:local_stats', 'local_stats'),
                ],
                (object) $DB->get_records('local_stats', ['contextid' => $context->id, 'userid' => $userid])
            );
        }
    }

    /**
     * Delete all personal data for all users in the specified context.
     *
     * @param context $context Context to delete data from.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        // This will not happen with this plugin.
        return;
    }

    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;
        if (empty($contextlist->count())) {
            return;
        }
        $DB->delete_records('local_stats', ['userid' => $contextlist->get_user()->id]);
    }

    // METHODS FOR \core_privacy\local\request\core_userlist_provider

    /**
     * Get the list of users who have data within a context.
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();
        $sql = "SELECT DISTINCT(userid)
                  FROM {local_stats}
                  WHERE contextid = :contextid";
        $params = ['contextid' => $context->id];
        $userlist->add_from_sql('userid', $sql, $params);
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        list($userinsql, $userinparams) = $DB->get_in_or_equal($userlist->get_userids());
        $DB->delete_records_select('local_stats', "userid $userinsql", $userinparams);
    }

    // METHODS FOR \core_privacy\local\request\user_preference_provider

    /**
     * Export all user preferences for the plugin.
     * @param int $userid The userid of the user whose data is to be exported.
     */
    public static function export_user_preferences(int $userid) {
        // no user preferences within this plugin.
    }
}
