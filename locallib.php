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
 * @package    local_contactlist
 * @author     Angela Baier
 * @copyright  2020 University of Vienna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * get number of visible course participants from DB.
 *
 * @param int $courseid
 * @return int
 */
function local_contactlist_get_total_visible(int $courseid) {
    global $DB;

    $sql = "SELECT COUNT(uid)
              FROM (
                    SELECT u.id as uid, uinfo.data, clvis.visib
                      FROM {role_assignments}  asg
                      JOIN {context} context ON asg.contextid = context.id AND context.contextlevel = 50
                      JOIN {user} u ON u.id = asg.userid
                      JOIN {course} course ON context.instanceid = course.id
                 LEFT JOIN {user_info_data} uinfo ON u.id = uinfo.userid
                 LEFT JOIN {local_contactlist_course_vis} clvis ON u.id = clvis.userid AND clvis.courseid = course.id
                     WHERE course.id = :cid
                    ) join1
              WHERE (join1.data IS NULL AND visib = 1)
                 OR (join1.data LIKE 'No' AND visib = 1)
                 OR (join1.data LIKE 'Yes' AND visib IS NULL)
                 OR (join1.data LIKE 'Yes' AND visib = 1)";

    return $DB->count_records_sql($sql, ['cid' => $courseid]);
}

/**
 * get total number of course participants from DB.
 *
 * @param int $courseid
 * @return int
 */
function local_contactlist_get_total_course(int $courseid) {
    global $DB;

    $sql = "SELECT COUNT(u.id)
              FROM {role_assignments} asg
              JOIN {context} context ON asg.contextid = context.id AND context.contextlevel = 50
              JOIN {user} u ON u.id = asg.userid
              JOIN {course} course ON context.instanceid = course.id
             WHERE course.id =:cid";

    return $DB->count_records_sql($sql, ['cid' => $courseid]);
}

/**
 * local_contactlist_save_update
 *
 * @param int $userid
 * @param int $courseid
 * @param int $show
 * @param int $showdefault
 */
function local_contactlist_save_update($userid, $courseid, $show, $showdefault) {
    global $DB;

    $record = local_contactlist_courselevel_visibility($userid, $courseid);

    if ($showdefault == 1 && $record) {
        $DB->delete_records('local_contactlist_course_vis', ['courseid' => $courseid, 'userid' => $userid]);
        return;
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
function local_contactlist_courselevel_visibility($userid, $courseid) {
    global $DB;

    $params = [
        'courseid' => $courseid,
        'userid' => $userid
    ];

    return $DB->get_record('local_contactlist_course_vis', $params);
}

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

    $wheres = array();

    $params = [
        'contextlevel' => CONTEXT_USER,
        'courseid' => $courseid
    ];

    $select = "SELECT uid AS id, picture, firstname, lastname, firstnamephonetic, lastnamephonetic, middlename,
               alternatename, imagealt, uid AS chat, email, join1.data, visib ";
    $from = "FROM (
                   SELECT u.id as uid, u.picture, u.firstname, u.lastname, u.email, u.firstnamephonetic, u.lastnamephonetic,
                          u.middlename, u.alternatename, u.imagealt, uinfo.data, clvis.visib
                     FROM {role_assignments}  asg
                     JOIN {context} context ON asg.contextid = context.id AND context.contextlevel = 50
                     JOIN {user} u ON u.id = asg.userid
                     JOIN {course} course ON context.instanceid = course.id
                LEFT JOIN {user_info_data} uinfo ON u.id = uinfo.userid
                LEFT JOIN {local_contactlist_course_vis} clvis ON u.id = clvis.userid AND clvis.courseid = course.id
                    WHERE course.id = :courseid
                  ) join1 ";

    $where1 = "WHERE ((join1.data IS NULL AND visib = 1)
                  OR (join1.data LIKE 'No' AND visib = 1)
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
    $sort = "ORDER BY join1.lastname ASC";

    return $DB->get_recordset_sql("$select $from $where $sort", $params);
}

/**
 * get comparison info global/local visibility
 * @param int $userid
 * @param int $courseid
 * @return string
 */
function local_contactlist_get_course_visibility_info_string($userid, $courseid) {
    $globalvisib = local_contactlist_get_global_setting($userid);
    $localvisib = local_contactlist_courselevel_visibility($userid, $courseid);

    $isvisible = false;
    $globalvisib = ($globalvisib && $globalvisib->data == 'Yes');

    if ($localvisib && ($localvisib->visib == 1 || ($globalvisib && $localvisib->visib != 2))) {
        $isvisible = true;
    } else if (!$localvisib && $globalvisib == 1) {
        $isvisible = true;
    }

    $infostring = $isvisible ? get_string('localvisible', 'local_contactlist') :
        get_string('localinvisible', 'local_contactlist');

    return '<p id="local-contactlist-info-box" class="alert alert-success">' . $infostring . '</p>';
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
    return html_writer::link($chaturl,
        '<span><i class="icon fa fa-comment fa-fw iconsmall"  title="Message" aria-label="Message"></i></span>',
        ['id' => 'message-user-button'.$userid, 'role' => 'button',
            'data-conversationid' => 0, 'data-userid' => $userid, 'class' => 'btn']);
}

/**
 * build html for moodle chat link.
 *
 * @param int $userid
 * @param int $courseid
 * @return string
 */
function local_contactlist_get_profile_link($userid, $courseid) {
    global $DB;

    $globalinfofield  = $DB->get_record('user_info_field', ['shortname' => 'contactlistdd']);

    $anchor = 'id_category_' . $globalinfofield->categoryid;
    $returnurl = (string)new moodle_url("/local/contactlist/studentview.php", ['id' => $courseid]);
    return (string)new moodle_url("/user/edit.php", ['id' => $userid, 'returnto' => 'url',
        'aria-expanded' => 'true', 'returnurl' => $returnurl], $anchor);

}

/**
 * get global rofile contactlist visibility setting
 *
 * @param int $userid
 * @return mixed
 */
function local_contactlist_get_global_setting($userid) {
    global $DB;

    $globalinfofield  = $DB->get_record('user_info_field', ['shortname' => 'contactlistdd']);
    $globalvisibility = $DB->get_record('user_info_data', ['userid' => $userid, 'fieldid' => $globalinfofield->id]);

    return $globalvisibility;
}
