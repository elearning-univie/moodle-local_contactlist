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
 * This function extends the navigation with the contactlist item
 *
 * @param navigation_node $navigation
 */
function local_contactlist_extend_navigation($navigation) {
    global $USER, $PAGE, $DB;

    if (empty($USER->id)) {
        return;
    }

    if ('admin-index' === $PAGE->pagetype) {
        $exists = $DB->record_exists('capabilities', array('name' => 'local/contactlist:view'));

        if (!$exists) {
            return;
        }
    }

    $context = context::instance_by_id($PAGE->context->id);
    $isvalidcontext = ($context instanceof context_course || $context instanceof context_module) ? true : false;
    if (!$isvalidcontext) {
        return;
    }

    $coursecontext = null;
    if ($context instanceof context_module) {
        $coursecontext = $context->get_course_context();
    } else {
        $coursecontext = $context;
    }

    if (!has_capability('local/contactlist:view', $coursecontext, $USER)) {
        return;
    }

    $icon = null;
    $pluginname = get_string('pluginname', 'local_contactlist');
    $url = new moodle_url('/local/contactlist/studentview.php', array('id' => $coursecontext->instanceid));

    $currentcoursenode = $navigation->find('currentcourse', $navigation::TYPE_ROOTNODE);
    if (isnodenotempty($currentcoursenode)) {
        $currentcoursenode->add($pluginname, $url, navigation_node::NODETYPE_LEAF, $pluginname, null, $icon);
    }

    $mycoursesnode = $navigation->find('mycourses', $navigation::TYPE_ROOTNODE);
    if (isnodenotempty($mycoursesnode)) {
        $currentcourseinmycourses = $mycoursesnode->find($coursecontext->instanceid, navigation_node::TYPE_COURSE);
        if ($currentcourseinmycourses) {
            $currentcourseinmycourses->add($pluginname, $url, navigation_node::NODETYPE_LEAF, $pluginname, null, $icon);
        }
    }

    $coursesnode = $navigation->find('courses', $navigation::TYPE_ROOTNODE);
    if (isnodenotempty($coursesnode)) {
        $currentcourseincourses = $coursesnode->find($coursecontext->instanceid, navigation_node::TYPE_COURSE);
        if ($currentcourseincourses) {
            $currentcourseincourses->add($pluginname, $url, navigation_node::NODETYPE_LEAF, $pluginname, null, $icon);
        }
    }
}
/**
 * isNodeNotEmpty.
 *
 * @param navigation_node $node
 * @return boolean
 */
function isnodenotempty(navigation_node $node) {
    return $node !== false && $node->has_children();
}


