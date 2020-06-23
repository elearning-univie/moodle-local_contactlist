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
             WHERE course.id = :cid) join1
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
             WHERE course.id = :cid) join1
          WHERE (join1.data IS NULL AND visib = 1)
          OR (join1.data LIKE 'NO' AND visib = 1)
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
          WHERE course.id =:cid";

    $participants = $DB->count_records_sql($sql, $params);

    return $participants;
}
/**
 * local_contactlist_save_update
 *
 * @param int $userid
 * @param int $courseid
 * @param int $show
 * @param int $showdefault
 */
function local_contactlist_save_update ($userid, $courseid, $show, $showdefault) {
    global $DB;

    $params = array();
    $params['courseid'] = $courseid;
    $params['userid'] = $userid;

    $sql = "SELECT * FROM {local_contactlist_course_vis} cv
            WHERE cv.courseid =:courseid
            AND cv.userid =:userid";

    $record = $DB->get_record_sql($sql, $params);

    if ($showdefault == 1) {
        if ($record) {
            $DB->delete_records('local_contactlist_course_vis',$params);
            return;
        }
    }

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
 * @return array
 */
function local_contactlist_courselevel_visibility ($userid, $courseid) {
    global $DB;

    $params = array();
    $params['courseid'] = $courseid;
    $params['userid'] = $userid;

    $visib  = $DB->get_record('local_contactlist_course_vis', $params);

    return $visib;
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

    $select = "SELECT uid AS id, picture, firstname, lastname, uid AS chat, email, join1.data, visib ";
    $from = "FROM
             (SELECT u.id as uid, u.picture, u.firstname, u.lastname, u.email, uinfo.data, clvis.visib
             FROM {role_assignments}  asg
             JOIN {context} context ON asg.contextid = context.id AND context.contextlevel = 50
             JOIN {user} u ON u.id = asg.userid
             JOIN {course} course ON context.instanceid = course.id
             LEFT JOIN {user_info_data} uinfo ON u.id = uinfo.userid
             LEFT JOIN {local_contactlist_course_vis} clvis ON u.id = clvis.userid AND clvis.courseid = course.id
             WHERE course.id = :courseid) join1 ";

    $where1 = "WHERE ((join1.data IS NULL AND visib = 1)
               OR (join1.data LIKE 'NO' AND visib = 1)
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
function local_contactlist_get_extra_user_fields_contactlist($context, $already = array()) {

    return array('chat', 'email');
}

/**
 * WIP: get comparison info global/local visibility
 * @param int $userid
 * @param int $courseid
 * @return string
 */
function local_contactlist_get_course_visibility_info_string($userid, $courseid) {
    global $DB;

    $globalinfofield  = $DB->get_record('user_info_field', ['shortname' => 'contactlistdd']);

    $infostring = "";
    $params = array();
    $params['userid'] = $userid;
    $params['fieldid'] = $globalinfofield->id;

    $globalvisib  = $DB->get_record('user_info_data', $params);
    $localvisib = local_contactlist_courselevel_visibility ($userid, $courseid);

    $infostring = '<p id="local-contactlist-info-box" class="alert alert-danger">'. get_string('localinvisible', 'local_contactlist').'</p>';
    if ($globalvisib) {
        if ($globalvisib->data == "Yes") {
            if ($localvisib->visib == 2) {
                $infostring = '<p id="local-contactlist-info-box" class="alert alert-danger">'. get_string('localinvisible', 'local_contactlist').'</p>';
            } else {
                $infostring = '<p id="local-contactlist-info-box" class="alert alert-success">'. get_string('localvisible', 'local_contactlist').'</p>';
            }
        } else if ($globalvisib->data == "No") {
            if ($localvisib->visib == 1) {
                $infostring = '<p id="local-contactlist-info-box" class="alert alert-success">'. get_string('localvisible', 'local_contactlist').'</p>';
            } else if ($localvisib->visib == 2) {
                $infostring = '<p id="local-contactlist-info-box" class="alert alert-danger">'. get_string('localinvisible', 'local_contactlist').'</p>';
            }
        }
    } else {
        if ($localvisib->visib == 1) {
            $infostring = '<p id="local-contactlist-info-box" class="alert alert-success">'. get_string('localvisible', 'local_contactlist').'</p>';
        } else if ($localvisib->visib == 2) {
            $infostring = '<p id="local-contactlist-info-box" class="alert alert-danger">'. get_string('localinvisible', 'local_contactlist').'</p>';
        }
    }
    return $infostring;
}
/**
 * build html for moodle chat link.
 *
 * @param int $userid
 * @return string
 */
function local_contactlist_get_chat_html($userid) {
    global $PAGE;

    $chaturl = (string)new moodle_url("/message/index.php", ['id' => $userid]);
    $PAGE->requires->js_call_amd('core_message/message_user_button', 'send', array('#message-user-button' . $userid));
    return html_writer::link($chaturl, '<span><i class="icon fa fa-comment fa-fw iconsmall"  title="Message" aria-label="Message"></i></span>',
        ['id' => 'message-user-button'.$userid, 'role' => 'button', 'data-conversationid' => 0, 'data-userid' => $userid, 'class' => 'btn']);
}
/**
 * build html for moodle chat link.
 *
 * @param int $userid
 * @return string
 */
function local_contactlist_get_profile_link($userid, $courseid) {
    global $DB;

    $globalinfofield  = $DB->get_record('user_info_field', ['shortname' => 'contactlistdd']);

    $anchor = 'id_category_'.$globalinfofield->categoryid;
    $returnurl = (string)new moodle_url("/local/contactlist/studentview.php", ['id' => $courseid]);
    return (string)new moodle_url("/user/edit.php", ['id' => $userid, 'returnto' => 'url', 'aria-expanded' => 'true', 'returnurl' => $returnurl], $anchor);

}
/**
 * get global rofile contactlist visibility setting
 *
 * @param int $userid
 * @param int $courseid
 * @return mixed
 */
function local_contactlist_get_global_setting($userid, $courseid) {
    global $DB;

    $globalinfofield  = $DB->get_record('user_info_field', ['shortname' => 'contactlistdd']);
    $globalvisibility = $DB->get_record('user_info_data', ['userid' => $userid, 'fieldid' => $globalinfofield->id]);

    return $globalvisibility;
}