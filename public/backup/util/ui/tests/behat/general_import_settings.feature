@core @core_backup
Feature: General import settings
  In order to import course content
  As a teacher
  I can import to a course based on default settings

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
      | Course 2 | C2        | 0        |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | teacher1 | C2     | editingteacher |
    And the following "activities" exist:
      | activity | name               | course | idnumber   |
      | data     | Test database name | C1     | database1  |
      | forum    | Test forum name    | C1     | forum1     |
    And the following "blocks" exist:
      | blockname   | contextlevel | reference | pagetypepattern | defaultregion |
      | comments    | Course       | C1        | course-view-*   | side-pre      |
    And the following "permission overrides" exist:
      | capability         | permission | role    | contextlevel | reference |
      | enrol/manual:enrol | Allow      | teacher | Course       | C1        |
    And the following "core_badges > Badges" exist:
      | name                                      | course | description       | image                        | status | type |
      | Published course badge                    | C1     | Badge description | badges/tests/behat/badge.png | active | 2    |
    And the following "core_badges > Criterias" exist:
      | badge                    | role           |
      | Published course badge   | editingteacher |
    And the following "groups" exist:
      | name | description | course | idnumber |
      | Group 1 | Group description | C1 | GROUP1 |
      | Group 2 | Group description | C1 | GROUP2 |
    And the following "groupings" exist:
      | name | course | idnumber |
      | Grouping 1 | C1 | GROUPING1 |
      | Grouping 2 | C1 | GROUPING2 |

  @allowDebugMessages
  Scenario Outline: Import course permission based on default setting
    Given the following config values are set as admin:
      | backup_import_permissions | <state> | backup |
    And I log in as "teacher1"
    When I import "Course 1" course into "Course 2" course using this options:
    And I am on the "Course 2" "permissions" page
    Then I should see "Non-editing teacher (<expect>)"

    Examples:
      | state | expect |
      | 0     | 0      |
      | 1     | 1      |

  @allowDebugMessages
  Scenario Outline: Import activities based on default setting
    Given the following config values are set as admin:
      | backup_import_activities | <state> | backup |
    And I log in as "teacher1"
    When I import "Course 1" course into "Course 2" course using this options:
    Then I should <expect> "Test database name"
    And I should <expect> "Test forum name"

    Examples:
      | state | expect  |
      | 0     | not see |
      | 1     | see     |

  @allowDebugMessages
  Scenario Outline: Import course's blocks based on default setting
    Given the following config values are set as admin:
      | backup_import_blocks     | <state> | backup |
    And I log in as "teacher1"
    When I import "Course 1" course into "Course 2" course using this options:
    Then "Comments" "block" should <expect>

    Examples:
      | state | expect    |
      | 0     | not exist |
      | 1     | exist     |

  @allowDebugMessages
  Scenario Outline: Import course badge based on default setting
    Given the following config values are set as admin:
      | backup_import_badges | <state> | backup |
    And I log in as "teacher1"
    When I import "Course 1" course into "Course 2" course using this options:
    And I navigate to "Badges" in current page administration
    Then I should <expect> "Published course badge"

    Examples:
      | state | expect  |
      | 0     | not see |
      | 1     |  see    |

  @allowDebugMessages
  Scenario Outline: Import groups and groupings based on default setting
    Given the following config values are set as admin:
      | backup_import_groups | <state> | backup |
    And I log in as "teacher1"
    And I import "Course 1" course into "Course 2" course using this options:
    When I am on the "Course 2" "groups" page
    And I should <expect> "Group 1"
    And I should <expect> "Group 2"
    And I am on the "Course 2" "groupings" page
    Then I should <expect> "Grouping 1"
    And I should <expect> "Grouping 2"

    Examples:
      | state | expect  |
      | 0     | not see |
      | 1     |  see    |
