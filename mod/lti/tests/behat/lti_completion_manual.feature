@mod @mod_lti @core_completion
Feature: Manual completion of LTI activity
  In order to mark an LTI activity as done
  As a student
  I want to be able to manually toggle the completion state of the activity

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
      | activity | name          | course | idnumber | completion |
      | lti      | Music history | C1     | lti1     | 1          |

  Scenario: Verify that in LTI a teacher cannot manually mark the activity as done
    Given I am on the "Music history" "lti activity" page logged in as teacher1
    Then the manual completion button for "Music history" should be disabled

  @javascript
  Scenario: Verify that students can manually mark the LTI activity as done
    Given I am on the "Music history" "lti activity" page logged in as student1
    And the manual completion button of "Music history" is displayed as "Mark as done"
    When I toggle the manual completion state of "Music history"
    Then the manual completion button of "Music history" is displayed as "Done"
