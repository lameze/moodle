@mod @mod_lesson @core_completion
Feature: Manual completion of a lesson activity
  In order to mark a lesson activity as done
  As a student
  I want to be able to manually toggle the completion state of the activity

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Vinnie    | Student1 | student1@example.com |
      | teacher1 | Darrell   | Teacher1 | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | enablecompletion | showcompletionconditions |
      | Course 1 | C1        | 1                | 1                        |
    And the following "course enrolments" exist:
      | user | course | role           |
      | student1 | C1 | student        |
      | teacher1 | C1 | editingteacher |
    And the following "activity" exists:
      | activity                   | lesson        |
      | course                     | C1            |
      | idnumber                   | mh1           |
      | name                       | Music history |
      | section                    | 1             |
      | completion                 | 1             |
    And the following "mod_lesson > pages" exist:
      | lesson        | qtype   | title                | content                                |
      | Music history | content | Music history part 1 |                                        |
      | Music history | essay   | Music essay          | Write a really interesting music essay |
    And the following "mod_lesson > answers" exist:
      | page                 | answer                      | jumpto    | score |
      | Music history part 1 | The history of music part 1 | Next page | 0     |
      | Music essay          |                             | Next page | 1     |

  Scenario: Verify that a teacher cannot manually mark the lesson activity as done
    When I am on the "Music history" "lesson activity" page logged in as teacher1
    Then the manual completion button for "Music history" should be disabled

  @javascript
  Scenario: Verify that students can manually mark the lesson activity as done
    Given I am on the "Music history" "lesson activity" page logged in as student1
    And the manual completion button of "Music history" is displayed as "Mark as done"
    When I toggle the manual completion state of "Music history"
    Then the manual completion button of "Music history" is displayed as "Done"
