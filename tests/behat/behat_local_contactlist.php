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
 * Behat custom functions for local_contactlist.
 *
 * @package   local_contactlist
 * @category  test
 * @copyright 2022 University of Vienna
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

/**
 * Behat helper functions for local_contactlist.
 *
 * @package   local_contactlist
 * @copyright 2022 University of Vienna
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_local_contactlist extends behat_base {

    /**
     * Convert page names to URLs for steps like 'When I am on the "[page name]" page'.
     *
     * @param string $name Course name.
     * @return moodle_url the corresponding URL.
     */
    protected function resolve_page_url(string $name): moodle_url {
        $courseid = $this->get_course_id($name);
        return new moodle_url('/local/contactlist/studentview.php', ['id' => $courseid]);
    }

    /**
     * Get course id from its identifier (shortname or fullname or idnumber)
     *
     * @param string $identifier
     * @return int
     */
    protected function get_course_id(string $identifier): int {
        global $DB;

        return $DB->get_field_select(
            'course',
            'id',
            "shortname = :shortname OR fullname = :fullname OR idnumber = :idnumber",
            [
                'shortname' => $identifier,
                'fullname' => $identifier,
                'idnumber' => $identifier,
            ],
            MUST_EXIST
        );
    }

    /**
     * Sets the theme by appending theme to the current URL and verifies the page loads.
     *
     * @When I set the theme to :theme
     * 
     * @param string $theme The theme to set "boost" or "classic"
     * @throws moodle_exception If the theme cannot be changed
     */
    public function i_set_the_theme_to($theme) {
        $session = $this->getSession();
        $driver = $session->getDriver();

        $current_url = $session->getCurrentUrl();
        $new_url = $current_url . (strpos($current_url, '?') === false ? '?' : '&') . "theme=$theme";
        
        $driver->visit($new_url);
        $session->wait(5000, "document.readyState === 'complete'");
        
        $loaded_url = $session->getCurrentUrl();
        if (strpos($loaded_url, "theme=$theme") === false) {
            throw new \moodle_exception(get_string('theme_not_applied', 'block_usearch', (object)[
                'expected' => "theme=$theme",
                'actual' => $loaded_url,
            ]));
        }
    }
}
