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

  @javascript
  Scenario: Default event types visibility on site calendar
    Given I log in as "admin"
      And I follow "This month"
    When I click on "New event" "button"
    Then the "Type of event" select box should contain "User"
    # Course and group types only available when there are courses and groups created.
    And the "Type of event" select box should not contain "Group"
    And the "Type of event" select box should not contain "Course"
    And the "Type of event" select box should contain "Category"
    And the "Type of event" select box should contain "Site"
    And I click on "Close" "button"
    And I log out

    # Teacher should be able to create only user events.
    Given I log in as "teacher1"
    And I follow "This month"
    When I click on "New event" "button"
    Then I should see "User"
    And "Type of event" "select" should not exist
    And I click on "Close" "button"
    And I log out

    # Student role should be able to create just user events by default.
    Given I log in as "student1"
    And I follow "This month"
    When I click on "New event" "button"
    Then I should see "User"
    And "Type of event" "select" should not exist
    And I click on "Close" "button"
    And I log out

  @javascript
  Scenario: Course event type should be visible only when there are available courses
  Given the following "courses" exist:
    | fullname | shortname |
    | Course 1 | C1 |
  And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | teacher |
#    And I log out
    Given I log in as "teacher1"
    And I am on homepage
    And I follow "This month"
    When I click on "New event" "button"
    Then the "Type of event" select box should contain "Course"
    And the "Type of event" select box should contain "User"
    And the "Type of event" select box should not contain "Group"
    And I click on "Close" "button"
    And I log out

  @javascript
  Scenario: Group event type should be visible only when there are available courses
    Given the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1 |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | teacher |
    And the following "groups" exist:
      | name | course | idnumber |
      | Group 1 | C1 | G1 |
      | Group 2 | C1 | G2 |
    And the following "group members" exist:
      | user | group |
      | teacher1 | G1 |
      | teacher1 | G2 |
    And I log out
    Given I log in as "teacher1"
    And I am on homepage
    And I follow "This month"
    When I click on "New event" "button"
    Then the "Type of event" select box should contain "Course"
    And the "Type of event" select box should contain "User"
    And the "Type of event" select box should contain "Group"
    And the "Type of event" select box should not contain "Site"
    And the "Type of event" select box should not contain "Category"
    And I click on "Close" "button"
    And I log out

  @javascript
  Scenario: Course field should only list available courses group event type
    Given the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1 |
      | Course 2 | C2 |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | teacher |
      | teacher1 | C2 | teacher |
    And the following "groups" exist:
      | name | course | idnumber |
      | Group 1 | C1 | G1 |
      | Group 2 | C1 | G2 |
    And the following "group members" exist:
      | user | group |
      | teacher1 | G1 |
      | teacher1 | G2 |
    Given I log in as "teacher1"
    And I am on homepage
    And I follow "This month"
    When I click on "New event" "button"
    And I set the field "Type of event" to "Group"
    And I open the autocomplete suggestions list in the "[name=groupcourseid]" "css_element"
    And I should see "Course 1" in the "div select[name=groupcourseid] ~ .form-autocomplete-suggestions" "css_element"
    And I should not see "Course 2" in the "div select[name=groupcourseid] ~ .form-autocomplete-suggestions" "css_element"
    And I click on "Close" "button"
    And I log out

  @javascript
  Scenario: Student role with manageentries capability should be able to create course events
    Given the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1 |
      | Course 2 | C1 |
    And the following "course enrolments" exist:
      | user | course | role |
      | student1 | C1 | student |
    And I log in as "admin"
    And I set the following system permissions of "Student" role:
      | capability | permission |
      | moodle/calendar:manageentries | Allow |
    And I log out
    And I log in as "student1"
    And I am on homepage
    And I follow "This month"
    When I click on "New event" "button"
    Then the "Type of event" select box should contain "User"
    And the "Type of event" select box should contain "Course"
    And the "Type of event" select box should not contain "Group"
    And the "Type of event" select box should not contain "Category"
    And the "Type of event" select box should not contain "Site"
    And I open the autocomplete suggestions list in the "[name=courseid]" "css_element"
    And I should see "Course 1" in the "div select[name=courseid] ~ .form-autocomplete-suggestions" "css_element"

  @javascript
  Scenario: A user should be able to create an event for a group that they are a member of in a course in which they
            are enrolled and have the moodle/calendar:managegroupentries capability.
    Given the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1 |
      | Course 2 | C2 |
    And the following "course enrolments" exist:
      | user | course | role |
      | student1 | C1 | student |
        And the following "groups" exist:
      | name | course | idnumber |
      | Group 1 | C1 | G1 |
      | Group 2 | C1 | G2 |
      | Group 3 | C2 | G3 |
      | Group 4 | C2 | G4 |
    And the following "group members" exist:
      | user | group |
      | student1 | G1 |
      | student1 | G2 |
    And I log in as "admin"
    And I set the following system permissions of "Student" role:
      | capability | permission |
      | moodle/calendar:managegroupentries | Allow |
      | moodle/calendar:manageentries | Prohibit |
    And I am on "Course 1" course homepage with editing mode on
    And I add the "Calendar" block
    And I log out
    And I log in as "student1"
    And I am on homepage
    And I follow "This month"
    When I click on "New event" "button"
    Then I should see "User"
    And "Type of event" "select" should not exist
    And I am on "Course 1" course homepage
    And I follow "This month"
    When I click on "New event" "button"
    Then the "Type of event" select box should contain "User"
    And the "Type of event" select box should not contain "Course"
    And the "Type of event" select box should contain "Group"
    And the "Type of event" select box should not contain "Category"
    And the "Type of event" select box should not contain "Site"
    And I open the autocomplete suggestions list in the "[name=groupcourseid]" "css_element"
    And I should see "Course 1" in the "div select[name=groupcourseid] ~ .form-autocomplete-suggestions" "css_element"
    And I should not see "Course 2" in the "div select[name=groupcourseid] ~ .form-autocomplete-suggestions" "css_element"

  @javascript
  Scenario: Student role with manageentries capability should be able to create site events (NOT WORKING YET)
    And I log in as "admin"
    And I set the following system permissions of "Student" role:
      | capability | permission |
      | moodle/calendar:manageentries | Allow |
    And I navigate to "Define roles" node in "Site administration > Users > Permissions"
    And I follow "Edit Student role"
    And I click on "System" "checkbox"
    And I press "Save changes"
    And I pause
    And I log out
    And I log in as "student1"
    And I am on homepage
    And I follow "This month"
    When I click on "New event" "button"
    Then the "Type of event" select box should contain "User"
    And the "Type of event" select box should not contain "Course"
    And the "Type of event" select box should not contain "Group"
    And the "Type of event" select box should not contain "Category"
    And the "Type of event" select box should contain "Site"

  @javascript
  Scenario: Student role with managegroupentries capability should be able to create group events on course co
    Given the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1 |
    And the following "course enrolments" exist:
      | user | course | role |
      | student1 | C1 | student |
        And the following "groups" exist:
      | name | course | idnumber |
      | Group 1 | C1 | G1 |
      | Group 2 | C1 | G2 |
    And the following "group members" exist:
      | user | group |
      | student1 | G1 |
      | student1 | G2 |
    And I log in as "admin"
    And I set the following system permissions of "Student" role:
      | capability | permission |
      | moodle/calendar:managegroupentries | Allow |
    And I log out
    And I log in as "student1"
    And I am on homepage
    And I follow "This month"
    And I set the field "menucourse" to "C1"
    When I click on "New event" "button"
    Then the "Type of event" select box should contain "User"
    And the "Type of event" select box should contain "Group"
    And the "Type of event" select box should not contain "Course"
    And the "Type of event" select box should not contain "Category"
    And the "Type of event" select box should not contain "Site"

  @javascript
  Scenario: Student role with manageentries and accessallgroups capabilities should be able to create group and course events
    Given the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1 |
    And the following "course enrolments" exist:
      | user | course | role |
      | student1 | C1 | student |
    And the following "groups" exist:
      | name | course | idnumber |
      | Group 1 | C1 | G1 |
      | Group 2 | C1 | G2 |
    And the following "group members" exist:
      | user | group |
      | student1 | G1 |
      | student1 | G2 |
    And I log in as "admin"
    And I set the following system permissions of "Student" role:
      | capability | permission |
      | moodle/calendar:manageentries | Allow |
      | moodle/site:accessallgroups | Allow |
    And I log out
    And I log in as "student1"
    And I am on homepage
    And I follow "This month"
    And I set the field "menucourse" to "C1"
    When I click on "New event" "button"
    Then the "Type of event" select box should contain "User"
    And the "Type of event" select box should contain "Group"
    And the "Type of event" select box should contain "Course"
    And the "Type of event" select box should not contain "Category"
    And the "Type of event" select box should not contain "Site"

  @javascript
  Scenario: Student role with manageentries capabilities should be able to create course events
    Given the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1 |
    And the following "course enrolments" exist:
      | user | course | role |
      | student1 | C1 | student |
    And I log in as "admin"
    And I set the following system permissions of "Student" role:
      | capability | permission |
      | moodle/calendar:manageentries | Allow |
    And I log out
    And I log in as "student1"
    And I am on homepage
    And I follow "This month"
    And I set the field "menucourse" to "C1"
    When I click on "New event" "button"
    Then the "Type of event" select box should contain "User"
    And the "Type of event" select box should contain "Course"
    And the "Type of event" select box should not contain "Group"
    And the "Type of event" select box should not contain "Category"
    And the "Type of event" select box should not contain "Site"

  @javascript
  Scenario: Student role with manageentries capabilities should be able to create course events
    Given the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1 |
    And the following "course enrolments" exist:
      | user | course | role |
      | student1 | C1 | student |
    And I log in as "admin"
    And I set the following system permissions of "Student" role:
      | capability | permission |
      | moodle/category:manage | Allow |
      | moodle/calendar:manageentries | Allow |
    And I log out
    And I log in as "student1"
    And I am on homepage
    And I follow "This month"
    And I set the field "menucourse" to "C1"
    When I click on "New event" "button"
    Then the "Type of event" select box should contain "User"
    And the "Type of event" select box should contain "Category"
    And the "Type of event" select box should not contain "Group"
    And the "Type of event" select box should not contain "Category"
    And the "Type of event" select box should not contain "Site"