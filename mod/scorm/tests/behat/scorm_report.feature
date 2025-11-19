@mod @mod_scorm
Feature: Scorm reports provide tracking data and scores to teachers
  In order to track data and scores for scorm activities
  As a teacher
  I need to be able to view scorm reports

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
      | student1 | Student   | 1        | student1@example.com |
      | student2 | Student   | 2        | student2@example.com |
    And the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | student1 | C1     | student        |
      | student2 | C1     | student        |
      | teacher1 | C1     | editingteacher |
    And the following "activities" exist:
      | activity | course | name    | packagefilepath                             | idnumber | grademethod |
      | scorm    | C1     | Scorm 1 | mod/scorm/tests/packages/singlescobasic.zip | scorm1   | 3           |
    And the following "mod_scorm > attempts" exist:
      | scorm  | user     | attempt | element               | value     | scoidentifier |
      | scorm1 | student1 | 1       | cmi.core.score.raw    | 50        | item_1        |
      | scorm1 | student1 | 1       | cmi.completion_status | completed | item_1        |
      | scorm1 | student1 | 2       | cmi.core.score.raw    | 70        | item_1        |
      | scorm1 | student1 | 2       | cmi.completion_status | completed | item_1        |
      | scorm1 | student2 | 1       | cmi.core.score.raw    | 100       | item_1        |
      | scorm1 | student2 | 1       | cmi.completion_status | completed | item_1        |
    And I am on the "Scorm 1" "scorm activity" page logged in as "teacher1"
    And I navigate to "Reports" in current page administration

  @javascript
  Scenario: Teacher can track data and scores in basic reports
    When I select "Basic report" from the "jump" singleselect
    Then the following should exist in the "generaltable" table:
      | First name | Email address        | Attempt | Started on              | Last accessed on        | Score |
      | Student 1  | student1@example.com | 1       | ##today##%A, %d %B %Y## | ##today##%A, %d %B %Y## | 50    |
      |            |                      | 2       | ##today##%A, %d %B %Y## | ##today##%A, %d %B %Y## | 70    |
      | Student 2  | student2@example.com | 1       | ##today##%A, %d %B %Y## | ##today##%A, %d %B %Y## | 100   |
    And I click on "//*//a[contains(text(),'1')]" "xpath" in the "Student 2" "table_row"
    And the following should exist in the "generaltable" table:
      | Title                                 | Status        | Time | Score |
      | Golf Explained - Run-time Basic Calls |               |      |       |
      | Golf Explained                        | Not attempted |      |       |

  @javascript
  Scenario Outline: Teacher can download scorm report in different supported formats
    Given I select "Basic report" from the "jump" singleselect
    When I click on "Download" "link"
    Then following "<fileformat>" should download a file that:
      | Has mimetype | <mimetypeformat> |

    Examples:
      | fileformat               | mimetypeformat                                                    |
      | Download in ODS format   | application/zip                                                   |
      | Download in Excel format | application/vnd.openxmlformats-officedocument.spreadsheetml.sheet |
      | Download in text format  | text/plain                                                        |

  @javascript
  Scenario: Teacher can delete selected attempts
    Given I select "Basic report" from the "jump" singleselect
    When I click on "scorm-selectall-attempts" "checkbox"
    And I click on "Delete selected attempts" "button"

  @javascript
  Scenario: Teacher can track data and scores in graph reports
    When I select "Graph report" from the "jump" singleselect
    Then "Show chart data" "link" should exist
    And "Number of participants" "text" should exist
    And "Percent(%) secured" "text" should exist
    And I click on "Show chart data" "link"
    And "0" "text" should exist in the "0 - 10" "table_row"

  Scenario Outline: Teacher can track data and scores in interactions and objectives reports
    When I select "<reporttype> report" from the "jump" singleselect
    Then "3 attempts for 3 users, out of 4 results" "text" should exist
    And the following should exist in the "generaltable" table:
      | First name | Email address        | Attempt | Started on              | Last accessed on        | Score | Golf Explained  |
      | Student 1  | student1@example.com | 1       | ##today##%A, %d %B %Y## | ##today##%A, %d %B %Y## | 50    | Not attempted   |
      |            |                      | 2       | ##today##%A, %d %B %Y## | ##today##%A, %d %B %Y## | 70    | Not attempted   |
      | Student 2  | student2@example.com | 1       | ##today##%A, %d %B %Y## | ##today##%A, %d %B %Y## | 100   | Not attempted   |

    Examples:
      | reporttype   |
      | Interactions |
      | Objectives   |
