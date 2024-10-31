@mod @mod_scorm @core_completion
Feature: Manual completion of a Scorm activity
  In order to mark a scorm activity as done
  As a student
  I want to be able to manually toggle the completion state of the activity

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Vinnie    | Student1 | student1@example.com |
      | teacher1 | Darrell   | Teacher1 | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | enablecompletion |
      | Course 1 | C1        | 1                |
    And the following "course enrolments" exist:
      | user | course | role           |
      | student1 | C1 | student        |
      | teacher1 | C1 | editingteacher |
    And the following "activity" exists:
      | activity        | scorm                                                         |
      | course          | C1                                                            |
      | name            | Music history                                                 |
      | completion      | 1                                                             |
      | packagefilepath | mod/scorm/tests/packages/RuntimeMinimumCalls_SCORM12-mini.zip |

  Scenario: Verify that in scorm a teacher cannot manually mark the activity as done
    Given I am on the "Music history" "scorm activity" page logged in as teacher1
    And the manual completion button for "Music history" should be disabled

  @javascript
  Scenario: Verify that students can manually mark the scorm activity as done
    When I am on the "Music history" "scorm activity" page logged in as student1
    Then the manual completion button of "Music history" is displayed as "Mark as done"
    And I toggle the manual completion state of "Music history"
    And the manual completion button of "Music history" is displayed as "Done"
