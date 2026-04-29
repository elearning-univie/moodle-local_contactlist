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
 * Admin settings for local_contactlist plugin.
 *
 * @package    local_contactlist
 * @copyright  2020 University of Vienna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$settings = new admin_settingpage(
    'local_contactlist',
    new lang_string('pluginname', 'local_contactlist'),
    'moodle/site:config'
);

if ($ADMIN->fulltree) {
    $settings->add(new \local_contactlist\admin\setting_defaultvisibility(
        'local_contactlist/defaultvisibility',
        get_string('defaultvisibility', 'local_contactlist'),
        get_string('defaultvisibility_desc', 'local_contactlist'),
        '1',
        ['1' => get_string('yes'), '0' => get_string('no')]
    ));
}

$ADMIN->add('localplugins', $settings);
