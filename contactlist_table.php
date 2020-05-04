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
 * Contains the class used for the displaying the participants table.
 *
 * @package    local_contactlist
 * @author     Angela Baier
 * @copyright  2020 University of Vienna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use context;
use core_user\output\status_field;
use DateTime;

defined('MOODLE_INTERNAL') || die;

global $CFG;

require_once($CFG->libdir . '/tablelib.php');
require_once($CFG->dirroot . '/local/contactlist/locallib.php');

/**
 * Class for the displaying the participants table.
 *
 * @package    local_contactlist
 * @author     Angela Baier
 * @copyright  2020 University of Vienna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class contactlist_table extends \table_sql {

    /**
     * @var int $courseid The course id
     */
    protected $courseid;

    /**
     * @var string $search The string being searched.
     */
    protected $search;

    /**
     * @var string[] Extra fields to display.
     */
    protected $extrafields;

    /**
     * @var \stdClass $course The course details.
     */
    protected $course;

    /**
     * @var  context $context The course context.
     */
    protected $context;

    /**
     * Sets up the table.
     *
     * @param int $courseid
     * @param string|array $search The search string(s)
     */
    public function __construct($courseid, $search) {
        global $CFG, $OUTPUT;

        parent::__construct('user-index-participants-' . $courseid);

        $this->selectall = $selectall;

        // Get the context.
        $this->course = get_course($courseid);
        $context = \context_course::instance($courseid, MUST_EXIST);
        $this->context = $context;

        // Define the headers and columns.
        $headers = [];
        $columns = [];

        $headers[] = get_string('fullname');
        $columns[] = 'fullname';

        $extrafields = get_extra_user_fields($context);
        foreach ($extrafields as $field) {
            $headers[] = get_user_field_name($field);
            $columns[] = $field;
        }

        $this->define_columns($columns);
        $this->define_headers($headers);

        // Make this table sorted by first name by default.
       // $this->sortable(true, 'firstname');

        $this->set_attribute('id', 'contactlist');

        // Set the variables we need to use later.
       // $this->currentgroup = $currentgroup;
//         $this->accesssince = $accesssince;
//         $this->roleid = $roleid;
//         $this->search = $search;
//         $this->enrolid = $enrolid;
//         $this->status = $status;
//         $this->countries = get_string_manager()->get_list_of_countries(true);
         $this->extrafields = $extrafields;
        $this->context = $context;
    }

    /**
     * Render the participants table.
     *
     * @param int $pagesize Size of page for paginated displayed table.
     * @param bool $useinitialsbar Whether to use the initials bar which will only be used if there is a fullname column defined.
     * @param string $downloadhelpbutton
     */
    public function out($pagesize, $useinitialsbar, $downloadhelpbutton = '') {
        global $PAGE;

        parent::out($pagesize, $useinitialsbar, $downloadhelpbutton);

//         if (has_capability('moodle/course:enrolreview', $this->context)) {
//             $params = ['contextid' => $this->context->id, 'courseid' => $this->course->id];
//             $PAGE->requires->js_call_amd('core_user/status_field', 'init', [$params]);
//         }
    }

    /**
     * Generate the fullname column.
     *
     * @param \stdClass $data
     * @return string
     */
    public function col_fullname($data) {
        global $OUTPUT;

        return $OUTPUT->user_picture($data, array('size' => 35, 'courseid' => $this->course->id, 'includefullname' => true));
    }

    /**
     * This function is used for the extra user fields.
     *
     * These are being dynamically added to the table so there are no functions 'col_<userfieldname>' as
     * the list has the potential to increase in the future and we don't want to have to remember to add
     * a new method to this class. We also don't want to pollute this class with unnecessary methods.
     *
     * @param string $colname The column name
     * @param \stdClass $data
     * @return string
     */
    public function other_cols($colname, $data) {
        // Do not process if it is not a part of the extra fields.
        if (!in_array($colname, $this->extrafields)) {
            return '';
        }
        return s($data->{$colname});
     }

      /**
      * Query the database for results to display in the table.
      *
      * @param int $pagesize size of page for paginated displayed table.
      * @param bool $useinitialsbar do you want to use the initials bar.
      */
      public function query_db($pagesize, $useinitialsbar = true) {
        global $USER;
        list($twhere, $tparams) = $this->get_sql_where();

        $sort = $this->get_sql_sort();
        if ($sort) {
            $sort = 'ORDER BY ' . $sort;
        }

        $rawdata = local_contactlist_get_list($this->course->id, $twhere, $tparams);
        $this->rawdata = [];

        foreach ($rawdata as $user) {
            $this->rawdata[$user->id] = $user;
        }
        $rawdata->close();

        if ($this->rawdata) {
            $this->allroleassignments = get_users_roles($this->context, array_keys($this->rawdata),
                    true, 'c.contextlevel DESC, r.sortorder ASC');
        } else {
            $this->allroleassignments = [];
        }

        // Set initial bars.
        if ($useinitialsbar) {
            $this->initialbars(true);
        }
    }

    /**
     * Override the table show_hide_link to not show for select column.
     *
     * @param string $column the column name, index into various names.
     * @param int $index numerical index of the column.
     * @return string HTML fragment.
     */
    protected function show_hide_link($column, $index) {
        if ($index > 0) {
            return parent::show_hide_link($column, $index);
        }
        return '';
    }
}

