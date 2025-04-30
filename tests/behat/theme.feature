@local @local_contactlist
Feature: Testing the plugin with different themes

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
  Scenario: Verifying the plugin displays correctly in the current subtheme
    When I log in as "student2"
    And I am on "Course 1" course homepage
    And I navigate to "Contactlist" in current page administration
    And I click on "Use moodle-wide contactlist visibility setting (default)." "checkbox"
    And I set the following fields to these values:
    | Contact information (name, email, profile picture and chat) visibility in this course | Visible |
    And I click on "Save changes" "button"
    Then I log out
    When I log in as "student1"
    And I am on "Course 1" course homepage
    And I navigate to "Contactlist" in current page administration
    And I click on "Save changes" "button"
    Then I should see "Use moodle-wide contactlist visibility setting (default)."
    And I should see "Contact information (name, email, profile picture and chat) visibility in this course"
    When I follow "Student 2"
    And I click on "Message" "button"
    And I send "Hi!" message in the message area
    And I should see "Hi!" in the "Student 2" "core_message > Message conversation"
    Then I should see "##today##%d %B##" in the "Student 2" "core_message > Message conversation"
    When I click on "conversation-actions-menu-button" "button"
    Then I should see "Mute"
    And I should see "Block user"
    And I should see "Add to contacts"
    And I should see "User info"
    Then I log out
    When I log in as "student1"
    And I set the theme to "classic"
    And I wait to be redirected
    And I am on "Course 1" course homepage
    And I navigate to "Contactlist" in current page administration
    And I click on "Save changes" "button"
    Then I should see "Use moodle-wide contactlist visibility setting (default)."
    And I should see "Contact information (name, email, profile picture and chat) visibility in this course"
    And I follow "Student 2"
    And I click on "Message" "button"
    When I click on "conversation-actions-menu-button" "button"
    Then I should see "Mute"
    And I should see "Block user"
    And I should see "Add to contacts"
    And I should see "User info"
    And I should see "Hi!" in the "Student 2" "core_message > Message conversation"
    And I should see "##today##%d %B##" in the "Student 2" "core_message > Message conversation"
    When I send "Hello!" message in the message area
    Then I should see "Hello!" in the "Student 2" "core_message > Message conversation"
    And I should see "##today##%d %B##" in the "Student 2" "core_message > Message conversation"
    Then I log out
    When I log in as "student1"
    And I set the theme to "boost"
    And I wait to be redirected
    And I am on "Course 1" course homepage
    And I navigate to "Contactlist" in current page administration
    And I should see "Use moodle-wide contactlist visibility setting (default)."
    And I should see "Contact information (name, email, profile picture and chat) visibility in this course"
    And I click on "Save changes" "button"
    And I follow "Student 2"
    And I click on "Message" "button"
    When I click on "conversation-actions-menu-button" "button"
    Then I should see "Mute"
    And I should see "Block user"
    And I should see "Add to contacts"
    And I should see "User info"
    And I should see "Hi!" in the "Student 2" "core_message > Message conversation"
    And I should see "##today##%d %B##" in the "Student 2" "core_message > Message conversation"
    And I should see "Hello!" in the "Student 2" "core_message > Message conversation"
    And I should see "##today##%d %B##" in the "Student 2" "core_message > Message conversation"
    When I send "Servus!" message in the message area
    Then I should see "Servus!" in the "Student 2" "core_message > Message conversation"
    And I should see "##today##%d %B##" in the "Student 2" "core_message > Message conversation"
