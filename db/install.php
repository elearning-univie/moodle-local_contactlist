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
 *  * Install script for local contactlist plugin
 *
 * @package       local_contactlist
 * @author        Angela Baier
 * @copyright     2020 University of Vienna
 * @since         Moodle 3.8+
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 **/

defined('MOODLE_INTERNAL') || die();


/**
 * Code run after the contactlist module database tables have been created.
 */
function xmldb_local_contactlist_install() {
    global $DB;

    try {
        $userinfocategory = $DB->get_record('user_info_category', array('name' => 'Privacy Settings'));
        if (!$userinfocategory) {
            $record = new stdClass();
            $record->name         = 'Privacy Settings';
            $id = $DB->insert_record('user_info_category', $record);
        } else {
            $id = $userinfocategory->id;
        }

        $userinfofield = $DB->get_record('user_info_field', array('categoryid' => $id, 'shortname' => 'contactlistdd'));

        if (!$userinfofield) {
            $record = new stdClass();
            $record->shortname    = 'contactlistdd';
            $record->name         = 'Global contact list visibility';
            $record->datatype     = 'menu';
            $record->categoryid   = $id;
            $record->sortorder    = 2;
            $record->required     = 1;
            $record->locked       = 0;
            $record->visible       = 1;
            $record->defaultdata  = 'No';
            $record->param1       = 'Yes
No';
            $DB->insert_record('user_info_field', $record);
        }
    } catch (\Throwable $e) {
        echo "$e->getMessage()";
    }
}