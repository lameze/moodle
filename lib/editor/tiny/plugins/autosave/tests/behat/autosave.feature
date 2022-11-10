@editor @editor_tiny @_file_upload
Feature: Tiny editor autosave
  To reduce frustration, Tiny editor should save drafts of my work.

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category | groupmode | description |
      | Course 1 | C1        | 0        | 1         |             |
    And the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher   | 1 | teacher1@example.com |
      | teacher2 | Teacher   | 2 | teacher2@example.com |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
      | teacher2 | C1 | editingteacher |
    And the following "blocks" exist:
      | blockname     | contextlevel | reference | pagetypepattern | defaultregion |
      | private_files | System       | 1         | my-index        | side-post     |
    And I am on the "Course 1" course page logged in as admin
    And I navigate to "Settings" in current page administration
    And I set the field with xpath "//select[@name='summary_editor[format]']" to "1"
    And I click on "Save and display" "button"
    And I log out

  @javascript
  Scenario: Restore a draft on user profile page
    Given I log in as "teacher1"
    And I open my profile in edit mode
    And I set the field "Description" to "This is my draft"
    # Wait for the autosave
    And I wait "5" seconds
    And I log out
    When I log in as "teacher1"
    And I open my profile in edit mode
    # Wait for the autorestore
    And I wait "2" seconds
    Then the field "Description" matches value "This is my draft"

  @javascript
  Scenario: Do not restore a draft if files have been modified
    Given I am on the "Course 1" course page logged in as teacher1
    And I navigate to "Settings" in current page administration
    And I set the field "Course summary" to "This is my draft"
    # Wait for the autosave
    And I wait "5" seconds
    And I log out
    And I log in as "teacher2"
    And I follow "Manage private files..."
    And I upload "/lib/editor/tiny/tests/behat/fixtures/tinyscreenshot.png" file to "Files" filemanager
    And I click on "Save changes" "button"
    And I am on "Course 1" course homepage
    And I navigate to "Settings" in current page administration
    And I set the field "Course summary" to "<p>Image test</p>"
    And I select the "p" element in position "1" of the "Course summary" TinyMCE editor
    And I click on the "Image" button for the "Course summary" TinyMCE editor
    And I click on "Browse repositories..." "button"
    And I click on "Private files" "link" in the ".fp-repo-area" "css_element"
    And I click on "tinyscreenshot.png" "link"
    And I click on "Select this file" "button"
    And I set the field "Describe this image" to "It's the Moodle"
    # Wait for the page to "settle".
    And I wait until the page is ready
    And I click on "Save image" "button"
    And I click on "Save and display" "button"
    When I am on the "Course 1" course page logged in as teacher1
    And I navigate to "Settings" in current page administration
    Then I should not see "This is my draft"

  @javascript
  Scenario: Do not restore a draft if text has been modified
    Given I am on the "Course 1" course page logged in as teacher1
    And I navigate to "Settings" in current page administration
    And I set the field "Course summary" to "This is my draft"
    And I wait "5" seconds
    And I am on the "Course 1" course page logged in as teacher2
    And I navigate to "Settings" in current page administration
    And I set the field "Course summary" to "Modified text"
    And I click on "Save and display" "button"
    When I am on the "Course 1" course page logged in as teacher1
    And I navigate to "Settings" in current page administration
    Then I should not see "This is my draft" in the "#id_summary_editor" "css_element"
    And the field "Course summary" matches value "<p>Modified text</p>"
