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
 * Contactlist form for course level
 *
 * @package    local_contactlist
 * @author     Angela Baier
 * @copyright  2020 University of Vienna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_contactlist\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/local/contactlist/locallib.php');

/**
 * Form to update local course contactlist visibility
 *
 * @package    local_contactlist
 * @author     Angela Baier
 * @copyright  2020 University of Vienna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class contactlist_form extends \moodleform {

    /**
     * Form definition method.
     */
    public function definition() {
        global $PAGE, $USER;

        $courseid = required_param('id', PARAM_INT);

        $PAGE->set_url(new \moodle_url('/local/contactlist/studentview.php', ['id' => $courseid]));
        $mform = $this->_form;

        $options = array(
            1 => get_string('visible', 'local_contactlist'),
            2 => get_string('invisible', 'local_contactlist')
        );

        $visib = local_contactlist_courselevel_visibility($USER->id, $courseid);

        $usedefault = 0;
        $localsetting = 2;
        if (!$visib) {
            $usedefault = 1;
            if ($globalsetting = local_contactlist_get_global_setting($USER->id)) {
                if ($globalsetting->data == "Yes") {
                    $localsetting = 1;
                }
            }
        } else {
            $localsetting = $visib->visib;
        }
        $mform->addElement('advcheckbox', 'usedefault', get_string('globaldefaultsetting', 'local_contactlist',
            ['here' => local_contactlist_get_profile_link($USER->id, $courseid)]), ' ');
        $mform->setDefault('usedefault', $usedefault);

        $mform->addElement('hidden', 'id', $courseid);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('select', 'visib', get_string('localvisibility', 'local_contactlist'), $options);
        $mform->setType('visib', PARAM_INT);
        $mform->setDefault('visib', $localsetting);
        $mform->disabledIf('visib', 'usedefault', 'eq', 1);
        $mform->addHelpButton('visib', 'visib', 'local_contactlist');
        $this->add_action_buttons(false);
    }

}
