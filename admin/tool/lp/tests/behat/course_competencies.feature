@report @tool_lp
Feature: See the competencies for an activity on the course competencies page.
  As a student
  In order to see only the competencies for an activity in the course competencies page.

  Background:
    Given the following lp "frameworks" exist:
      | shortname | idnumber |
      | Test-Framework | ID-FW1 |
    And the following lp "competencies" exist:
      | shortname | framework | idnumber   |
      | Test-Comp1 | ID-FW1   | Test-Comp1 |
      | Test-Comp2 | ID-FW1   | Test-Comp2 |
    Given the following "courses" exist:
      | shortname | fullname   | enablecompletion |
      | C1        | Course 1   | 1                |
    And the following "users" exist:
      | username | firstname | lastname | email |
      | student1 | Student | 1 | student1@example.com |
    And the following "course enrolments" exist:
      | user | course | role |
      | student1 | C1 | student |
    And the following "activities" exist:
      | activity | name       | intro      | course | idnumber | completion | completionview |
      | page     | PageName1  | PageDesc1  | C1     | PAGE1    | 1          | 1              |
      | page     | PageName2  | PageDesc2  | C1     | PAGE2    | 1          | 1              |
    And the following "core_competency > course_competencies" exist:
      | course | competency |
      | C1     | Test-Comp1 |
      | C1     | Test-Comp2 |
    And the following "core_competency > course_module_competencies" exist:
      | activity | competency |
      | PAGE1    | Test-Comp1 |

  @javascript
  Scenario: Go to the competency course competencies page.
    Given I am on the "Course 1" course page logged in as student1
    When I follow "Competencies"
    Then I should see "Test-Comp1"
    And I should see "Test-Comp2"
    And I wait until "Filter competencies by resource or activity" "field" exists
    And I set the field "Filter competencies by resource or activity" to "PageName1"
    And I press the enter key
    And I should see "Test-Comp1"
    And I should not see "Test-Comp2"
    And I wait until "Filter competencies by resource or activity" "field" exists
    And I set the field "Filter competencies by resource or activity" to "PageName2"
    And I press the enter key
    And I should not see "Test-Comp1"
    And I should not see "Test-Comp2"
    And I should see "No competencies have been linked to this activity or resource."

  Scenario: None course competencies page.
    When I am on the PageName1 "page activity" page logged in as student1
    Then I should see "Test page content"
