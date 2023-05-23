@mod @mod_feedback
Feature: Anonymous feedback
  In order to collect feedbacks
  As an admin
  I need to be able to allow anonymous feedbacks

  Background:
    Given the following "users" exist:
      | username | firstname | lastname |
      | user1    | Username  | 1        |
      | user2    | Username  | 2        |
      | teacher  | Teacher   | 3        |
      | manager  | Manager   | 4        |
    And the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
    And the following "course enrolments" exist:
      | user  | course | role    |
      | user1 | C1     | student |
      | user2 | C1     | student |
      | teacher | C1   | editingteacher |
    And the following "system role assigns" exist:
      | user    | course               | role    |
      | manager | Acceptance test site | manager |
    And the following "activities" exist:
      | activity   | name            | course               | idnumber  | anonymous | publish_stats | section |
      | feedback   | Site feedback   | Acceptance test site | feedback0 | 1         | 1             | 1       |
      | feedback   | Course feedback | C1                   | feedback1 | 1         | 1             | 0       |
    And the following "mod_feedback > question" exists:
      | activity        | feedback0             |
      | name            | Do you like our site? |
      | questiontype    | multichoice           |
      | label           | multichoice2          |
      | subtype         | r                     |
      | hidenoselect    | 1                     |
      | ignoreempty     | 0                     |
      | values          | Yes\nNo\nI don't know |
      | position        | 1                     |

  Scenario: Guests can see anonymous feedback on front page but can not complete
    When I am on the "Site feedback" "feedback activity" page
    Then I should not see "Answer the questions"
    And I should not see "Preview questions"

  Scenario: Complete anonymous feedback on the front page as an authenticated user
    When I am on the "Site feedback" "feedback activity" page logged in as "user1"
    And I should not see "Preview questions"
    And I follow "Answer the questions"
    And I should see "Do you like our site?"
    And I set the following fields to these values:
      | Yes | 1 |
    And I press "Submit your answers"
    And I should not see "Analysis"
    And I press "Continue"

  @javascript
  Scenario: Complete anonymous feedback and view analysis on the front page as an authenticated user
    Given the following "mod_feedback > responses" exist:
      | activity  | user  | Do you like our site? |
      | feedback0 | user1 | Yes                   |
      | feedback0 | user2 | No                    |
    And I log in as "admin"
    And I set the following system permissions of "Authenticated user on site home" role:
      | capability                   | permission |
      | mod/feedback:viewanalysepage | Allow      |
    And I am on the "Site feedback" "feedback activity" page logged in as "user2"
    And I follow "Analysis"
    And I should see "Submitted answers: 2"
    And I should see "Questions: 1"
    # And I should not see "multichoice2" # TODO MDL-29303 do not show labels to users who can not edit feedback
    And I show chart data for the "multichoice2" feedback
    And I should see "Do you like our site?"
    And I should see "1 (50.00 %)" in the "Yes" "table_row"
    And I should see "1 (50.00 %)" in the "No" "table_row"
    And I am on the "Site feedback" "feedback activity" page logged in as "manager"
    And I navigate to "Responses" in current page administration
    And I should not see "Username"
    And I should see "Anonymous entries (2)"
    And I follow "Response number: 1"
    And I should not see "Username"
    And I should see "Response number: 1 (Anonymous)"

  Scenario: Complete fully anonymous feedback on the front page as a guest
    Given the following config values are set as admin:
      | feedback_allowfullanonymous | 1 |
    And I am on site homepage
    When I follow "Site feedback"
    And I should not see "Preview questions"
    And I follow "Answer the questions"
    And I should see "Do you like our site?"
    And I set the following fields to these values:
      | Yes | 1 |
    And I press "Submit your answers"
    And I should not see "Analysis"
    And I press "Continue"

  @javascript
  Scenario: Complete fully anonymous feedback and view analyze on the front page as a guest
    Given the following config values are set as admin:
      | feedback_allowfullanonymous | 1 |
    And I log in as "admin"
    And I set the following system permissions of "Guest" role:
      | capability                   | permission |
      | mod/feedback:viewanalysepage | Allow      |
    And I log out
    When I follow "Site feedback"
    And I should not see "Preview questions"
    And I follow "Answer the questions"
    And I should see "Do you like our site?"
    And I set the following fields to these values:
      | Yes | 1 |
    And I press "Submit your answers"
    And I press "Continue"
    # Starting new feedback
    When I follow "Site feedback"
    And I should not see "Preview questions"
    And I follow "Answer the questions"
    And I should see "Do you like our site?"
    And I set the following fields to these values:
      | No | 1 |
    And I press "Submit your answers"
    And I follow "Analysis"
    And I should see "Submitted answers: 2"
    And I should see "Questions: 1"
    # And I should not see "multichoice2" # TODO MDL-29303
    And I show chart data for the "multichoice2" feedback
    And I should see "Do you like our site?"
    And I should see "1 (50.00 %)" in the "Yes" "table_row"
    And I should see "1 (50.00 %)" in the "No" "table_row"
    And I am on the "Site feedback" "feedback activity" page logged in as "manager"
    And I navigate to "Responses" in current page administration
    And I should see "Anonymous entries (2)"
    And I follow "Response number: 1"
    And I should see "Response number: 1 (Anonymous)"

  @javascript
  Scenario: Anonymous feedback in a course
    Given the following "mod_feedback > question" exists:
      | activity        | feedback1             |
      | name            | Do you like this course? |
      | questiontype    | multichoice           |
      | label           | multichoice1          |
      | subtype         | r                     |
      | hidenoselect    | 1                     |
      | values          | Yes\nNo\nI don't know |
    And the following "mod_feedback > responses" exist:
      | activity  | user  | Do you like this course? |
      | feedback1 | user1 | Yes                      |
      | feedback1 | user2 | No                       |
    And I am on the "Course feedback" "feedback activity" page logged in as user2
    And I should not see "Preview questions"
    And I follow "Analysis"
    And I should see "Submitted answers: 2"
    And I should see "Questions: 1"
    # And I should not see "multichoice2" # TODO MDL-29303
    And I show chart data for the "multichoice1" feedback
    And I should see "Do you like this course?"
    And I should see "1 (50.00 %)" in the "Yes" "table_row"
    And I should see "1 (50.00 %)" in the "No" "table_row"
#    And I log out
    And I am on the "Course feedback" "feedback activity" page logged in as teacher
    And I follow "Preview"
    And I should see "Do you like this course?"
    And I press "Continue"
    And I should not see "Answer the questions"
    And I navigate to "Responses" in current page administration
    And I should not see "Username"
    And I should see "Anonymous entries (2)"
    And I follow "Response number: 1"
    And I should not see "Username"
    And I should see "Response number: 1 (Anonymous)"
    And I should not see "Prev"
    And I follow "Next"
    And I should see "Response number: 2 (Anonymous)"
    And I should see "Prev"
    And I should not see "Next"
    And I click on "Back" "link" in the "[role=main]" "css_element"
    # Delete anonymous response
    And I click on "Delete entry" "link" in the "Response number: 1" "table_row"
    And I press "Yes"
    And I should see "Anonymous entries (1)"
    And I should not see "Response number: 1"
    And I should see "Response number: 2"

  Scenario: Collecting new non-anonymous feedback from a previously anonymous feedback activity
    When I am on the "Course feedback" "feedback activity" page logged in as teacher
    And I navigate to "Settings" in current page administration
    And I set the following fields to these values:
      | Allow multiple submissions | Yes |
    And I press "Save and display"
    And the following "mod_feedback > question" exists:
      | activity      | feedback1                   |
      | name          | this is a short text answer |
      | questiontype  | textfield                   |
      | itemmaxlength | 200                         |
    And the following "mod_feedback > responses" exist:
      | activity  | user  | this is a short text answer |
      | feedback1 | user1 | anontext                    |
    # Switch to non-anon responses.
    And I am on the "Course feedback" "feedback activity editing" page logged in as teacher
    And I set the following fields to these values:
        | Record user names | User's name will be logged and shown with answers |
    And I press "Save and display"
    # Now leave a non-anon feedback as user1
    And the following "mod_feedback > responses" exist:
      | activity  | user  | this is a short text answer |
      | feedback1 | user1 | usertext                    |
    # Now check the responses are correct.
    And I am on the "Course feedback" "feedback activity" page logged in as teacher
    And I follow "Responses"
    Then I should see "Anonymous entries (1)"
    And I should see "Non anonymous entries (1)"
    And I click on "," "link" in the "Username 1" "table_row"
    And I should see "(Username 1)"
    And I should see "usertext"
    And I navigate to "Responses" in current page administration
    And I follow "Response number: 1"
    And I should see "Response number: 1 (Anonymous)"
    Then I should see "anontext"
