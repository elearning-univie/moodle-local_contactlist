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
 * Privacy Subsystem implementation for local_contactlist.
 *
 * @package    mod_flashcards
 * @copyright  2020 University of Vienna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_contactlist\privacy;

defined('MOODLE_INTERNAL') || die();

/**
 * The local_contactlist module does store data. WIP.
 *
 * @package       local_contactlist
 * @author        Angela Baier
 * @copyright     2020 University of Vienna
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements \core_privacy\local\metadata\null_provider {
    /**
     * Database info.
     * 
     * @param collection $collection
     * @return collection
     */
    public static function get_metadata(collection $collection) : collection {

        $collection->add_database_table(
            'user_info_data',
            [
                'userid' => 'privacy:metadata:user_info_data:userid',
                'data' => 'privacy:metadata:user_info_data:data',
            ],
            'privacy:metadata:user_info_data'
            );

        $collection->add_database_table(
            'local_contactlist_course_vis',
            [
                'userid' => 'privacy:metadata:local_contactlist_course_vis:userid',
                'courseid' => 'privacy:metadata:local_contactlist_course_vis:courseid',
                'visib' => 'privacy:metadata:local_contactlist_course_vis:visib',
            ],
            'privacy:metadata:local_contactlist_course_vis'
            );
        
        return $collection;
    }
}