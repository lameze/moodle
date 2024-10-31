@mod @mod_survey @core_completion
Feature: Manual completion of a Survey activity
  In order to mark a survey activity as done
  As a student
  I want to be able to manually toggle the completion state of the activity

  Background:
    Given I enable "survey" "mod" plugin
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
      | student1 | Student   | 1        | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category | enablecompletion |
      | Course 1 | C1        | 0        | 1                |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
    Given the following "activities" exist:
      | activity   | name                   | course | idnumber    | completion |
      | survey     | Test survey name       | C1     | survey1     | 1          |


  Scenario: Verify that a teacher can see the completion condition of a survey activity
    When I am on the "survey1" Activity page logged in as teacher1
    Then "Test survey name" should have the "Mark as done" completion condition

  @javascript
  Scenario: Verify that a student can manually mark the survey activity as done
    Given I am on the "survey1" Activity page logged in as student1
    And the manual completion button of "Test survey name" is displayed as "Mark as done"
    When I toggle the manual completion state of "Test survey name"
    Then the manual completion button of "Test survey name" is displayed as "Done"
