@mod @mod_lti @core_completion
Feature: View activity completion information in the LTI activity
  In order to have visibility of LTI completion requirements
  As a student
  I need to be able to view my LTI completion progress

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
      | activity | name          | course | idnumber | completion | completionview | completionusegrade |
      | lti      | Music history | C1     | lti1     | 2          | 1              | 1                  |


  @javascript
  Scenario: Use manual completion
    Given I am on the "Music history" "lti activity editing" page logged in as teacher1
    And I expand all fieldsets
    And I set the field "Students must manually mark the activity as done" to "1"
    And I press "Save and display"
    # Teacher view.
    And the manual completion button for "Music history" should be disabled
    # Student view.
    When I am on the "Music history" "lti activity" page logged in as student1
    Then the manual completion button of "Music history" is displayed as "Mark as done"
    And I toggle the manual completion state of "Music history"
    And the manual completion button of "Music history" is displayed as "Done"
