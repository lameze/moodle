@mod_quiz @quizaccess @quizaccess_seb
Feature: Show or hide blocks around a Safe Exam Browser quiz
  In order to keep the exam screen free of distractions
  As an administrator
  I need to control whether blocks appear before a Safe Exam Browser quiz starts and after it finishes

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | 1        | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | student1 | C1     | student |
    And the following "activities" exist:
      | activity | course | name   | idnumber | seb_requiresafeexambrowser |
      | quiz     | C1     | Quiz 1 | quiz1    | 1                          |
    And the following "question categories" exist:
      | contextlevel    | reference | name           |
      | Activity module | quiz1     | Test questions |
    And the following "questions" exist:
      | questioncategory | qtype     | name    | questiontext       |
      | Test questions   | truefalse | Reading | Can you read this? |
    And quiz "Quiz 1" contains the following questions:
      | question | page |
      | Reading  | 1    |

  Scenario Outline: Blocks are shown before the quiz starts only when displayblocksbeforestart is set
    Given the following "blocks" exist:
      | blockname    | contextlevel    | reference | pagetypepattern | defaultregion |
      | online_users | Activity module | quiz1     | mod-quiz-view   | side-pre      |
    And the following config values are set as admin:
      | displayblocksbeforestart | <displayblocksbeforestart> | quizaccess_seb |
    When I am on the "Quiz 1" "quiz activity" page logged in as student1
    Then I should see "This quiz has been configured so that students may only attempt it using the Safe Exam Browser."
    And "Online users" "block" <blockvisibility> exist

    Examples:
      | displayblocksbeforestart | blockvisibility |
      | 0                        | should not      |
      | 1                        | should          |

  Scenario Outline: Blocks are shown after the quiz finishes only when displayblockswhenfinished is set
    Given the following "blocks" exist:
      | blockname    | contextlevel    | reference | pagetypepattern | defaultregion |
      | online_users | Activity module | quiz1     | mod-quiz-*      | side-pre      |
    And user "student1" has attempted "Quiz 1" with responses:
      | slot | response |
      | 1    | True     |
    And the following config values are set as admin:
      | displayblockswhenfinished | <displayblockswhenfinished> | quizaccess_seb |
    When I am on the "Quiz 1" "quiz activity" page logged in as student1
    Then "Online users" "block" <blockvisibility> exist

    Examples:
      | displayblockswhenfinished | blockvisibility |
      | 0                         | should not      |
      | 1                         | should          |
