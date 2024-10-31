@mod @mod_chat @core_completion
Feature: View activity completion information in the chat activity
  In order to have visibility of chat completion requirements
  As a student
  I need to be able to view my chat completion progress

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Vinnie    | Student1 | student1@example.com |
      | teacher1 | Darrell   | Teacher1 | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category | enablecompletion |
      | Course 1 | C1        | 0        | 1                |
    And the following "course enrolments" exist:
      | user | course | role           |
      | student1 | C1 | student        |
      | teacher1 | C1 | editingteacher |
    And I enable "chat" "mod" plugin
    And the following "activity" exists:
      | activity       | chat          |
      | course         | C1            |
      | name           | Music history |
      | section        | 1             |
      | completion     | 1             |

  Scenario: Chat module displays manual completion button to teachers
    Given I am on the "Course 1" course page logged in as teacher1
    And "Music history" should have the "Mark as done" completion condition

  @javascript
  Scenario: Verify that students can manually complete a chat activity
    Given I am on the "Music history" Activity page logged in as student1
    And the manual completion button of "Music history" is displayed as "Mark as done"
    When I toggle the manual completion state of "Music history"
    Then the manual completion button of "Music history" is displayed as "Done"
