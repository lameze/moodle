@mod @mod_assign @core_completion
Feature: Automatic completion of assignment activity
  In order to have visibility of assignment completion requirements
  As a student
  I need to be able to view my assignment completion progress

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Vinnie    | Student1 | student1@example.com |
      | teacher1 | Darrell   | Teacher1 | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category | enablecompletion | showcompletionconditions |
      | Course 1 | C1        | 0        | 1                | 1                        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | student1 | C1     | student        |
      | teacher1 | C1     | editingteacher |
    And the following "activity" exists:
      | activity                 | assign        |
      | course                   | C1            |
      | idnumber                 | mh1           |
      | name                     | Music history |
      | section                  | 1             |
      | completion               | 1             |
      | grade[modgrade_type]     | point         |
      | grade[modgrade_point]    | 100           |

  @javascript
  Scenario: Verify that students can complete an assignment activity by achieving a passing grade
    Given I am on the "Music history" "assign activity editing" page logged in as teacher1
    And I expand all fieldsets
    And I set the following fields to these values:
    | assignsubmission_onlinetext_enabled | 1                                                 |
    | Add requirements         | 1                  |
    | View the activity                   | 1                                                 |
    | completionusegrade                  | 1                                                 |
    | completionsubmit                    | 1                                                 |
    And I press "Save and display"
    And I log out
    And I am on the "Music history" "assign activity" page logged in as student1
    And the "View" completion condition of "Music history" is displayed as "done"
    And the "Make a submission" completion condition of "Music history" is displayed as "todo"
    And the "Receive a grade" completion condition of "Music history" is displayed as "todo"
    And I am on the "Music history" "assign activity" page
    And I press "Add submission"
    And I set the field "Online text" to "History of playing with drumsticks reversed"
    And I press "Save changes"
    And I press "Submit assignment"
    And I press "Continue"
    And the "View" completion condition of "Music history" is displayed as "done"
    And the "Make a submission" completion condition of "Music history" is displayed as "done"
    And the "Receive a grade" completion condition of "Music history" is displayed as "todo"
    And I log out
    And I am on the "Music history" "assign activity" page logged in as teacher1
    And I go to "Vinnie Student1" "Music history" activity advanced grading page
    And I set the field "Grade out of 100" to "33"
    And I set the field "Notify student" to "0"
    And I press "Save changes"
    And I follow "View all submissions"
    And I log out
    When I am on the "Music history" "assign activity" page logged in as student1
    Then the "View" completion condition of "Music history" is displayed as "done"
    And the "Make a submission" completion condition of "Music history" is displayed as "done"
    And the "Receive a grade" completion condition of "Music history" is displayed as "done"

  @javascript
  Scenario: Automatic completion items should reset when a new attempt is manually given.
  Given I am on the "Music history 2" "assign activity" page logged in as student1
    And the "Make a submission" completion condition of "Music history 2" is displayed as "todo"
    And I press "Add submission"
    And I set the field "Online text" to "History of playing with drumsticks reversed"
    And I press "Save changes"
    And I press "Submit assignment"
    And I press "Continue"
    And the "Make a submission" completion condition of "Music history 2" is displayed as "done"
    And I log out
    And I am on the "Music history 2" "assign activity" page logged in as teacher1
    And I go to "Vinnie Student1" "Music history 2" activity advanced grading page
    And I set the field "Grade out of 100" to "33"
    And I set the field "Notify student" to "0"
    And I set the field "Allow another attempt" to "Yes"
    And I press "Save changes"
    And I log out
    When I am on the "Music history 2" "assign activity" page logged in as student1
    And I should see "Reopened"
    And "Add a new attempt based on previous submission" "button" should exist
    Then the "Make a submission" completion condition of "Music history 2" is displayed as "todo"
