@mod @mod_url @core_completion
Feature: Manual completion of a URL activity
  In order to mark a URL activity as done
  As a student
  I want to be able to manually toggle the completion state of the activity

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Vinnie    | Student1 | student1@example.com |
      | teacher1 | Darrell   | Teacher1 | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category | enablecompletion | showcompletionconditions |
      | Course 1 | C1        | 0        | 1                | 1                        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | student1 | C1     | student        |
      | teacher1 | C1     | editingteacher |
    And the following config values are set as admin:
      | displayoptions | 0,1,2,3,4,5,6 | url |

  @javascript
  Scenario: Use manual completion with automatic URL as student
    Given the following "activity" exists:
      | activity       | url                 |
      | course         | C1                  |
      | idnumber       | Music history       |
      | name           | Music history       |
      | intro          | URL description     |
      | externalurl    | https://moodle.org/ |
      | completion     | 1                   |
      | completionview | 1                   |
      | display        | 0                   |
    When I am on the "Course 1" course page logged in as student1
    Then the manual completion button of "Music history" is displayed as "Mark as done"
    And I toggle the manual completion state of "Music history"
    And the manual completion button of "Music history" is displayed as "Done"

  @javascript
  Scenario Outline: The Mark as done completion condition will be shown on the course page for Open, In pop-up and New window display mode if the Show activity completion conditions is set to No as teacher
    Given the following "activity" exists:
      | activity       | url                 |
      | course         | C1                  |
      | idnumber       | Music history       |
      | name           | Music history       |
      | intro          | URL description     |
      | externalurl    | https://moodle.org/ |
      | completion     | 1                   |
      | completionview | 1                   |
      | display        | <display>           |
      | popupwidth     | 620                 |
      | popupheight    | 450                 |
    When I am on the "Course 1" course page logged in as teacher1
    Then "Music history" should have the "Mark as done" completion condition

    Examples:
      | display | description |
      | 0       | Auto        |
      | 6       | Popup       |
      | 3       | New         |

  @javascript
  Scenario Outline: The manual completion button will be shown on the course page for Open, In pop-up and New window display mode if the Show activity completion conditions is set to No as student
    Given the following "activity" exists:
      | activity       | url                 |
      | course         | C1                  |
      | idnumber       | Music history       |
      | name           | Music history       |
      | intro          | URL description     |
      | externalurl    | https://moodle.org/ |
      | completion     | 1                   |
      | completionview | 1                   |
      | display        | <display>           |
      | popupwidth     | 620                 |
      | popupheight    | 450                 |
    When I am on the "Course 1" course page logged in as student1
    Then the manual completion button for "Music history" should exist
    And the manual completion button of "Music history" is displayed as "Mark as done"
    And I toggle the manual completion state of "Music history"
    And the manual completion button of "Music history" is displayed as "Done"

    Examples:
      | display | description |
      | 0       | Auto        |
      | 6       | Popup       |
      | 3       | New         |
