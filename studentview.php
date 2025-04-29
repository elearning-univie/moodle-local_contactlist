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
 * @copyright  2020 University of Vienna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot . '/local/contactlist/locallib.php');
require_once($CFG->dirroot . '/local/contactlist/contactlist_table.php');

require_once($CFG->libdir.'/tablelib.php');

global $PAGE, $OUTPUT, $USER, $DB, $COURSE;

$page         = optional_param('page', 0, PARAM_INT);
$perpage      = optional_param('perpage', 20, PARAM_INT);
$contextid    = optional_param('contextid', 0, PARAM_INT);
$courseid     = optional_param('id', 0, PARAM_INT);

$PAGE->set_url(new moodle_url('/local/contactlist/studentview.php', [
    'page' => $page,
    'perpage' => $perpage,
    'contextid' => $contextid,
    'id' => $courseid,
]));

if ($contextid) {
    $context = context::instance_by_id($contextid, MUST_EXIST);
    if ($context->contextlevel != CONTEXT_COURSE) {
        throw new moodle_exception('invalidcontext');
    }
    $course = $DB->get_record('course', ['id' => $context->instanceid], '*', MUST_EXIST);
} else {
    $course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
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

$customfieldcategory = $DB->get_record('customfield_category', ['name' => 'Privacy Settings']);
$customfieldfield = $DB->get_record('customfield_field',
                    ['categoryid' => $customfieldcategory->id, 'shortname' => 'conlistcoursevis']);
$customfielddata = $DB->get_record('customfield_data',
                   ['fieldid' => $customfieldfield->id, 'instanceid' => $context->instanceid]);

if (($customfielddata && $customfielddata->intvalue == 2) || !has_capability('local/contactlist:view', $context)) {
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('errorlistnotshown', 'local_contactlist'));
    echo $OUTPUT->footer();
    die();
}

$systemcontext = context_system::instance();
$isfrontpage = ($course->id == SITEID);

if ($isfrontpage) {
    $PAGE->set_pagelayout('admin');
    course_require_view_participants($systemcontext);
} else {
    $PAGE->set_pagelayout('incourse');
}

if ($node) {
    $node->force_open();
}

echo $OUTPUT->header();
echo $OUTPUT->heading($pagetitle);
echo html_writer::tag('br', null);
$mform = new \local_contactlist\form\contactlist_form();
$formdata = $mform->get_data();

if ($formdata) {
    local_contactlist_save_update($USER->id, $courseid, $formdata->visib, $formdata->usedefault);
    $mform = new \local_contactlist\form\contactlist_form();
}

$localvsglobal = local_contactlist_get_course_visibility_info_string($USER->id, $courseid);
echo $localvsglobal;

$mform->display();

$perpage = 20;
$baseurl = new moodle_url('/local/contactlist/studentview.php', [
    'contextid' => $context->id,
    'id' => $courseid,
    'perpage' => $perpage,
]);
$participanttable = new contactlist_table($courseid);
$participanttable->define_baseurl($baseurl);

$visibleno = local_contactlist_get_total_visible($courseid);
$totalno = local_contactlist_get_total_course($courseid);
$visbilityinfo = get_string('totalvsvisible', 'local_contactlist', ['visible' => $visibleno, 'total' => $totalno]);

echo html_writer::tag('br', null);
echo $visbilityinfo;
$participanttable->out($perpage, true);
echo $OUTPUT->footer();
