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
 * Privacy Subsystem implementation for local_contactlist.
 *
 * @package    local_contactlist
 * @copyright  2020 University of Vienna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_contactlist\privacy;
use core_privacy\local\metadata\collection;

defined('MOODLE_INTERNAL') || die();

/**
 * The local_contactlist module does store data. WIP.
 *
 * @package       local_contactlist
 * @author        Angela Baier
 * @copyright     2020 University of Vienna
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements 
// This plugin has data.
\core_privacy\local\metadata\provider

{
    /**
     * Database info.
     *
     * @param collection $collection
     * @return collection
     */
    public static function get_metadata(collection $collection) : collection {

        $collection->add_database_table(
            'user_info_data',
            [
                'userid' => 'privacy:metadata:user_info_data:userid',
                'data' => 'privacy:metadata:user_info_data:data',
            ],
            'privacy:metadata:user_info_data'
            );

        $collection->add_database_table(
            'local_contactlist_course_vis',
            [
                'userid' => 'privacy:metadata:local_contactlist_course_vis:userid',
                'courseid' => 'privacy:metadata:local_contactlist_course_vis:courseid',
                'visib' => 'privacy:metadata:local_contactlist_course_vis:visib',
            ],
            'privacy:metadata:local_contactlist_course_vis'
            );
        return $collection;
    }
    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param int $userid the userid.
     * @return contextlist the list of contexts containing user info for the user.
     */
    public static function get_contexts_for_userid(int $userid) : contextlist {
        $contextlist = new contextlist();

        // local plugin visibility settings
        $params = ['userid' => $userid, 'contextlevel' => CONTEXT_COURSE];
        $sql = "SELECT ctx.id
                FROM {context} ctx
                JOIN {course} c ON ctx.instanceid = c.id AND ctx.contextlevel = :contextlevel
                JOIN {local_contactlist_course_vis} ctl ON c.id = ctl.courseid
                WHERE ctl.userid = :userid";

        $contextlist->add_from_sql($sql, $params);

        // global visibility settings controlled by plugin
        $params = ['userid' => $userid, 'contextlevel' => CONTEXT_USER];
        $sql = "SELECT ctx.id
                FROM {context} ctx
                JOIN {user_info_data} uid ON uid.userid = ctx.instanceid AND uid.userid = :userid
                WHERE ctx.contextlevel = :contextlevel";
        $contextlist->add_from_sql($sql, $params);

        return $contextlist;
    }
    /**
     * 
     * @param userlist $userlist
     */
    public static function get_users_in_context(userlist $userlist) {
        global $DB;

        $context = $userlist->get_context();

        $params = [
            'instanceid'    => $context->instanceid,
        ];

        if ($context->contextlevel = CONTEXT_COURSE) {
        // userlist for course context.
        $sql = "SELECT ctl.userid 
                FROM {local_contactlist_course_vis} ctl
                WHERE ctl.courseid = :instanceid";

        $userlist->add_from_sql('userid', $sql, $params);
        }

        if ($context->contextlevel = CONTEXT_USER) {
        $sql = "SELECT ctl.userid
                FROM {user_info_data} uid
                WHERE uid.userid = :instanceid";

        $userlist->add_from_sql('userid', $sql, $params);
        }
    }
    /**
     * Export personal data for the given approved_contextlist. User and context information is contained within the contextlist.
     *
     * @param approved_contextlist $contextlist a list of contexts approved for export.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;
        if (empty($contextlist->get_contextids())) {
            return;
        }

        //        if (!empty($responsedata)) {
        //            $context = \context_module::instance($lastcmid);
        //            // Fetch the generic module data for the questionnaire.
        //            $contextdata = \core_privacy\local\request\helper::get_context_data($context, $user);
        //            // Merge with attempt data and write it.
        //            $contextdata = (object)array_merge((array)$contextdata, $responsedata);
        //            \core_privacy\local\request\writer::with_context($context)->export_data([], $contextdata);
    }
    /**
     * Delete all data for all users in the specified context.
     *
     * @param   context $context The specific context to delete data for.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;
        $globalinfofield  = $DB->get_record('user_info_field', ['shortname' => 'contactlistdd']);
        $userid = $contextlist->get_user()->id;

        if($context->contextlevel = CONTEXT_COURSE) {
            $DB->delete_records('choice_answers', ['choiceid' => $instanceid, 'userid' => $userid]);
        }
        if($context->contextlevel = CONTEXT_USER) {
            $DB->delete_records('user_info_data', ['fieldid' => $globalinfofield->id, 'userid' => $userid]);
        }
    }
    
    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param   approved_contextlist $contextlist The approved contexts and user information to delete information for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $globalinfofield  = $DB->get_record('user_info_field', ['shortname' => 'contactlistdd']);
        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if($context->contextlevel = CONTEXT_COURSE) {
                $DB->delete_records('choice_answers', ['choiceid' => $instanceid, 'userid' => $userid]);
            }
            if($context->contextlevel = CONTEXT_USER) {
                $DB->delete_records('user_info_data', ['fieldid' => $globalinfofield->id, 'userid' => $userid]);
            }
        }
    }

}