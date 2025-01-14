@local @local_contactlist
Feature: Manage visibility of contact list as an administrator

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email | idnumber | profile_field_contactlistdd |
      | teacher1 | Teacher | 1 | teacher1@example.com | 1 | No |
      | student1 | Student | 1 | student1@example.com | 2 | Yes |
    And the following "courses" exist:
      | fullname | shortname | format |
      | Course 1 | C1 | topics |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
      | student1 | C1 | student |

  @javascript
  Scenario: Granting the view capability to students
    Given I log in as "admin"
    And I am on "Course 1" course homepage
    And I follow "Settings"
    And I click on "collapseElement-8" "button"
    And I set the following fields to these values:
    |  Course navigation contactlist visibility  | No |
    And I click on "Save and display" "button"
    And I log out
    When I log in as "student1"
    And I am on "Course 1" course homepage
    Then I should not see "Contactlist"
    And I log out
    When I log in as "admin"
    And I am on "Course 1" course homepage
    And I follow "Settings"
    And I click on "collapseElement-8" "button"
    And I set the following fields to these values:
    | Course navigation contactlist visibility  | Yes |
    And I click on "Save and display" "button"
    And I log out
    When I log in as "student1"
    And I am on "Course 1" course homepage
    Then I should see "Contactlist"

