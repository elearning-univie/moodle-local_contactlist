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
require_once($CFG->libdir.'/tablelib.php');


global $PAGE, $OUTPUT, $USER, $DB, $COURSE;

$page         = optional_param('page', 0, PARAM_INT);
$perpage      = optional_param('perpage', DEFAULT_PAGE_SIZE, PARAM_INT);
$contextid    = optional_param('contextid', 0, PARAM_INT);
$courseid     = optional_param('id', 0, PARAM_INT);

$PAGE->set_url('/contactlist/studentview.php', array(
    'page' => $page,
    'perpage' => $perpage,
    'contextid' => $contextid,
    'id' => $courseid));

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

$systemcontext = context_system::instance();
$isfrontpage = ($course->id == SITEID);

$frontpagectx = context_course::instance(SITEID);

if ($isfrontpage) {
    $PAGE->set_pagelayout('admin');
    course_require_view_participants($systemcontext);
} else {
    $PAGE->set_pagelayout('incourse');
    course_require_view_participants($context);
}

$PAGE->set_title("$course->shortname: ".get_string('pagetitle', 'local_contactlist'));
$PAGE->set_heading($course->fullname);
$PAGE->set_pagetype('course-view-' . $course->format);

$node = $PAGE->settingsnav->find('contactlist', navigation_node::TYPE_CONTAINER);

if ($node) {
    $node->force_open();
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pagetitle', 'local_contactlist'));

$mform = new \local_contactlist\form\contactlist_form(array('id' => $courseid));

$mform->display();

$formdata = $mform->get_data();

if($formdata) {
    local_contactlist_save_update($USER->id, $courseid, $formdata->localvisibility);
    //print_object($formdata);
}

// Initialise the table.
$table = new flexible_table('localcontactlist_table');
//$table = new html_table();
$table->head = array(
    get_string('name', 'local_contactlist'),
    get_string('surname', 'local_contactlist'),
    get_string('email', 'local_contactlist'));
$table->data = array();
$table->initialbars(true);
$table->class = '';
$table->id = '';

$table->sortable(true, 'lastname');
$table->initialbars(true);
$table->print_initials_bar();


$table->define_columns(array('firstname', 'lastname', 'email'));
$table->define_headers(array(
    get_string('name', 'local_contactlist'),
    get_string('surname', 'local_contactlist'),
    get_string('email', 'local_contactlist')));
$table->define_baseurl($PAGE->url);

$table->setup();

$tabledata = [];
$tabledata = local_contactlist_get_participants($courseid, $USER->id);
$tablearray = [];
foreach ($tabledata as $values) {
    $tablearray = array('firstname' => $values->firstname, 'lastname' => $values->lastname, 'email' => $values->email);
    $table->add_data($tablearray);
}

$table->print_html();

echo $OUTPUT->footer();
die();
