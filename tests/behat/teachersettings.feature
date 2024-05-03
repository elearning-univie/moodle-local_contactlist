@local @local_contactlist

Feature: Possibility to disable the contactlist in a course
  As a teacher
  I want to set the contactlist invisible in my course.

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | User | One | teacher1@example.com |
      | student1 | User | Two | user1@example.com |
    And the following "courses" exist:
      | fullname | shortname | format |
      | Course 1 | C1 | topics |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
      | student1 | C1 | student |

  @javascript
  Scenario: Activate or disable the contactlist in a course
    Given I am on the "Course 1" course page logged in as teacher1
    And I am on the "Course 1" "course editing" page
    And I expand all fieldsets
    And the field "id_customfield_conlistcoursevis" matches value "Yes"
    And I log out

    Given I log in as "student1"
    And I am on the "C1" "Course" page
    And I should see "Contactlist"
    And I log out

    Given I log in as "teacher1"
    And I am on the "Course 1" "course editing" page
    And I expand all fieldsets
    And I set the field "Course navigation contactlist visibility" to "No"
    And I press "Save and display"
    And I log out

    Given I log in as "student1"
    And I am on the "C1" "Course" page
    And I should not see "Contactlist"

    Then I am on the "local_contactlist > C1" page
    And I should see "This course does not offer a contact list."
