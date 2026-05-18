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
 * @copyright  2020 University of Vienna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Returns the roles used in a course with display names respecting local overrides.
 *
 * @param int $courseid
 * @return array Array of ['id' => roleid, 'name' => display_name]
 */
function local_contactlist_get_course_roles(int $courseid) {
    global $DB;

    $context = context_course::instance($courseid);

    $sql = "SELECT DISTINCT r.id, r.name, r.shortname, r.sortorder
              FROM {role_assignments} ra
              JOIN {role} r ON r.id = ra.roleid
             WHERE ra.contextid = :contextid
          ORDER BY r.sortorder";

    $roles = $DB->get_records_sql($sql, ['contextid' => $context->id]);

    $rolenames = role_fix_names($roles, $context, ROLENAME_ALIAS, true);

    $result = [];
    foreach ($roles as $role) {
        $result[] = ['id' => $role->id, 'name' => $rolenames[$role->id]];
    }
    return $result;
}

/**
 * get number of visible course participants from DB.
 *
 * @param int $courseid
 * @param int $roleid Optional role filter (0 = all roles).
 * @return int
 */
function local_contactlist_get_total_visible(int $courseid, int $roleid = 0) {
    global $DB;

    $params = ['cid' => $courseid];
    $rolefilter = '';
    if ($roleid > 0) {
        $rolefilter = ' AND asg.roleid = :roleid';
        $params['roleid'] = $roleid;
    }

    $sql = "SELECT COUNT(uid)
              FROM (
                    SELECT u.id as uid, uinfo.data, clvis.visib
                      FROM {role_assignments}  asg
                      JOIN {context} context ON asg.contextid = context.id AND context.contextlevel = 50
                      JOIN {user} u ON u.id = asg.userid
                      JOIN {course} course ON context.instanceid = course.id
                      JOIN {user_info_field} uinfofield ON uinfofield.shortname = 'contactlistdd'
                 LEFT JOIN {user_info_data} uinfo ON u.id = uinfo.userid AND uinfo.fieldid = uinfofield.id
                 LEFT JOIN {local_contactlist_course_vis} clvis ON u.id = clvis.userid AND clvis.courseid = course.id
                     WHERE course.id = :cid{$rolefilter}
                    ) join1
              WHERE (join1.data IS NULL AND visib = 1)
                 OR (join1.data LIKE 'No' AND visib = 1)
                 OR (join1.data LIKE 'Yes' AND visib IS NULL)
                 OR (join1.data LIKE 'Yes' AND visib = 1)";

    return $DB->count_records_sql($sql, $params);
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

    if ($showdefault == 1) {
        if ($record) {
            $DB->delete_records('local_contactlist_course_vis', ['courseid' => $courseid, 'userid' => $userid]);
        }
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
        'userid' => $userid,
    ];

    return $DB->get_record('local_contactlist_course_vis', $params);
}

/**
 * Returns the participants for a given course.
 *
 * @param int $courseid The course id
 * @param string $additionalwhere Any additional SQL to add to where
 * @param array $additionalparams The additional params
 * @param string $sort Optional SQL sort.
 * @param int $limitfrom Return a subset of records, starting at this point (optional).
 * @param int $limitnum Return a subset comprising this many records (optional, required if $limitfrom is set).
 * @param int $roleid Optional role filter (0 = all roles).
 * @return moodle_recordset
 */
function local_contactlist_get_list(
    $courseid,
    $additionalwhere = '',
    $additionalparams = [],
    $sort = '',
    $limitfrom = 0,
    $limitnum = 0,
    $roleid = 0
) {
    global $DB;

    $wheres = [];

    $params = [
        'contextlevel' => CONTEXT_USER,
        'courseid' => $courseid,
    ];

    $rolefilter = '';
    if ($roleid > 0) {
        $rolefilter = ' AND asg.roleid = :roleid';
        $params['roleid'] = $roleid;
    }

    $select = "SELECT uid AS id, picture, firstname, lastname, firstnamephonetic, lastnamephonetic, middlename,
               alternatename, imagealt, uid AS chat, email, join1.data, visib ";
    $from = "FROM (
                   SELECT u.id as uid, u.picture, u.firstname, u.lastname, u.email, u.firstnamephonetic, u.lastnamephonetic,
                          u.middlename, u.alternatename, u.imagealt, uinfo.data, clvis.visib
                     FROM {role_assignments}  asg
                     JOIN {context} context ON asg.contextid = context.id AND context.contextlevel = 50
                     JOIN {user} u ON u.id = asg.userid
                     JOIN {course} course ON context.instanceid = course.id
                     JOIN {user_info_field} uinfofield ON uinfofield.shortname = 'contactlistdd'
                LEFT JOIN {user_info_data} uinfo ON u.id = uinfo.userid AND uinfo.fieldid = uinfofield.id
                LEFT JOIN {local_contactlist_course_vis} clvis ON u.id = clvis.userid AND clvis.courseid = course.id
                    WHERE course.id = :courseid{$rolefilter}
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

    return $DB->get_recordset_sql("$select $from $where $sort", $params, $limitfrom, $limitnum);
}

/**
 * Determine if a user is currently visible in a course's contact list.
 *
 * @param int $userid
 * @param int $courseid
 * @return bool
 */
function local_contactlist_is_visible($userid, $courseid) {
    $globalvisib = local_contactlist_get_global_setting($userid);
    $localvisib  = local_contactlist_courselevel_visibility($userid, $courseid);

    $globalvisib = ($globalvisib && $globalvisib->data == 'Yes');

    if ($localvisib) {
        if ($localvisib->visib == 1) {
            return true;
        }
        if ($globalvisib && $localvisib->visib != 2) {
            return true;
        }
        return false;
    }

    return $globalvisib;
}

/**
 * Build the template context for the personal settings panel.
 *
 * @param int  $userid
 * @param int  $courseid
 * @param bool $expanded Whether the panel starts expanded.
 * @return array Mustache template context.
 */
function local_contactlist_get_settings_panel_context($userid, $courseid, $expanded = true) {
    global $OUTPUT;
    $isvisible    = local_contactlist_is_visible($userid, $courseid);
    $globalsetting = local_contactlist_get_global_setting($userid);
    $localvisib   = local_contactlist_courselevel_visibility($userid, $courseid);

    // Info bar.
    $alertclass   = $isvisible ? 'alert-success' : 'alert-warning';
    $statustext   = $isvisible
        ? get_string('localvisible', 'local_contactlist')
        : get_string('localinvisible', 'local_contactlist');
    $toggletext   = $expanded
        ? get_string('hidepersonalsettings', 'local_contactlist')
        : get_string('showpersonalsettings', 'local_contactlist');
    $chevronclass = $expanded ? 'fa-chevron-up' : 'fa-chevron-down';

    // Profile setting (read-only display).
    $profileval          = ($globalsetting && $globalsetting->data === 'Yes') ? 'Yes' : 'No';
    $currentprofilevalue = $profileval === 'Yes'
        ? get_string('visible', 'local_contactlist')
        : get_string('invisible', 'local_contactlist');

    // Course-level visibility setting.
    $usedefault   = 0;
    $localsetting = 2;
    if (!$localvisib) {
        $usedefault = 1;
        if ($globalsetting && $globalsetting->data === 'Yes') {
            $localsetting = 1;
        }
    } else {
        $localsetting = $localvisib->visib;
    }

    $visiboptions = [
        ['value' => 1, 'label' => get_string('visible', 'local_contactlist'), 'selected' => $localsetting == 1],
        ['value' => 2, 'label' => get_string('invisible', 'local_contactlist'), 'selected' => $localsetting == 2],
    ];

    return [
        'alertclass'                => $alertclass,
        'helpicon'                  => $OUTPUT->help_icon('localvisibility', 'local_contactlist'),
        'statustext'                => $statustext,
        'toggletext'                => $toggletext,
        'chevronclass'              => $chevronclass,
        'expanded'                  => $expanded,
        'formaction'                => (new moodle_url('/local/contactlist/studentview.php'))->out(false),
        'courseid'                  => $courseid,
        'sesskey'                   => sesskey(),
        'currentprofilevalue'       => $currentprofilevalue,
        'profilelink'               => local_contactlist_get_profile_link($userid, $courseid),
        'usedefault'                => (bool) $usedefault,
        'visiboptions'              => $visiboptions,
        'str_personalsettings'      => get_string('personalsettings', 'local_contactlist'),
        'str_personalsettings_desc' => get_string('personalsettings_desc', 'local_contactlist'),
        'str_currentprofilesetting' => get_string('currentprofilesetting', 'local_contactlist'),
        'str_change'                => get_string('change', 'local_contactlist'),
        'str_coursevisibility'      => get_string('coursevisibility', 'local_contactlist'),
        'str_useprofilesetting'     => get_string('useprofilesetting', 'local_contactlist'),
        'str_individualsetting'     => get_string('individualsetting', 'local_contactlist'),
        'str_save'                  => get_string('save', 'local_contactlist'),
    ];
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
    $PAGE->requires->js_call_amd('core_message/message_user_button', 'send', ['#message-user-button' . $userid]);
    return html_writer::link(
        $chaturl,
        '<span><i class="icon fa fa-comment fa-fw iconsmall"  title="Message" aria-label="Message"></i></span>',
        ['id' => 'message-user-button' . $userid, 'role' => 'button',
            'data-conversationid' => 0, 'data-userid' => $userid, 'class' => 'btn']
    );
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
