@mod @mod_bigbluebuttonbn @javascript
Feature: Bigbluebuttonbn rooms
  A meeting is created, roles for each type of participant can be changed

  Background:
    Given I enable "bigbluebuttonbn" "mod" plugin
    And the following course exists:
      | name      | Test course |
      | shortname | C1          |
    And the following "users" exist:
      | username | firstname | lastname | email                 |
      | traverst | Terry     | Travers  | t.travers@example.com |
    And the following "course enrolments" exist:
      | user     | course | role   |
      | traverst | C1     | student |
    And the following "activity" exists:
      | course              | C1               |
      | activity            | bigbluebuttonbn  |
      | name                | RoomRecordings   |
      | idnumber            | bigbluebuttonbn1 |
      | type                | 0                |
      | recordings_imported | 0                |

  Scenario: Add a mod_bigbluebuttonbn instance and set the teacher role as moderator
    Given I am on the "RoomRecordings" "bigbluebuttonbn activity editing" page logged in as "admin"
    And I set the field "bigbluebuttonbn_participant_selection_type" to "Role"
    And I set the field "bigbluebuttonbn_participant_selection" to "Manager"
    And I click on "bigbluebuttonbn_participant_selection_add" "button"
    And I set the field "select-for-role-1" to "Moderator"
    And I press "Save and display"
    When I am on the "RoomRecordings" "bigbluebuttonbn activity editing" page logged in as "admin"
    Then "[name=select-for-role-1] option[value=moderator][selected]" "css_element" should exist
