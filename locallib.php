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

define('CONTACTLIST_DEFAULT', get_string('globaldefault', 'local_contactlist'));
define('CONTACTLIST_VISIBLE', get_string('visible', 'local_contactlist'));
define('CONTACTLIST_INVISIBLE', get_string('invisible', 'local_contactlist'));
/**
 * get course participants from DB.
 * 
 * @param int $couseid
 * @return array
 */
function local_contactlist_get_participants(int $courseid, $userid, $additionalwhere, $additionalparams){
    global $DB;

    $params = array();
    $params['cid'] = $courseid;
    $params['cid2'] = $courseid;

    if (!empty($additionalwhere)) {
        $wheres[] = $additionalwhere;
        $params = array_merge($params, $additionalparams);
    }

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
/**
 * get number of visible course participants from DB.
 *
 * @param int $couseid
 * @return int
 */
function local_contactlist_get_total_visible(int $courseid){
    global $DB;
    
    $params = array();
    $params['cid'] = $courseid;
    $params['cid2'] = $courseid;

    $sql ="SELECT COUNT(uid) FROM
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
          OR (join1.data LIKE 'Yes' AND visib = 1)";
    
    $participants = $DB->count_records_sql($sql, $params);
    
    return $participants;
}
/**
 * get total number of course participants from DB.
 *
 * @param int $couseid
 * @return int
 */
function local_contactlist_get_total_course(int $courseid){
    global $DB;
    
    $params = array();
    $params['cid'] = $courseid;

    $sql ="SELECT COUNT(USER.id)
          FROM {role_assignments} AS asg
          JOIN {context} AS context ON asg.contextid = context.id AND context.contextlevel = 50
          JOIN {user} AS USER ON USER.id = asg.userid
          JOIN {course} AS course ON context.instanceid = course.id
          WHERE asg.roleid = 5
          AND course.id =:cid";
    
    $participants = $DB->count_records_sql($sql, $params);
    
    return $participants;
}
/**
/**
 * 
 * @param int $userid
 * @param int $courseid
 * @param int $show
 * @return number
 */
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
        $newrecord->visib    = $show;
        $DB->insert_record('local_contactlist_course_vis', $newrecord);
    }
}
/**
 * 
 * @param int $userid
 * @param int $courseid
 * @return int
 */
 function local_contactlist_courselevel_visibility($userid, $courseid){
    global $DB;

    $params = array();
    $params['courseid'] = $courseid;
    $params['userid'] = $userid;

    $visib  = $DB->get_record('local_contactlist_course_vis', $params);

    if(!$visib){
        return 0;
    }
    return $visib->visib;
    }

/**
 * Returns the SQL used by the contactlist table.
 *
 * @param int $courseid The course id
 * @param string $additionalwhere Any additional SQL to add to where
 * @param array $additionalparams The additional params
 * @return array
 */
function local_contactlist_get_participants_sql($courseid, $additionalwhere = '', $additionalparams = array()) {
    global $DB, $USER, $CFG;

    $context = \context_course::instance($courseid, MUST_EXIST);

    $params['contextlevel'] = CONTEXT_USER;

    $params['courseid'] = $courseid;
    $params['courseid2'] = $courseid;

    $select = "SELECT uid AS id, picture, firstname, lastname, email, join1.data, visib ";
    $from = "FROM
             (SELECT * FROM
             (SELECT USER.id as uid, USER.picture as picture, USER.firstname, USER.lastname, USER.email
             FROM {role_assignments}  asg
             JOIN {context} AS context ON asg.contextid = context.id AND context.contextlevel = 50
             JOIN {user} AS USER ON USER.id = asg.userid
             JOIN {course} AS course ON context.instanceid = course.id
             WHERE asg.roleid = 5
             AND course.id = :courseid) AS table1
             LEFT JOIN {user_info_data} as table2 on table1.uid = table2.userid ) as join1
             LEFT JOIN (SELECT * FROM {local_contactlist_course_vis} WHERE courseid = :courseid2) as table3 on join1.uid = table3.userid ";

    $where1 = "WHERE ((join1.data IS NULL AND visib = 1)
               OR (join1.data LIKE 'Yes' AND visib IS NULL)
               OR (join1.data LIKE 'Yes' AND visib = 1)) ";

    if (!empty($additionalwhere)) {
        $wheres[] = $additionalwhere;
        $params = array_merge($params, $additionalparams);
    }
    if ($wheres) {

        $where2 = 'AND ' . implode(' AND ', $wheres);
    } else {
        $where2 = '';
    }

    $where = $where1 . $where2;
    return array($select, $from, $where, $params);
}

/**
 * Returns the total number of visible participants for a given course.
 *
 * @param int $courseid The course id
 * @param string $additionalwhere Any additional SQL to add to where
 * @param array $additionalparams The additional params
 * @return int
 */
// function local_contactlist_get_total_participants($courseid, $additionalwhere = '', $additionalparams = array()) {
//     global $DB;

//     list($select, $from, $where, $params) = local_contactlist_get_participants_sql($courseid, $additionalwhere, $additionalparams);

//     return $DB->count_records_sql("SELECT COUNT(id) $from $where", $params);
// }

/**
 * Returns the participants for a given course.
 *
 * @param int $courseid The course id
 * @param string $additionalwhere Any additional SQL to add to where
 * @param array $additionalparams The additional params
 * @return moodle_recordset
 */
function local_contactlist_get_list($courseid, $additionalwhere = '', $additionalparams = array()) {
    global $DB;

    list($select, $from, $where, $params) = local_contactlist_get_participants_sql($courseid, $additionalwhere, $additionalparams);

    $sort = "ORDER BY join1.lastname ASC";

    return $DB->get_recordset_sql("$select $from $where $sort", $params, $limitfrom, $limitnum);
}

