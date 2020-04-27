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
    $params['cid'] = $courseid;
    $params['cid2'] = $courseid;

    $sql ="SELECT uid, firstname, lastname, email FROM
          (SELECT * FROM
          (SELECT USER.id as uid, USER.firstname, USER.lastname, USER.email
          FROM {role_assignments} AS asg
          JOIN {context} AS context ON asg.contextid = context.id AND context.contextlevel = 50
          JOIN {user} AS USER ON USER.id = asg.userid
          JOIN {course} AS course ON context.instanceid = course.id
          WHERE asg.roleid = 5
          AND course.id =:cid) AS table1
          LEFT JOIN {user_info_data} as table2 on table1.uid = table2.userid ) as join1
          LEFT JOIN (SELECT * FROM {local_contactlist_course_vis} WHERE courseid =:cid2) as table3 on join1.uid = table3.userid
          WHERE (join1.data IS NULL AND visib = 1)
          OR (join1.data LIKE 'Yes' AND visib IS NULL)
          OR (join1.data LIKE 'Yes' AND visib = 1)
          ORDER BY join1.lastname ASC";

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
