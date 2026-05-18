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
 * Custom admin setting for contactlist default visibility.
 *
 * @package    local_contactlist
 * @copyright  2020 University of Vienna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_contactlist\admin;

defined('MOODLE_INTERNAL') || die();

/**
 * Extends admin_setting_configselect to sync the defaultvalue stored in the
 * course custom field configdata when the admin setting is saved.
 */
class setting_defaultvisibility extends \admin_setting_configselect {
    /**
     * Saves the setting value and updates the defaultvalue.
     *
     * @param string $data visible(1) or hidden(0)
     * @return string Empty string on success, error string on failure
     */
    public function write_setting($data) {
        $result = parent::write_setting($data);

        global $DB;

        $field = $DB->get_record('customfield_field', ['shortname' => 'conlistcoursevis']);

        if ($field && $field->configdata) {
            $configdata = json_decode($field->configdata, true);
            $configdata['defaultvalue'] = ($data === '1') ? 'Yes' : 'No';
            $field->configdata = json_encode($configdata);
            $field->timemodified = time();
            $DB->update_record('customfield_field', $field);
        }

        return $result;
    }
}
