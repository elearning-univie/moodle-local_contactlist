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
 * Upgrade code for the contactlist plugin
 *
 * @package   local_contactlist
 * @copyright 2022 University of Vienna
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade the contactlist plugin.
 *
 * @param int $oldversion The old version of the contactlist plugin
 * @return bool
 */
function xmldb_local_contactlist_upgrade($oldversion) {
    global $CFG, $DB;

    require_once($CFG->libdir . '/db/upgradelib.php');
    $dbman = $DB->get_manager();

    if ($oldversion < 2022120100) {
        $table = new xmldb_table('local_contactlist');

        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }

        upgrade_plugin_savepoint(true, 2022120100, 'local', 'contactlist');
    }

    if ($oldversion < 2023101900) {
        $table = new xmldb_table('customfield_field');
        $field = new xmldb_field('description');

        if ($dbman->field_exists($table, $field)) {
            $customfieldfield = $DB->get_record('customfield_field', array('shortname' => 'conlistcoursevis'));
            $customfieldfield->description = get_string('customcoursefieldlabel', 'local_contactlist');
            $DB->update_record('customfield_field', $customfieldfield);
        }

        upgrade_plugin_savepoint(true, 2023101900, 'local', 'contactlist');
    }

    return true;
}