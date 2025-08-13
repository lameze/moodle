@mod @mod_quiz
Feature: Displaying quiz questions in shuffled order
  In order for questions to appear randomly
  As a teacher
  I need to be able to shuffle the question order

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
    And the following "activities" exist:
      | activity | name   | course | questionsperpage | idnumber |
      | quiz     | Quiz 1 | C1     | 2                | quiz1    |
    And the following "question categories" exist:
      | contextlevel    | reference | name           |
      | Activity module | quiz1     | Test questions |
    And the following "questions" exist:
      | questioncategory | qtype     | name | user     | questiontext         |
      | Test questions   | truefalse | TF1  | teacher1 | This is Question 1   |
      | Test questions   | truefalse | TF2  | teacher1 | This is Question 2   |
      | Test questions   | truefalse | TF3  | teacher1 | This is Question 3   |
      | Test questions   | truefalse | TF4  | teacher1 | This is Question 4   |
      | Test questions   | truefalse | TF5  | teacher1 | This is Question 5   |
    And quiz "Quiz 1" contains the following questions:
      | question | page |
      | TF1      | 1    |
      | TF2      | 1    |
      | TF3      | 2    |
      | TF4      | 2    |
      | TF5      | 3    |

  @javascript
  Scenario: Quiz questions order can be shuffled
    Given I am on the "Quiz 1" "mod_quiz > Edit" page logged in as teacher1
    # Shuffle quiz question order.
    And I set the field "Shuffle" to "1"
    And I am on the "Quiz 1" "quiz activity" page
    # Preview quiz as teacher.
    When I press "Preview quiz"
    And I press "Next page"
    And I press "Next page"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Submit all your answers and finish?" "dialogue"
    Then "This is Question 1" "text" should not exist
    And "This is Question 2" "text" should not exist
    And I press "Next page"
    And "This is Question 3" "text" should not exist
    And "This is Question 4" "text" should not exist
    And I press "Next page"
    And "This is Question 5" "text" should not exist
#    And I am on the "Quiz 1" "quiz activity" page logged in as student1
#    And I press "Attempt quiz"
#    And I press "Next page"
#    And I press "Next page"
#    And I press "Finish attempt ..."
#    And I press "Submit all and finish"
#    And I click on "Submit all and finish" "button" in the "Submit all your answers and finish?" "dialogue"
#    And I click on "Finish review" "link"
#    And I am on the "Quiz 1" "mod_quiz > Edit" page logged in as teacher1
#    And I should see "You cannot add or remove questions because this quiz has attempts."
#    And the "Shuffle" "checkbox" should be disabled
#    And the "Repaginate" "button" should be disabled
#    And the "Select multiple items" "button" should be disabled
