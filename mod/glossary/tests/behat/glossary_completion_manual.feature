@mod @mod_glossary @core_completion
Feature: Manually complete a glossary
  In order to manually complete glossary requirements
  As a student
  I need to be able to manually complete the glossary completion status

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Vinnie    | Student1 | student1@example.com |
      | teacher1 | Darrell   | Teacher1 | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | enablecompletion | showcompletionconditions |
      | Course 1 | C1        | 1                | 1                        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | student1 | C1     | student        |
      | teacher1 | C1     | editingteacher |
    And the following "activity" exists:
      | activity  | glossary       |
      | course     | C1            |
      | name       | Music history |
      | section    | 1             |
      | completion | 1             |

  Scenario: Verify that a teacher cannot manually mark the glossary activity as done
    When I am on the "Music history" "glossary activity" page logged in as teacher1
    Then the manual completion button for "Music history" should be disabled

  @javascript
  Scenario: Verify that a student can manually complete a glossary activity
    Given I am on the "Music history" "glossary activity" page logged in as student1
    And the manual completion button of "Music history" is displayed as "Mark as done"
    When I toggle the manual completion state of "Music history"
    Then the manual completion button of "Music history" is displayed as "Done"
