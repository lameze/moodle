@enrol @enrol_cohort
Feature: Cohort enrolment management

  Background:
    Given the following "users" exist:
      | username    | firstname | lastname | email                   |
      | teacher001  | Teacher   | 001      | teacher001@example.com  |
    And the following "cohorts" exist:
      | name         | idnumber | visible |
      | Alpha1       | A1       | 1       |
      | Beta2        | B1       | 1       |
    And the following "courses" exist:
      | fullname   | shortname | format | startdate       |
      | Course 001 | C001      | weeks  | ##1 month ago## |
    And the following "course enrolments" exist:
      | user       | course | role           | timestart       |
      | teacher001 | C001   | editingteacher | ##1 month ago## |

  @javascript
  Scenario: Add multiple cohorts to the course
    When I log in as "teacher001"
    And I am on the "Course 001" "enrolment methods" page
    And I select "Cohort sync" from the "Add method" singleselect
    And I open the autocomplete suggestions list
    And I click on "Alpha1" item in the autocomplete list
    And "Alpha1" "autocomplete_selection" should exist
    And I click on "Beta2" item in the autocomplete list
    And "Alpha1" "autocomplete_selection" should exist
    And "Beta2" "autocomplete_selection" should exist
    And I press "Add method"
    Then I should see "Cohort sync (Beta2 - Student)"
    And I should see "Cohort sync (Alpha1 - Student)"

  @javascript
  Scenario: Edit cohort enrolment
    When I log in as "teacher001"
    And I add "Cohort sync" enrolment method in "Course 001" with:
      | Cohort | Alpha1 |
    And I should see "Cohort sync (Alpha1 - Student)"
    And I click on "Edit" "link" in the "Alpha1" "table_row"
    And I set the field "Assign role" to "Non-editing teacher"
    And I click on "Save" "button"
    And I should see "Cohort sync (Alpha1 - Non-editing teacher)"

  @javascript
  Scenario: Course cohort enrolment sync cohorts members
    Given the following "users" exist:
      | username | firstname | lastname | email          |
      | s1       | Sandra1   | Student1 | s1@example.com |
      | s2       | Sandra2   | Student2 | s2@example.com |
      | s4       | Sandra4   | Student4 | s4@example.com |
    And the following "cohort members" exist:
      | user | cohort |
      | s1   | A1     |
      | s2   | A1     |
    When I log in as "teacher001"
    And I add "Cohort sync" enrolment method in "Course 001" with:
      | Cohort      | A1 |
      | customint2  | -1 |
    Then I should see "Cohort sync (Alpha1 - Student)"
    And I select "Groups" from the "jump" singleselect
    # Redundant step, as the following step will fail if Alpha1 cohort is not present.
    #And the "groups[]" select box should contain "Alpha1 cohort (2)"
    And I set the field "groups[]" to "Alpha1 cohort (2)"
    And the "members" select box should contain "Sandra1 Student1 (s1@example.com)"
    And the "members" select box should contain "Sandra2 Student2 (s2@example.com)"
    #And I click on "Add/remove users" "button"
    # Do we really need to check that both users are listed again?
#    And I should see "Add/remove users: Alpha1 cohort"
#    And the "removeselect[]" select box should contain "Sandra1 Student1 (s1@example.com)"
#    And the "removeselect[]" select box should contain "Sandra2 Student2 (s2@example.com)"
#    And I log out
    And I log in as "admin"
    And I navigate to "Users > Accounts > Cohorts" in site administration
    And I press "Assign" action in the "Alpha1" report row
    And I should see "Cohort 'Alpha1' members"
    And I should see "Removing users from a cohort may result in unenrolling of users from multiple courses which includes deleting of user settings, grades, group membership and other user information from affected courses."
    # Do we really need to check that both users are listed again?
    # No need to check if Sandra is listed if it's going to be removed in the step below (it will fail, if not there)
    #And the "removeselect[]" select box should contain "Sandra1 Student1 (s1@example.com)"
    #And the "removeselect[]" select box should contain "Sandra2 Student2 (s2@example.com)"
    And I set the field "removeselect[]" to "Sandra2 Student2 (s2@example.com)"
    And I click on "Remove" "button"
    # Add user s4 to the cohort.
    And I set the field "addselect_searchtext" to "s4"
    And I set the field "addselect[]" to "Sandra4 Student4 (s4@example.com)"
    And I click on "Add" "button"
    And the "removeselect[]" select box should contain "Sandra1 Student1 (s1@example.com)"
    And the "removeselect[]" select box should contain "Sandra4 Student4 (s4@example.com)"
    And the "removeselect[]" select box should not contain "Sandra2 Student2 (s2@example.com)"
    And I trigger cron
    And I am on "Course 001" course homepage
    And I navigate to course participants
    # Verifies students 1 and 4 are in the cohort and student 2 is not any more.
    And the following should exist in the "participants" table:
      | First name / Last name | Email address  | Roles   | Groups        |
      | Sandra1 Student1       | s1@example.com | Student | Alpha1 cohort |
      | Sandra4 Student4       | s4@example.com | Student | Alpha1 cohort |
    And the following should not exist in the "participants" table:
      | First name / Last name | Email address  | Roles   | Groups        |
      | Sandra2 Student2       | s2@example.com | Student | Alpha1 cohort |

    @javascript
    Scenario: Course cohort enrolment creates a new group
    Given the following "users" exist:
      | username | firstname | lastname | email          |
      | s3       | Sandra3   | Student3 | s3@example.com |
      | s5       | Sandra5   | Student5 | s5@example.com |
    And the following "cohort members" exist:
      | user | cohort |
      | s3   | B1     |
      | s5   | B1     |
    When I log in as "teacher001"
    And I add "Cohort sync" enrolment method in "Course 001" with:
      | Cohort      | B1 |
    And I click on "Edit" "link" in the "Beta2" "table_row"
    And I set the field "Add to group" to "Create new group"
    And I click on "Save changes" "button"
    And I select "Groups" from the "jump" singleselect
    # Redundant step, as the following step will fail if Beta2 cohort is not present.
    # And the "groups[]" select box should contain "Beta2 cohort (2)"
    And I set the field "groups[]" to "Beta2 cohort (2)"
    Then the "members" select box should contain "Sandra3 Student3 (s3@example.com)"
    And the "members" select box should contain "Sandra5 Student5 (s5@example.com)"
