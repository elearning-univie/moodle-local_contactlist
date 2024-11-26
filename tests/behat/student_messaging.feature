@local @local_contactlist
Feature: Students can use the contactlist to message other students

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email | idnumber | profile_field_contactlistdd |
      | student1 | Student | 1 | student1@example.com | 1 | Yes |
      | student2 | Student | 2 | student2@example.com | 2 | Yes |
    And the following "courses" exist:
      | fullname | shortname | format |
      | Course 1 | C1 | topics |
    And the following "course enrolments" exist:
      | user | course | role |
      | student1 | C1 | student |
      | student2 | C1 | student |

  @javascript
  Scenario: Send a message to a student from the contact list
    When I log in as "student2"
    And I am on "Course 1" course homepage
    And I follow "Contactlist"
    And I click on "Use moodle-wide contactlist visibility setting (default)." "checkbox"
    And I set the following fields to these values:
    | Contact information (name, email, profile picture and chat) visibility in this course | Visible |
    And I click on "Save changes" "button"
    Then I log out
    When I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Contactlist"
    And I follow "Student 2"
    And I click on "Message" "button"
    And I send "Hi!" message in the message area
    And I should see "Hi!" in the "Student 2" "core_message > Message conversation"
    Then I should see "##today##%d %B##" in the "Student 2" "core_message > Message conversation"
