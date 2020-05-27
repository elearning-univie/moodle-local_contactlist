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
 * Contactlist Student view
 *
 * @package    local_contactlist
 * @author     Angela Baier
 * @copyright  2020 University of Vienna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/dataformatlib.php');
require_once($CFG->dirroot . '/local/contactlist/locallib.php');
require_once($CFG->dirroot . '/local/contactlist/contactlist_table.php');

require_once($CFG->libdir.'/tablelib.php');


global $PAGE, $OUTPUT, $USER, $DB, $COURSE;

$page         = optional_param('page', 0, PARAM_INT);
$perpage      = optional_param('perpage', DEFAULT_PAGE_SIZE, PARAM_INT);
$contextid    = optional_param('contextid', 0, PARAM_INT);
$courseid     = optional_param('id', 0, PARAM_INT);
$filtersapplied = optional_param_array('unified-filters', [], PARAM_NOTAGS);
$filterwassubmitted = optional_param('unified-filter-submitted', 0, PARAM_BOOL);

$PAGE->set_url(new moodle_url('/local/contactlist/studentview.php', array(
    'page' => $page,
    'perpage' => $perpage,
    'contextid' => $contextid,
    'id' => $courseid)));

if ($contextid) {
    $context = context::instance_by_id($contextid, MUST_EXIST);
    if ($context->contextlevel != CONTEXT_COURSE) {
        print_error('invalidcontext');
    }
    $course = $DB->get_record('course', array('id' => $context->instanceid), '*', MUST_EXIST);
} else {
    $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
    $context = context_course::instance($course->id, MUST_EXIST);
}

require_login($course);

$pagetitle = get_string('pagetitle', 'local_contactlist');
$PAGE->set_title("$course->shortname: " . $pagetitle);
$PAGE->set_heading($course->fullname);
$node = $PAGE->settingsnav->find('contactlist', navigation_node::TYPE_CONTAINER);

if ($node) {
    $node->make_active();
}

if (!has_capability('local/contactlist:view', $context) ) {
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('errornotallowedonpage', 'local_contactlist'));
    echo $OUTPUT->footer();
    die();
}

$systemcontext = context_system::instance();
$isfrontpage = ($course->id == SITEID);

$frontpagectx = context_course::instance(SITEID);

if ($isfrontpage) {
    $PAGE->set_pagelayout('admin');
    course_require_view_participants($systemcontext);
} else {
    $PAGE->set_pagelayout('incourse');
    if (!has_capability('local/contactlist:view', $context) ) {
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('errornotallowedonpage', 'local_contactlist'));
        echo $OUTPUT->footer();
        die();
    }
}

$PAGE->set_pagetype('course-view-' . $course->format);

if ($node) {
    $node->force_open();
}

echo $OUTPUT->header();
echo $OUTPUT->heading($pagetitle);

if (!local_contactlist_courselevel_visibility($USER->id, $courseid)) {
    $PAGE->requires->js_call_amd('local_contactlist/studentsettings', 'init', ['courseid' => $courseid]);
}
$mform = new \local_contactlist\form\contactlist_form(array('id' => $courseid));
$mform->display();
$formdata = $mform->get_data();

if ($formdata) {
    local_contactlist_save_update($USER->id, $courseid, $formdata->visib);
}
$localvsglobal = local_contactlist_get_visibility_info_string($USER->id, $courseid);
echo '<p class="alert alert-warning" >'. $localvsglobal.'</p>';


$hasgroupfilter = false;
$lastaccess = 0;
$searchkeywords = [];
$enrolid = 0;
$status = -1;
foreach ($filtersapplied as $filter) {
    $filtervalue = explode(':', $filter, 2);
    $value = null;
    if (count($filtervalue) == 2) {
        $key = clean_param($filtervalue[0], PARAM_INT);
        $value = clean_param($filtervalue[1], PARAM_INT);
    } else {
        // Search string.
        $key = USER_FILTER_STRING;
        $value = clean_param($filtervalue[0], PARAM_TEXT);
    }

    switch ($key) {
        case USER_FILTER_ENROLMENT:
            $enrolid = $value;
            break;
        case USER_FILTER_GROUP:
            $groupid = $value;
            $hasgroupfilter = true;
            break;
        case USER_FILTER_LAST_ACCESS:
            $lastaccess = $value;
            break;
        case USER_FILTER_ROLE:
            $roleid = $value;
            break;
        case USER_FILTER_STATUS:
            // We only accept active/suspended statuses.
            if ($value == ENROL_USER_ACTIVE || $value == ENROL_USER_SUSPENDED) {
                $status = $value;
            }
            break;
        default:
            // Search string.
            $searchkeywords[] = $value;
            break;
    }
}

$perpage = 20;
$baseurl = new moodle_url('/local/contactlist/studentview.php', array(
    'contextid' => $context->id,
    'id' => $courseid,
    'perpage' => $perpage));
$participanttable = new contactlist_table($courseid, $searchkeywords);
$participanttable->define_baseurl($baseurl);

// Do this so we can get the total number of rows.
ob_start();
$participanttable->out($perpage, true);
$participanttablehtml = ob_get_contents();
ob_end_clean();

//\core_message\helper::messageuser_requirejs();

$visibleno = local_contactlist_get_total_visible($courseid);
$totalno = local_contactlist_get_total_course($courseid);
$visbilityinfo = get_string('totalvsvisible', 'local_contactlist', ['visible' => $visibleno, 'total' => $totalno]);

echo $visbilityinfo;
echo $participanttablehtml;
echo $OUTPUT->footer();