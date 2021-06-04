@local @local_contactlist

Feature: Possibility to disable visibility in contactlist
  As a user
  I want to override my default visibility setting in contactlists of single courses.

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | user1 | User | One | user1@example.com |
      | user2 | User | Two | user2@example.com |
    And the following "courses" exist:
      | fullname | shortname | format |
      | Course 1 | C1 | topics |
    And the following "course enrolments" exist:
      | user | course | role |
      | user1 | C1 | student |
      | user2 | C1 | student |

  @javascript
  Scenario: As a user I want to change my visibility in a contact list, overriding my default settings. Then I want to switch it back.
    Given I log in as "user1"
    And I open my profile in edit mode
    Then the field "id_profile_field_contactlistdd" matches value "No"
    And I am on "Course 1" course homepage
    And I follow "Contactlist"
    And I set the field "id_usedefault" to "0"
    And the field "id_visib" matches value "Invisible"
    And I set the field "id_visib" to "Visible"
    And I click on "id_submitbutton" "button"
    And I log out

    And I log in as "user2"
    And I am on "Course 1" course homepage
    And I follow "Contactlist"
    Then I should see "Use moodle-wide contactlist visibility setting"
    And I should see "user1@example.com"
    And I log out

    Then I log in as "user1"
    And I am on "Course 1" course homepage
    And I follow "Contactlist"
    And the field "id_usedefault" matches value "0"
    And I set the field "id_visib" to "Invisible"
    And I click on "id_submitbutton" "button"
    And I log out

    And I log in as "user2"
    And I am on "Course 1" course homepage
    And I follow "Contactlist"
    Then I should see "Use moodle-wide contactlist visibility setting"
    And I should not see "user1@example.com"
