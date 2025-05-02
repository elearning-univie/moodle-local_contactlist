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
 * @copyright  2020 University of Vienna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

global $CFG;

require_once($CFG->libdir . '/tablelib.php');
require_once(__DIR__ . '/locallib.php');

/**
 * Class for the displaying the participants table.
 *
 * @package    local_contactlist
 * @copyright  2020 University of Vienna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class contactlist_table extends \table_sql {

    /**
     * @var int $courseid The course id
     */
    protected $courseid;

    /**
     * @var string[] Extra fields to display.
     */
    protected $extrafields;

    /**
     * @var  context $context The course context.
     */
    protected $context;

    /**
     * Sets up the table.
     *
     * @param int $courseid
     */
    public function __construct($courseid) {
        parent::__construct('user-index-participants-' . $courseid);
        global $CFG;

        // Define the headers and columns.
        $headers = [];
        $columns = [];
        $extrafields = [];

        // Get the context.
        $this->courseid = $courseid;
        $context = \context_course::instance($courseid, MUST_EXIST);
        $this->context = $context;

        $headers[] = get_string('fullname');
        $columns[] = 'fullname';

        $headers[] = \core_user\fields::get_display_name('email');
        $columns[] = 'email';
        $extrafields[] = 'email';
        
        if (!empty($CFG->messaging)) {
            $headers[] = get_string('chat', 'local_contactlist');
            $columns[] = 'chat';
            $extrafields[] = 'chat';

            $this->column_class('chat', 'contactlist_studentview_cc');
            $this->no_sorting('chat');
        }



        $this->define_columns($columns);
        $this->define_headers($headers);
        $this->set_attribute('id', 'contactlist');
        $this->extrafields = $extrafields;

        $this->column_class('fullname', 'contactlist_studentview_fnc');

        $this->sortable(true, 'lastname');
    }

    /**
     * Generate the fullname column.
     *
     * @param \stdClass $data
     * @return string
     */
    public function col_fullname($data) {
        global $OUTPUT;

        return $OUTPUT->user_picture($data, ['size' => 35, 'courseid' => $this->courseid, 'includefullname' => true]);
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

        if ($colname == 'chat') {
            return local_contactlist_get_chat_html($data->{$colname});
        }
        if ($colname == 'email') {
            $emaillink = '<a href="mailto:' . s($data->{$colname}) . '">' . s($data->{$colname}) . '</a>';
            return $emaillink;
        }
    }

      /**
       * Query the database for results to display in the table.
       *
       * @param int $pagesize size of page for paginated displayed table.
       * @param bool $useinitialsbar do you want to use the initials bar.
       */
    public function query_db($pagesize, $useinitialsbar = true) {
        list($twhere, $tparams) = $this->get_sql_where();

        $total = local_contactlist_get_total_visible($this->courseid);

        $this->pagesize($pagesize, $total);

        $sort = $this->get_sql_sort();
        if ($sort) {
            $sort = 'ORDER BY ' . $sort;
        }

        $rawdata = local_contactlist_get_list(
            $this->courseid, $twhere, $tparams, $sort, $this->get_page_start(), $this->get_page_size());
        $this->rawdata = [];

        foreach ($rawdata as $user) {
            $this->rawdata[$user->id] = $user;
        }
        $rawdata->close();

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
        if ($index > 2) {
            return parent::show_hide_link($column, $index);
        }
        return '';
    }
}

