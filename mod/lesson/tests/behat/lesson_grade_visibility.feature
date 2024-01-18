@mod @mod_lesson @javascript
Feature: Grade info visibility can be set in lesson
  In order to display grade info
  As a teacher
  I need to set grade type setting

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
      | student1 | Student   | 1        | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
    Given the following "activities" exist:
      | activity | name     | course | grade[modgrade_type] |
      | lesson   | Lesson 1 | C1     | none                 |
    And the following "mod_lesson > pages" exist:
      | lesson | qtype     | title    | content          |
      | Lesson 1   | truefalse | T or F 1 | T or F 1 content |
    And the following "mod_lesson > answers" exist:
      | page     | answer | response | jumpto    | score |
      | T or F 1 | True   | True     | Next page | 1     |

  Scenario: Grade info visibility is set in lesson
    Given I am on the "Lesson 1" "lesson activity" page logged in as teacher1
    And I pause
    And I set the following fields to these values:
      | FALSE | 1 |
    And I press "Submit"
    # Confirm grade visibility
    Then I should see "Your current grade is 0.0 out of 100"

#    Examples:
#      | gradetype | gradevisibility |
#      | none      | should not      |
#      | point     | should          |

  @javascript
  Scenario: Lesson grade can be viewed by student
    Given I am on the "Lesson 1" "lesson activity" page logged in as student1
    # Answer the question correctly
    And I set the following fields to these values:
      | True | 1 |
    When I press "Submit"
    And I press "Continue"
    # Confirm that "View grades" link exists
    Then "View grades" "link" should exist
    And I click on "View grades" "link"
    # Confirm that "View grades" link is clickable
    And "Lesson 1" row "Grade" column of "generaltable" table should contain "100.00"
