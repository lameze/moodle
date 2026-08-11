@mod_quiz @quizaccess @quizaccess_seb
Feature: Force a quiz password on quizzes that require Safe Exam Browser
  In order to stop students sharing a way into an exam
  As an administrator
  I need to be able to make a quiz password compulsory whenever Safe Exam Browser is required

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
    And the following "activities" exist:
      | activity | course | name   | idnumber |
      | quiz     | C1     | Quiz 1 | quiz1    |
    And the following config values are set as admin:
      | quizpasswordrequired | 1 | quizaccess_seb |

  Scenario: A quiz requiring Safe Exam Browser cannot be saved without a quiz password
    Given I am on the "Quiz 1" "quiz activity editing" page logged in as "teacher1"
    And I expand all fieldsets
    When I set the field "seb_requiresafeexambrowser" to "Yes – Configure manually"
    And I press "Save and display"
    Then I should see "Current settings require quizzes using the Safe Exam Browser to have a quiz password set." in the "Require password" "form_row"

  Scenario: A quiz requiring Safe Exam Browser saves once a quiz password is given
    Given I am on the "Quiz 1" "quiz activity editing" page logged in as "teacher1"
    And I expand all fieldsets
    When I set the following fields to these values:
      | seb_requiresafeexambrowser | Yes – Configure manually |
      | quizpassword               | letmein                  |
    And I press "Save and display"
    Then I should not see "Current settings require quizzes using the Safe Exam Browser to have a quiz password set."
    And I should see "Quiz 1"

  Scenario: A quiz that does not require Safe Exam Browser still saves without a quiz password
    Given I am on the "Quiz 1" "quiz activity editing" page logged in as "teacher1"
    And I expand all fieldsets
    When I set the field "seb_requiresafeexambrowser" to "No"
    And I press "Save and display"
    Then I should not see "Current settings require quizzes using the Safe Exam Browser to have a quiz password set."
    And I should see "Quiz 1"
