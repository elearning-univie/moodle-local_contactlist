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

namespace local_contactlist\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

define('CONTACTLIST_DEFAULT', get_string('globaldefault', 'local_contactlist'));
define('CONTACTLIST_VISIBLE', get_string('visible', 'local_contactlist'));
define('CONTACTLIST_INVISIBLE', get_string('invisible', 'local_contactlist'));

/**
 * Form to update local course contactlist visibility
 *
 * @package       local_contactlist
 * @author        Angela Baier
 * @copyright     2020 University of Vienna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class contactlist_form extends \moodleform {

    /**
     * Form definition method.
     */
    public function definition() {
        global $PAGE;

        $courseid     = optional_param('id', 0, PARAM_INT); // This are required.

        $PAGE->set_url('/local/contactlist/studentview.php', array(
            'id' => $courseid));
        $mform = $this->_form;

        $localvsglobal = get_string('personalvisibilityinfo', 'local_contactlist');

        echo '<p>'. $localvsglobal.'</p>';
       
        $options = array(
            0 => CONTACTLIST_DEFAULT,
            1 => CONTACTLIST_VISIBLE,
            2 => CONTACTLIST_INVISIBLE
        );

        $mform->addElement('select', 'localvisibility', get_string('localvisibility', 'local_contactlist'), $options);
        $mform->setType('localvisibility', PARAM_INT);
        $mform->addHelpButton('localvisibility', 'localvisibility','local_contactlist');
        $this->add_action_buttons();
    }

}
