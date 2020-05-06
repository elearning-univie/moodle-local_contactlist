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

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/local/contactlist/locallib.php');

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

        $courseid = required_param('id', PARAM_INT); // This are required.
        $PAGE->set_url(new \moodle_url('/local/contactlist/studentview.php', ['id' => $courseid]));
        $mform = $this->_form;
        $localvsglobal = "";

        echo '<p>'. $localvsglobal.'</p>';

        $options = array(
            0 => CONTACTLIST_DEFAULT,
            1 => CONTACTLIST_VISIBLE,
            2 => CONTACTLIST_INVISIBLE
        );

        $visib = local_contactlist_courselevel_visibility($USER->id, $courseid);
        $mform->addElement('select', 'visib', get_string('localvisibility', 'local_contactlist'), $options);
        $mform->setType('visib', PARAM_INT);
        $mform->setDefault('visib', $visib);
        $mform->addHelpButton('visib', 'visib', 'local_contactlist');
        $this->add_action_buttons();
    }

}
