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
 * Adds the contactlist to the course navigation
 *
 * @param navigation_node $parentnode
 * @param stdClass $course
 * @param context_course $context
 * @return void
 * @throws coding_exception
 * @throws moodle_exception
 */
function local_contactlist_extend_navigation_course(navigation_node $parentnode, stdClass $course, context_course $context) {
    global $DB;

    if (!has_capability('local/contactlist:view', $context)) {
        return;
    }

    $params = [
        'name' => 'Privacy Settings',
        'shortname' => 'conlistcoursevis',
        'instanceid' => $context->instanceid
    ];

    $sql = "SELECT cfd.intvalue FROM {customfield_data} cfd
              JOIN {customfield_field} cff ON cfd.fieldid = cff.id
              JOIN {customfield_category} cfc ON cff.categoryid = cfc.id
             WHERE cfc.name = :name
               AND cff.shortname = :shortname
               AND cfd.instanceid = :instanceid";

    $customfielddatavalue = $DB->get_field_sql($sql, $params);

    if ($customfielddatavalue == 2) {
        return;
    }

    $url = new moodle_url('/local/contactlist/studentview.php', array('id' => $course->id));
    $title = get_string('nodename', 'local_contactlist');
    $pix = new pix_icon('t/addcontact', $title);
    $childnode = navigation_node::create($title, $url, navigation_node::TYPE_SETTING, 'contactlist',
        'contactlist', $pix);

    $node = $parentnode->add_node($childnode);
    $node->nodetype = navigation_node::TYPE_SETTING;
}

/**
 * This function extends the navigation with the contactlist item
 *
 * @param navigation_node $navigation
 */
function local_contactlist_extend_navigation($navigation) {
    global $USER, $PAGE, $DB, $CFG;

    if (empty($USER->id) || $CFG->version >= 2022041900) {
        return;
    }

    if ('admin-index' === $PAGE->pagetype) {
        if (!($DB->record_exists('capabilities', array('name' => 'local/contactlist:view')))) {
            return;
        }
    }

    $context = context::instance_by_id($PAGE->context->id);
    if (!($context instanceof context_course || $context instanceof context_module)) {
        return;
    }

    $coursecontext = $context instanceof context_module ? $context->get_course_context() : $context;
    if (!has_capability('local/contactlist:view', $coursecontext, $USER)) {
        return;
    }

    $params = [
        'name' => 'Privacy Settings',
        'shortname' => 'conlistcoursevis',
        'instanceid' => $coursecontext->instanceid
    ];

    $sql = "SELECT cfd.intvalue FROM {customfield_data} cfd
              JOIN {customfield_field} cff ON cfd.fieldid = cff.id
              JOIN {customfield_category} cfc ON cff.categoryid = cfc.id
             WHERE cfc.name = :name
               AND cff.shortname = :shortname
               AND cfd.instanceid = :instanceid";

    $customfielddatavalue = $DB->get_field_sql($sql, $params);

    if (!$customfielddatavalue || $customfielddatavalue == 1) {
        $rootnodes = array($navigation->find('mycourses', navigation_node::TYPE_ROOTNODE),
            $navigation->find('courses', navigation_node::TYPE_ROOTNODE));

        foreach ($rootnodes as $mycoursesnode) {
            if (empty($mycoursesnode)) {
                continue;
            }

            $beforekey = null;
            $participantsnode = $mycoursesnode->find('participants', navigation_node::TYPE_CONTAINER);
            if ($participantsnode) { // Add the navnode after participants.
                $keys = $participantsnode->parent->get_children_key_list();
                $igrades = array_search('participants', $keys);
                if ($igrades !== false) {
                    if (isset($keys[$igrades + 1])) {
                        $beforekey = $keys[$igrades + 1];
                    }
                }
            }

            if ($beforekey == null) { // No participants node found, fall back to other variants!
                $activitiesnode = $mycoursesnode->find('activitiescategory', navigation_node::TYPE_CATEGORY);
                if ($activitiesnode == false) {
                    $custom = $mycoursesnode->find_all_of_type(navigation_node::TYPE_CUSTOM);
                    $sections = $mycoursesnode->find_all_of_type(navigation_node::TYPE_SECTION);
                    if (!empty($custom)) {
                        $first = reset($custom);
                        $beforekey = $first->key;
                    } else if (!empty($sections)) {
                        $first = reset($sections);
                        $beforekey = $first->key;
                    }
                } else {
                    $beforekey = 'activitiescategory';
                }
            }

            $url = new moodle_url('/local/contactlist/studentview.php', array('id' => $coursecontext->instanceid));
            $title = get_string('nodename', 'local_contactlist');
            $pix = new pix_icon('t/addcontact', $title);
            $childnode = navigation_node::create($title, $url, navigation_node::TYPE_CUSTOM, 'contactlist', 'contactlist', $pix);

            if (($mycoursesnode !== false && $mycoursesnode->has_children())) {
                $currentcourseinmycourses = $mycoursesnode->find($coursecontext->instanceid, navigation_node::TYPE_COURSE);
                if ($currentcourseinmycourses) {
                    $currentcourseinmycourses->add_node($childnode, $beforekey);
                    break;
                }
            }
        }
    }
}

