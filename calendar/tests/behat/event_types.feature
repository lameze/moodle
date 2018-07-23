@core @core_calendar
Feature: Perform basic calendar functionality
  In order to ensure the calendar works as expected
  As an admin
  I need to create calendar data

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | student1 | Student | 1 | student1@example.com |
      | teacher1 | Teacher | 1 | teacher1@example.com |
    And I log in as "admin"
    And I am on site homepage
    And I turn editing mode on
    And I add the "Calendar" block
    And I log out
#    And the following "courses" exist:
#      | fullname | shortname |
#      | Course 1 | C1 |
#      | Course 2 | C2 |
#      | Course 3 | C3 |
#    And the following "course enrolments" exist:
#      | user | course | role |
#      | student1 | C1 | student |
#      | teacher1 | C1 | teacher |
#      | student1 | C2 | student |
#      | teacher1 | C2 | teacher |
#      | student1 | C3 | student |
#      | teacher1 | C3 | teacher |
#    And the following "groups" exist:
#      | name | course | idnumber |
#      | Group 1 | C1 | G1 |
#      | Group 2 | C1 | G2 |
#      | Group 3 | C2 | G3 |
#      | Group 4 | C2 | G4 |
#    And the following "group members" exist:
#      | user | group |
#      | student1 | G1 |
#      | teacher1 | G1 |
#
#  @javascript
#  Scenario: Default event types visibility on site calendar
#    Given I log in as "admin"
#      And I follow "This month"
#    When I click on "New event" "button"
#    Then the "Type of event" select box should contain "User"
#    # Course and group types only available when there are courses and groups created.
#    And the "Type of event" select box should not contain "Group"
#    And the "Type of event" select box should not contain "Course"
#    And the "Type of event" select box should contain "Category"
#    And the "Type of event" select box should contain "Site"
#    And I click on "Close" "button"
#    And I log out
#
#    # Teacher should be able to create only user events.
#    Given I log in as "teacher1"
#    And I follow "This month"
#    When I click on "New event" "button"
#    Then I should see "User"
#    And "Type of event" "select" should not exist
#    And I click on "Close" "button"
#    And I log out
#
#    # Student role should be able to create just user events by default.
#    Given I log in as "student1"
#    And I follow "This month"
#    When I click on "New event" "button"
#    Then I should see "User"
#    And "Type of event" "select" should not exist
#    And I click on "Close" "button"
#    And I log out

#  @javascript
#  Scenario: Course event type should be visible only when there are available courses
#  Given the following "courses" exist:
#    | fullname | shortname |
#    | Course 1 | C1 |
#  And the following "course enrolments" exist:
#      | user | course | role |
#      | teacher1 | C1 | teacher |
##    And I log out
#    Given I log in as "teacher1"
#    And I am on homepage
#    And I follow "This month"
#    When I click on "New event" "button"
#    Then the "Type of event" select box should contain "Course"
#    And the "Type of event" select box should contain "User"
#    And the "Type of event" select box should not contain "Group"
#    And I click on "Close" "button"
#    And I log out

#  @javascript
#  Scenario: Group event type should be visible only when there are available courses
#    Given the following "courses" exist:
#      | fullname | shortname |
#      | Course 1 | C1 |
#    And the following "course enrolments" exist:
#      | user | course | role |
#      | teacher1 | C1 | teacher |
#    And the following "groups" exist:
#      | name | course | idnumber |
#      | Group 1 | C1 | G1 |
#      | Group 2 | C1 | G2 |
#    And the following "group members" exist:
#      | user | group |
#      | teacher1 | G1 |
#      | teacher1 | G2 |
##    And I log out
#    Given I log in as "teacher1"
#    And I am on homepage
#    And I follow "This month"
#    When I click on "New event" "button"
#    Then the "Type of event" select box should contain "Course"
#    And the "Type of event" select box should contain "User"
#    And the "Type of event" select box should contain "Group"
#    And the "Type of event" select box should not contain "Site"
#    And the "Type of event" select box should not contain "Category"
#    And I click on "Close" "button"
#    And I log out
#
#  @javascript
#  Scenario: Course field should only list available courses group event type
#    Given the following "courses" exist:
#      | fullname | shortname |
#      | Course 1 | C1 |
#      | Course 2 | C2 |
#    And the following "course enrolments" exist:
#      | user | course | role |
#      | teacher1 | C1 | teacher |
#      | teacher1 | C2 | teacher |
#    And the following "groups" exist:
#      | name | course | idnumber |
#      | Group 1 | C1 | G1 |
#      | Group 2 | C1 | G2 |
#    And the following "group members" exist:
#      | user | group |
#      | teacher1 | G1 |
#      | teacher1 | G2 |
#    Given I log in as "teacher1"
#    And I am on homepage
#    And I follow "This month"
#    When I click on "New event" "button"
#    And I set the field "Type of event" to "Group"
#    And I click on "Course" the autocomplete suggestions list
#    And I should see "C1" in the ".form-autocomplete-suggestions" "css_element"
#    And I should not see "C2" in the ".form-autocomplete-suggestions" "css_element"
#    And I click on "Close" "button"
#    And I log out
  # Course field should not list courses without groups
  # student should be able to create course and group events and course events
#  Scenario: Student role should be able to create course events with correct capabilities
#    Given I log in as "admin"
#    And I set the following system permissions of "Student" role:
#      | capability | permission |
#      | moodle/calendar:manageentries | Allow |
#    And I log in as "student 1"
#    And I follow "Calendar"
#    When I click on "New event" "button"
#    Then the "Type of event" select box should contain "User"
#    And the "Type of event" select box should contain "Course"
#    And the "Type of event" select box should not contain "Group"
#    And the "Type of event" select box should not contain "Category"
#    And the "Type of event" select box should not contain "Site"
#    And I click on "Close" "button"
#    And I log out
#
#  Scenario: Student role should be able to create group events with correct capabilities
#    Given I log in as "admin"
#    And I set the following system permissions of "Student" role:
#      | capability | permission |
#      | moodle/calendar:manageentries | Allow |
#      | moodle/calendar:acessallgroups | Allow |
#    And I log in as "student 1"
#    And I follow "Calendar"
#    When I click on "New event" "button"
#    Then the "Type of event" select box should contain "User"
#    And the "Type of event" select box should contain "Course"
#    And the "Type of event" select box should contain "Group"
#    And the "Type of event" select box should not contain "Category"
#    And the "Type of event" select box should not contain "Site"
#
#  Scenario: Student role should be able to create group events with correct capabilities (MANAGEGROUPENTRIES)
#    Given I log in as "admin"
#    And I set the following system permissions of "Student" role:
#      | capability | permission |
#      | moodle/calendar:managegroupentries| Allow |
#    And I log in as "student 1"
#    And I follow "Calendar"
#    When I click on "New event" "button"
#    And the "Type of event" select box should not contain "Group"
#    And I log in as "admin"
#    And I navigate to "Users > Groups" in current page administration
#    And I add "Student 1 (student0@example.com)" user to "Group 1" group members
#    And I log in as "student 1"
#    And I follow "Calendar"
#    When I click on "New event" "button"
#    And the "Type of event" select box should contain "Group"
#
#    # GROUP MODE SHOULD NOT AFFECT
#  Scenario: Course group mode should not affect group visibility
#    Given I log in as "admin"
#    And I am on "Course 1" course homepage
#    When I navigate to "Edit settings" in current page administration
#    Given I set the following fields to these values:
#      | Group mode | Separate groups |
#      | Force group mode | No |
  # WHAT IF i REMOVE MANAGEOWNENTRIES