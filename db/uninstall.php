<?php
// This file is part of mod_offlinequiz for Moodle - http://moodle.org/
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
 * Uninstall script for local contactlist plugin
 *
 * @package       local_contactlist
 * @author        Angela Baier
 * @copyright     2020 University of Vienna
 * @since         Moodle 3.8+
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 **/

/**
 * Uninstall script for local contactlist plugin
 */
function xmldb_local_contactlist_uninstall() {
    global $DB;

    try {
        $userinfofield = $DB->get_record('user_info_field', ['shortname' => 'contactlistdd']);
        if ($userinfofield) {
            $DB->delete_records('user_info_data', ['fieldid' => $userinfofield->id]);
            $DB->delete_records('user_info_field', ['id' => $userinfofield->id]);
            $DB->delete_records('user_info_category', ['id' => $userinfofield->categoryid]);
        }

        $coursefield = $DB->get_record('customfield_field', ['shortname' => 'conlistcoursevis']);
        if ($coursefield) {
            $DB->delete_records('customfield_data', ['fieldid' => $coursefield->id]);
            $DB->delete_records('customfield_field', ['id' => $coursefield->id]);
            $DB->delete_records('customfield_category', ['id' => $coursefield->categoryid]);
        }
    } catch (\Throwable $e) {
        echo "$e->getMessage()";
    }
}
