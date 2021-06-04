@local @local_contactlist

Feature: Global visibility in contactlist
  As a user
  I want to be invisible in contactlists by default.

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
  Scenario: A user is invisible to other users in the contact list by default.
    Given I log in as "user1"
    And I open my profile in edit mode
    And I click on "Privacy Settings" "text"
    Then the field "id_profile_field_contactlistdd" matches value "No"
    And I click on "Update profile" "button"
    And I log out

    Then I log in as "user2"
    And I am on "Course 1" course homepage
    And I follow "Contactlist"
    Then I should see "You are currently INVISIBLE in this course!"
    And I should not see "user1@example.com"
    And I should not see "user2@example.com"
