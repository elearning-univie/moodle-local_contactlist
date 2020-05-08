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
 * Interface implementation of the external Webservices
 *
 * @package    local_contactlist
 * @copyright  2020 University of Vienna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/local/contactlist/locallib.php');

/**
 * Class local_contactlist_external
 *
 * @copyright  2020 University of Vienna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_contactlist_external extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function update_settings_parameters() {
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT, 'course id'),
                'updateval' => new external_value(PARAM_INT, 'visibility 0 = hidden, 1 = visible')
            )
        );
    }

    /**
     * Updates the Settings for a User
     *
     * @param int $updateval
     * @return string|null
     * @throws dml_exception
     */
    public static function update_settings($courseid, $updateval) {
        global $USER;

        $params = self::validate_parameters(self::update_settings_parameters(), array('courseid' => $courseid, 'updateval' => $updateval));
        local_contactlist_save_update($USER->id, $params['courseid'], $params['updateval']);

        return $updateval;
    }

    /**
     * Returns return value description
     *
     * @return external_value
     */
    public static function update_settings_returns() {
        return new external_value(PARAM_INT, '0/1');
    }
}
