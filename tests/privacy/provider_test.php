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
 * Privacy provider tests.
 *
 * @package   local_contactlist
 * @copyright 2022 University of Vienna
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_contactlist\privacy;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/local/contactlist/locallib.php');

use local_contactlist\privacy\provider;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\writer;

/**
 * Privacy provider tests class.
 *
 * @package   local_contactlist
 * @copyright 2022 University of Vienna
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers \local_contactlist\privacy\provider
 */
class provider_test extends \core_privacy\tests\provider_testcase {

    /**
     * Test for provider::get_metadata().
     */
    public function test_get_metadata() {
        $collection = new collection('local_contactlist');
        $newcollection = provider::get_metadata($collection);
        $itemcollection = $newcollection->get_collection();
        $this->assertCount(2, $itemcollection);

        $userinfodatatable = array_shift($itemcollection);
        $this->assertEquals('user_info_data', $userinfodatatable->get_name());

        $contactlistcoursevistable = array_shift($itemcollection);
        $this->assertEquals('local_contactlist_course_vis', $contactlistcoursevistable->get_name());

        $privacyfields = $userinfodatatable->get_privacy_fields();
        $this->assertArrayHasKey('userid', $privacyfields);
        $this->assertArrayHasKey('data', $privacyfields);
        $this->assertEquals('privacy:metadata:user_info_data', $userinfodatatable->get_summary());

        $privacyfields = $contactlistcoursevistable->get_privacy_fields();
        $this->assertArrayHasKey('userid', $privacyfields);
        $this->assertArrayHasKey('courseid', $privacyfields);
        $this->assertArrayHasKey('visib', $privacyfields);
        $this->assertEquals('privacy:metadata:local_contactlist_course_vis', $contactlistcoursevistable->get_summary());
    }

    /**
     * Test for provider::get_contexts_for_userid().
     */
    public function test_get_contexts_for_userid() {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();

        $user = $this->getDataGenerator()->create_user();

        $user2 = $this->getDataGenerator()->create_user(['profile_field_contactlistdd' => 'Yes']);
        $this->getDataGenerator()->enrol_user($user2->id, $course->id, 'student');

        $user3 = $this->getDataGenerator()->create_user(['profile_field_contactlistdd' => 'Yes']);
        $this->getDataGenerator()->enrol_user($user3->id, $course->id, 'student');
        local_contactlist_save_update($user3->id, $course->id, 1, 0);

        $contextlist = provider::get_contexts_for_userid($user->id);
        $contextlist2 = provider::get_contexts_for_userid($user2->id);
        $contextlist3 = provider::get_contexts_for_userid($user3->id);

        $this->assertCount(0, $contextlist);
        $this->assertCount(1, $contextlist2);
        $this->assertCount(2, $contextlist3);
    }

    /**
     * Test for provider::get_users_in_context().
     */
    public function test_get_users_in_context() {
        $this->resetAfterTest();

        $component = 'local_contactlist';

        $course = $this->getDataGenerator()->create_course();
        $coursecontext = \context_course::instance($course->id);

        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'student');
        $userlist = new \core_privacy\local\request\userlist($coursecontext, $component);
        provider::get_users_in_context($userlist);
        $this->assertCount(0, $userlist);

        $user2 = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user2->id, $course->id, 'student');
        local_contactlist_save_update($user2->id, $course->id, 1, 0);
        $userlist = new \core_privacy\local\request\userlist($coursecontext, $component);
        provider::get_users_in_context($userlist);
        $this->assertCount(1, $userlist);
        $this->assertTrue(in_array($user2->id, $userlist->get_userids()));

        $user3 = $this->getDataGenerator()->create_user(['profile_field_contactlistdd' => 'Yes']);
        $this->getDataGenerator()->enrol_user($user3->id, $course->id, 'student');
        $usercontext = \context_user::instance($user3->id);
        $userlist = new \core_privacy\local\request\userlist($usercontext , $component);
        provider::get_users_in_context($userlist);
        $this->assertCount(1, $userlist);
        $this->assertTrue(in_array($user3->id, $userlist->get_userids()));
    }

    /**
     * Test for provider::export_user_data().
     */
    public function test_export_user_data() {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $coursecontext = \context_course::instance($course->id);

        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'student');

        $writer = writer::with_context($coursecontext);
        $this->assertFalse($writer->has_any_data());

        local_contactlist_save_update($user->id, $course->id, 1, 0);

        $approvedlist = new approved_contextlist($user, 'local_contactlist', [$coursecontext->id]);
        provider::export_user_data($approvedlist);

        $courseid = $course->id;
        $this->assertEquals($user->id, $writer->get_data()->$courseid->userid);
    }

    /**
     * Test for provider::delete_data_for_all_users_in_context().
     */
    public function test_delete_data_for_all_users_in_context() {

    }

    /**
     * Test for provider::delete_data_for_user().
     */
    public function test_delete_data_for_user() {

    }
}