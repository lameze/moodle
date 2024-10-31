@mod @mod_imscp @core_completion
Feature: View activity completion information in the IMS content package activity
  In order to have visibility of IMS content package completion requirements
  As a student
  I need to be able to view my IMS content package completion progress

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Vinnie    | Student1 | student1@example.com |
      | teacher1 | Darrell   | Teacher1 | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category | enablecompletion | showcompletionconditions |
      | Course 1 | C1        | 0        | 1                | 1                        |
    And the following "course enrolments" exist:
      | user | course | role           |
      | student1 | C1 | student        |
      | teacher1 | C1 | editingteacher |
    And the following "activities" exist:
      | activity | course | name          | completion | packagefilepath                            |
      | imscp    | C1     | Music history | 1          | mod/imscp/tests/packages/singescobasic.zip |

  Scenario: Verify that a teacher cannot mark a IMSCP activity as completed
    When I am on the "Music history" "imscp activity" page logged in as teacher1
    Then the manual completion button for "Music history" should be disabled

  @javascript
  Scenario: A student can manually complete a IMSCP activity
    When I am on the "Music history" "imscp activity" page logged in as student1
    Then the manual completion button of "Music history" is displayed as "Mark as done"
    And I toggle the manual completion state of "Music history"
    And the manual completion button of "Music history" is displayed as "Done"
