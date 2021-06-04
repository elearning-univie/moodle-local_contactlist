@local @local_contactlist

Feature: Global and local visibility in contactlist
  In order to be visible in contactlists by default
  As a user
  I want to be able to switch on global visibility on my course page and switch it off again.

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
  Scenario: A user enables global visibility in the profile settings to be visible and back again.
    Given I log in as "user1"
    And I open my profile in edit mode
    And I click on "Privacy Settings" "text"
    And I set the field "id_profile_field_contactlistdd" to "Yes"
    And I click on "Update profile" "button"
    And I log out

    Then I log in as "user2"
    And I am on "Course 1" course homepage
    And I follow "Contactlist"
    Then I should see "Use moodle-wide contactlist visibility setting"
    And I should see "user1@example.com"
    And I log out

    Then I log in as "user1"
    And I open my profile in edit mode
    And I click on "Privacy Settings" "text"
    And I set the field "id_profile_field_contactlistdd" to "No"
    And I click on "Update profile" "button"
    And I log out

    Then I log in as "user2"
    And I am on "Course 1" course homepage
    And I follow "Contactlist"
    Then I should see "Use moodle-wide contactlist visibility setting"
    And I should not see "user1@example.com"
