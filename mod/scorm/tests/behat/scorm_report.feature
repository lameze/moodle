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
      | scorm1 | student2 | 1       | cmi.core.score.raw    | 100       | item_1        |
      | scorm1 | student2 | 1       | cmi.completion_status | completed | item_1        |
    And I am on the "Scorm 1" "scorm activity" page logged in as "teacher1"
    And I navigate to "Reports" in current page administration

  Scenario Outline: Teacher can download scorm report in different supported formats
    Given I click on "Download" "link"
    Then following "<fileformat>" should download a file that:
      | Has mimetype | <mimetypeformat> |

    Examples:
      | fileformat               | mimetypeformat                                                    |
      | Download in ODS format   | application/zip                                                   |
      | Download in Excel format | application/vnd.openxmlformats-officedocument.spreadsheetml.sheet |
      | Download in text format  | text/plain                                                        |

  @javascript
  Scenario: Teacher can delete selected scorm attempts
    Given I click on "scorm-selectall-attempts" "checkbox"
    When I click on "Delete selected attempts" "button" and accept the dialog
    Then I should see "Deleted user attempts"
    And the following should exist in the "generaltable" table:
      | First name | Attempt | Score |
      | Student 1  | -       | -     |
      | Student 2  | -       | -     |

  Scenario Outline: Teacher can track data and scores in interactions and objectives reports
    When I select "<reporttype> report" from the "jump" singleselect
    Then I should see "2 attempts for 3 users, out of 3 results"
    And the following should exist in the "generaltable" table:
      | First name | Email address        | Attempt | Score |
      | Student 1  | student1@example.com | 1       | 50    |
      | Student 2  | student2@example.com | 1       | 100   |

    Examples:
      | reporttype   |
      | Interactions |
      | Objectives   |
