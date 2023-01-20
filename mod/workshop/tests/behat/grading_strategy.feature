@mod @mod_workshop
Feature: Workshop grading strategy selection
  In order to verify that the assessment form is displayed correctly
  As a teacher
  I need to choose one of the four workshop grading strategies

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Mary      | Teacher  | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
    And the following "activities" exist:
      | activity | course | name       |
      | workshop | C1     | Workshop 1 |
    And I am on the "Workshop 1" "workshop activity editing" page logged in as teacher1

    @javascript
  Scenario Outline: Teachers can use different grading strategies
    Given I set the following fields to these values:
      | strategy | <strategy> |
    And I press "Save and display"
    When I click on "Assessment form" "link"
    And I should see "<label>"
      And I pause
    And I set the following fields to these values:
      | id_description__idx_0_editor | <description 0> |
      | id_description__idx_1_editor | <description 1> |
    And I press "Save and preview"
    Then I should see "Assessment form"
    And I should see "<description 0>"
    And I should <seegrade> "Grade for <description 0>"
    And I should see "Comment for <description 0>"
    And I should see "<description 1>"
    And I should <seegrade> "Grade for <description 1>"
    And I should see "Comment for <description 1>"
    And I should see "Overall feedback"
    And I should see "Feedback for the author"
    And I press "Back to editing form"

    Examples:
      | strategy      | label                | description 0 | description 1 | seegrade |
      | accumulative  | Accumulative grading | Aspect 1      | Aspect 2      | see      |
      | comments      | Comments             | Aspect 1      | Aspect 2      | not see  |
      | numerrors     | Number of errors     | Assertion 1   | Assertion 2   | not see  |

  @javascript
  Scenario: Choose rubric as grading strategy
    Given I set the following fields to these values:
      | strategy | rubric |
    And I press "Save and display"
    When I click on "Assessment form" "link"
    And I should see "Rubric"
    And I set the following fields to these values:
      | id_description__idx_0_editor | Criterion1 |
      | definition__idx_0__idy_0     | One zero   |
      | id_description__idx_1_editor | Criterion2 |
      | definition__idx_1__idy_0     | Two zero   |
    And I press "Save and preview"
    Then I should see "Assessment form"
    And I should see "Criterion 1"
    And I should see "Criterion1"
    And I should see "One zero"
    And I should see "Criterion 2"
    And I should see "Criterion2"
    And I should see "Two zero"
    And I should see "Overall feedback"
    And I should see "Feedback for the author"
    And I press "Back to editing form"
    And I should see "Rubric"
