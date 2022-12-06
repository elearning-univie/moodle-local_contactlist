@local @local_contactlist
Feature: Contactlist user messages
  In order to communicate with other users
  As a user
  I need to be able to send a message via the contactlist

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
  Scenario: Send a message to a student from the contact list
    Given I log in as "teacher1"
    And I am on the "local_contactlist > C1" page
    And I should see "Messages"
    And I click on "//*[starts-with(@id,'message-user-button')]" "xpath_element"
    And I send "Hi!" message in the message area
    And I should see "Hi!" in the "Student 1" "core_message > Message conversation"
    And I should see "##today##%d %B##" in the "Student 1" "core_message > Message conversation"
