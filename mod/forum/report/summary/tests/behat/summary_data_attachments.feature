@mod @mod_forum @forumreport @forumreport_summary
Feature: Attachments count column data available
  In order to gather data on users' forum attachments
  As a teacher
  I need to view accurate attachment count data in the forum summary report

  Scenario: Add discussions and replies with attached files
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
      | student1 | Student   | 1        | student1@example.com |
      | student2 | Student   | 2        | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
      | Course 2 | C2        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
      | student2 | C1     | student        |
      | teacher1 | C2     | editingteacher |
    And the following "activities" exist:
      | activity | name   | course | idnumber   |
      | forum    | forum1 | C1     | forum1C1   |
      | forum    | forum2 | C1     | forum2C1   |
      | forum    | forum1 | C2     | forum1C2   |
    And the following "mod_forum > discussions" exist:
      | user     | forum  | name        | message  | attachments        | inlineattachments | course |
      | teacher1 | forum1 | discussion1 | message1 | att1.jpg, att2.txt |                   | C1     |
      | teacher1 | forum2 | discussion2 | message2 | att3.jpg           | in1.jpg           | C1     |
      | student1 | forum1 | discussion3 | message3 | att4.jpg           | in2.jpg           | C1     |
      | student2 | forum1 | discussion4 | message4 |                    |                   | C1     |
      | teacher1 | forum1 | discussion1 | message1 | att1.jpg, att2.txt |                   | C2     |
    And the following "mod_forum > replies" exist:
      | user     | forum  | parentsubject | message  | attachments        | inlineattachments | course |
      | teacher1 | forum1 | discussion1   | reply1   | att5.jpg           | in3.txt           | C1     |
      | teacher1 | forum1 | discussion1   | reply2   | att5.jpg           | in3.txt           | C1     |
      | teacher1 | forum2 | discussion2   | reply2   | att6.jpg           |                   | C1     |
      | student1 | forum1 | discussion3   | reply3   | att7.jpg, att8.jpg | in2.jpg           | C1     |
      | student2 | forum1 | discussion4   | reply4   |                    |                   | C1     |
    When I am on the forum1C1 "forum activity" page logged in as teacher1
    And I navigate to "Reports" in current page administration
    Then "Teacher 1" row "Number of attachments" column of "forumreport_summary_table" table should contain "6"
    And "Student 1" row "Number of attachments" column of "forumreport_summary_table" table should contain "5"
    And "Student 2" row "Number of attachments" column of "forumreport_summary_table" table should contain "0"
