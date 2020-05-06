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
 * get participants list
 *
 * @param int $courseid
 * @param int $userid
 * @param array $additionalwhere
 * @param array $additionalparams
 * @return array
 */
function local_contactlist_get_participants (int $courseid, $userid, $additionalwhere, $additionalparams) {
    global $DB;

    $params = array();
    $params['cid'] = $courseid;

    if (!empty($additionalwhere)) {
        $wheres[] = $additionalwhere;
        $params = array_merge($params, $additionalparams);
    }

    $sql = "SELECT uid, firstname, lastname, email FROM
          (SELECT u.id as uid, u.firstname, u.lastname, u.email, uinfo.data, clvis.visib
             FROM {role_assignments}  asg
             JOIN {context} context ON asg.contextid = context.id AND context.contextlevel = 50
             JOIN {user} u ON u.id = asg.userid
             JOIN {course} course ON context.instanceid = course.id
             LEFT JOIN {user_info_data} uinfo ON u.id = uinfo.userid
             LEFT JOIN {local_contactlist_course_vis} clvis ON u.id = clvis.userid AND clvis.courseid = course.id
             WHERE asg.roleid = 5
             AND course.id = :cid) join1
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
 * @param int $courseid
 * @return int
 */
function local_contactlist_get_total_visible (int $courseid) {
    global $DB;

    $params = array();
    $params['cid'] = $courseid;

    $sql = "SELECT COUNT(uid) FROM
          (SELECT u.id as uid, uinfo.data, clvis.visib
             FROM {role_assignments}  asg
             JOIN {context} context ON asg.contextid = context.id AND context.contextlevel = 50
             JOIN {user} u ON u.id = asg.userid
             JOIN {course} course ON context.instanceid = course.id
             LEFT JOIN {user_info_data} uinfo ON u.id = uinfo.userid
             LEFT JOIN {local_contactlist_course_vis} clvis ON u.id = clvis.userid AND clvis.courseid = course.id
             WHERE asg.roleid = 5
             AND course.id = :cid) join1
          WHERE (join1.data IS NULL AND visib = 1)
          OR (join1.data LIKE 'Yes' AND visib IS NULL)
          OR (join1.data LIKE 'Yes' AND visib = 1)";

    $participants = $DB->count_records_sql($sql, $params);

    return $participants;
}
/**
 * get total number of course participants from DB.
 *
 * @param int $courseid
 * @return int
 */
function local_contactlist_get_total_course (int $courseid) {
    global $DB;

    $params = array();
    $params['cid'] = $courseid;

    $sql = "SELECT COUNT(u.id)
          FROM {role_assignments} asg
          JOIN {context} context ON asg.contextid = context.id AND context.contextlevel = 50
          JOIN {user} u ON u.id = asg.userid
          JOIN {course} course ON context.instanceid = course.id
          WHERE asg.roleid = 5
          AND course.id =:cid";

    $participants = $DB->count_records_sql($sql, $params);

    return $participants;
}
/**
 * local_contactlist_save_update
 *
 * @param int $userid
 * @param int $courseid
 * @param int $show
 * @return number
 */
function local_contactlist_save_update ($userid, $courseid, $show) {
    global $DB;

    $params = array();
    $params['courseid'] = $courseid;
    $params['userid'] = $userid;

    $sql = "SELECT * FROM {local_contactlist_course_vis} cv
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
 * get visibility status for course.
 *
 * @param int $userid
 * @param int $courseid
 * @return int
 */
function local_contactlist_courselevel_visibility ($userid, $courseid) {
    global $DB;

    $params = array();
    $params['courseid'] = $courseid;
    $params['userid'] = $userid;

    $visib  = $DB->get_record('local_contactlist_course_vis', $params);

    if (!$visib) {
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
    $wheres = array();

    $context = \context_course::instance($courseid, MUST_EXIST);

    $params['contextlevel'] = CONTEXT_USER;

    $params['courseid'] = $courseid;

    $select = "SELECT uid AS id, picture, firstname, lastname, email, join1.data, visib ";
    $from = "FROM
             (SELECT u.id as uid, u.picture, u.firstname, u.lastname, u.email, uinfo.data, clvis.visib
             FROM {role_assignments}  asg
             JOIN {context} context ON asg.contextid = context.id AND context.contextlevel = 50
             JOIN {user} u ON u.id = asg.userid
             JOIN {course} course ON context.instanceid = course.id
             LEFT JOIN {user_info_data} uinfo ON u.id = uinfo.userid
             LEFT JOIN {local_contactlist_course_vis} clvis ON u.id = clvis.userid AND clvis.courseid = course.id
             WHERE asg.roleid = 5
             AND course.id = :courseid) join1 ";

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
 * Returns the participants for a given course.
 *
 * @param int $courseid The course id
 * @param string $additionalwhere Any additional SQL to add to where
 * @param array $additionalparams The additional params
 * @return moodle_recordset
 */
function local_contactlist_get_list ($courseid, $additionalwhere = '', $additionalparams = array()) {
    global $DB;
    $limitfrom = null;
    $limitnum = null;

    list($select, $from, $where, $params) = local_contactlist_get_participants_sql($courseid, $additionalwhere, $additionalparams);

    $sort = "ORDER BY join1.lastname ASC";

    return $DB->get_recordset_sql("$select $from $where $sort", $params, $limitfrom, $limitnum);
}
/**
 * extra user field function with alternative capabilities for contactlist.
 *
 * @param object $context Context
 * @param array $already Array of fields that we're going to show anyway
 *   so don't bother listing them
 * @return array Array of field names from user table, not including anything
 *   listed in $already
 */
function get_extra_user_fields_contactlist($context, $already = array()) {
    global $CFG;

    // Only users with permission get the extra fields.
    if (!has_capability('local/contactlist:viewuseridentity', $context)) {
        return array();
    }

    // Split showuseridentity on comma (filter needed in case the showuseridentity is empty).
    $extra = array_filter(explode(',', $CFG->showuseridentity));

    foreach ($extra as $key => $field) {
        if (in_array($field, $already)) {
            unset($extra[$key]);
        }
    }

    // If the identity fields are also among hidden fields, make sure the user can see them.
    $hiddenfields = array_filter(explode(',', $CFG->hiddenuserfields));
    $hiddenidentifiers = array_intersect($extra, $hiddenfields);

    if ($hiddenidentifiers) {
        if ($context->get_course_context(false)) {
            // We are somewhere inside a course.
            $canviewhiddenuserfields = has_capability('local/contactlist:viewhiddenuserfields', $context);

        } else {
            // We are not inside a course.
            $canviewhiddenuserfields = has_capability('local/contactlist:viewhiddendetails', $context);
        }

        if (!$canviewhiddenuserfields) {
            // Remove hidden identifiers from the list.
            $extra = array_diff($extra, $hiddenidentifiers);
        }
    }

    // Re-index the entries.
    $extra = array_values($extra);

    return $extra;
}
/**
 * WIP: get comparison info global/local visibility
 * @param int $userid
 * @param int $courseid
 * @return string
 */
function get_visibility_info_string ($userid, $courseid) {
    global $DB;

    $globalinfofield  = $DB->get_record('user_info_field', ['shortname' => 'contactlistdd']);

    $infostring = "";
    $params = array();
    $params['userid'] = $userid;
    $params['fieldid'] = $globalinfofield->id;

    $globalvisib  = $DB->get_record('user_info_data', $params);
    $localvisib = local_contactlist_courselevel_visibility ($userid, $courseid);

    if ($globalvisib) {
        if ($globalvisib->data = "Yes") {
            if ($localvisib = 2) { // local no
                return get_string('gyln', 'local_contactlist');
            } else {
                $infostring = "";
            }
        }
        if ($globalvisib->data = "No") { // global = No
            if ($localvisib = 1) { // local yes
                return get_string('gnly', 'local_contactlist');
            } else {
                $infostring = "";
            }
        }
    } else { // global not set
        if ($localvisib = 1) { // local yes
            return get_string('gnly', 'local_contactlist');
        } else {
            $infostring = "";
        }
    }

    return $infostring;
}

