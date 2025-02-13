@local @local_contactlist

Feature: Contactlist can be sorted

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email | idnumber | profile_field_contactlistdd |
      | teacher1 | Seb | Vettel | teacher1@example.com | t1 | No |
      | student1 | Annie | Edison | student1@example.com | s1 | Yes |
      | student2 | George | Bradley | student2@example.com | s2 | Yes |
      | student3 | Travis | Sutcliff | student3@example.com | s3 | Yes |
    And the following "courses" exist:
      | fullname | shortname | format |
      | Course 1 | C1 | topics |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
      | student1 | C1 | student |
      | student2 | C1 | student |
      | student3 | C1 | student |

  @javascript
  Scenario: All user names are shown and firstname, lastname and email address are sortable.
    Given I log in as "teacher1"
    And I am on the "local_contactlist > C1" page
    Then the following should exist in the "contactlist" table:
      | First name  | Email address |
      | Annie Edison | student1@example.com |
      | George Bradley | student2@example.com |
      | Travis Sutcliff | student3@example.com |
    And "George Bradley" "table_row" should appear before "Annie Edison" "table_row"
    And "Annie Edison" "table_row" should appear before "Travis Sutcliff" "table_row"
    And I follow "Last name"
    And "Annie Edison" "table_row" should appear before "George Bradley" "table_row"
    And "Travis Sutcliff" "table_row" should appear before "Annie Edison" "table_row"
    And I follow "First name"
    And "Annie Edison" "table_row" should appear after "George Bradley" "table_row"
    And "George Bradley" "table_row" should appear after "Travis Sutcliff" "table_row"
    And I follow "First name"
    And "George Bradley" "table_row" should appear after "Annie Edison" "table_row"
    And "Travis Sutcliff" "table_row" should appear after "George Bradley" "table_row"
    And I follow "Email address"
    And "George Bradley" "table_row" should appear after "Annie Edison" "table_row"
    And "Travis Sutcliff" "table_row" should appear after "George Bradley" "table_row"
    And I follow "Email address"
    And "Annie Edison" "table_row" should appear after "George Bradley" "table_row"
    And "George Bradley" "table_row" should appear after "Travis Sutcliff" "table_row"
