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
 * This file contains functions used by the local contactlist plugin.
 *
 * @package       local_contactlist
 * @author        Angela Baier
 * @copyright     2020 University of Vienna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * get course participants from DB.
 * 
 * @param int $couseid
 * @return array
 */
function local_contactlist_get_participants(int $courseid, $userid){
    global $DB;

    $params = array();
    $params['courseid'] = $courseid;
    $sql = "SELECT USER.id, USER.firstname, USER.lastname , USER.email
            FROM {role_assignments} AS asg
            JOIN {context} AS context ON asg.contextid = context.id AND context.contextlevel = 50
            JOIN {user} AS USER ON USER.id = asg.userid
            JOIN {course} AS course ON context.instanceid = course.id
            JOIN {user_info_data} AS uid ON uid.userid = USER.id 
            WHERE asg.roleid = 5
            AND uid.data LIKE 'Yes'
            AND course.id =:courseid
            ORDER BY USER.lastname DESC";
    
    $participants = $DB->get_records_sql($sql, $params);
    
    return $participants;
}

function local_contactlist_save_update($userid, $courseid, $show) {
    global $DB;

    $params = array();
    $params['courseid'] = $courseid;
    $params['userid'] = $userid;

    $sql = "SELECT * FROM {local_contactlist_course_vis} AS cv 
            WHERE cv.courseid =:courseid
            AND cv.userid =:userid";
    
    $record = $DB->get_record_sql($sql, $params);
    
    if ($record) {
        $record->visib = $show;
        $DB->update_record('local_contactlist_course_vis', $record);
    } else {
        $newrecord = new stdClass();
        $newrecord->courseid = $courseid;
        $newrecord->userid   = $userid;
        $newrecord->visib     = $show;
        $DB->insert_record('local_contactlist_course_vis', $newrecord);
    }

}
