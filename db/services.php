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
 * External service definition
 *
 * @package    local_contactlist
 * @copyright  2020 University of Vienna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

$services = [
    'localcontactlist' => [
        'functions' => ['localcontactlist_update_settings'],
        'shortname' => 'localcontactlist',
        'requiredcapability' => 'local/contactlist:view',
        'restrictedusers' => 0,
        'enabled' => 1,
    ],
];

$functions = [
    'localcontactlist_update_settings' => [
        'classname' => 'local_contactlist_external',
        'methodname' => 'update_settings',
        'classpath' => 'local/contactlist/externallib.php',
        'description' => 'update_settings',
        'type' => 'write',
        'ajax' => true,
        'loginrequired' => true,
    ],
];
