@block @block_badges @core_badges @_file_upload @javascript
Feature: Enable Block Badges on the frontpage and view awarded badges
  In order to enable the badges block on the frontpage
  As a admin
  I can add badges block to the frontpage

  Scenario: Add the recent badges block on the frontpage and view recent badges
    Given the following "users" exist:
      | username | firstname | lastname | email | idnumber |
      | teacher1 | Teacher | 1 | teacher1@example.com | T1 |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1 | 0 |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
    And the following "blocks" exist:
      | blockname | contextlevel | reference | pagetypepattern | defaultregion |
      | badges    | System       | 1         | site-index      | side-pre      |
    # Create badge 1.
    And the following "core_badges > Badge" exists:
      | name        | Badge 1                                |
      | course      | C1                                     |
      | description | Badge 1                                |
      | image       | blocks/badges/tests/fixtures/badge.png |
      | type        | 2                                      |
    # Assign editingteacher role as criteria.
    And the following "core_badges > Criteria" exists:
      | badge | Badge 1        |
      | role  | editingteacher |
    # Award badge.
    And I am on the "Course 1" course page logged in as teacher1
    And I navigate to "Badges > Manage badges" in current page administration
    And I click on "Badge 1" "link"
    And I select "Recipients (0)" from the "jump" singleselect
    And I press "Award badge"
    And I set the field "potentialrecipients[]" to "Teacher 1 (teacher1@example.com)"
    And I press "Award badge"
    # Confirm Badge 1 appears on the latest badges block.
    When I am on site homepage
    Then I should see "Badge 1" in the "Latest badges" "block"
