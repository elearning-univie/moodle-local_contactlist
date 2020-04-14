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
 * @package       local
 * @subpackage    contactlist
 * @author        Angela Baier
 * @copyright     2020 University of Vienna
 * @since         Moodle 3.8+
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 **/

defined('MOODLE_INTERNAL') || die();


/**
 * Uninstall script for local contactlist plugin
 */
function xmldb_local_contactlist_uninstall() {
    global $DB;
    
    try {
        $userinfocategory = $DB->get_record('user_info_category', array('name' => 'Privacy Settings'));
        $id = $userinfocategory->id;

        $userinfofield = $DB->get_record('user_info_field',  array('categoryid' => $id));
        $fieldid = $userinfofield->id;

        $DB->delete_records('user_info_data',  array('fieldid' => $fieldid));

        $DB->delete_records('user_info_field',  array('categoryid' => $id));

        $DB->delete_records('user_info_category', array('name' => 'Privacy Settings'));

    } catch (\Throwable $e) {
        echo "$e->getMessage()";
    }

}