@editor @editor_tiny @tiny_media @javascript @_file_upload
Feature: Use the TinyMCE editor to upload an h5p package
  In order to work with h5p
  As a user
  I need to be able to upload h5p packages

  Background:
    Given the following "courses" exist:
      | shortname | fullname |
      | C1        | Course 1 |
    And the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | 1 | teacher1@example.com |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
    And the following "activities" exist:
      | activity | name       | intro      | introformat | course | content  | contentformat | idnumber |
      | page     | PageName1  | PageDesc1  | 1           | C1     | H5Ptest  | 1             | 1        |
    And the "displayh5p" filter is "on"
    And the following config values are set as admin:
      | allowedsources | https://moodle.h5p.com/content/[id] | filter_displayh5p |
    And the following "blocks" exist:
      | blockname     | contextlevel | reference | pagetypepattern | defaultregion |
      | private_files | System       | 1         | my-index        | side-post     |

  @javascript @external
  Scenario: Insert an embedded h5p using Tiny editor
    Given I change window size to "large"
    And I am on the PageName1 "page activity editing" page logged in as admin
    And I click on the "Configure H5P content" button for the "Page content" TinyMCE editor
    And I set the field "H5P URL or file upload" to "https://moodle.h5p.com/content/1290772960722742119"
    And I click on "Insert H5P content" "button" in the "Insert H5P content" "dialogue"
    And I wait until the page is ready
    When I click on "Save and display" "button"
    Then ".h5p-placeholder" "css_element" should exist
    And I wait until the page is ready
    And I switch to "h5pcontent" iframe
    And I should see "Lorum ipsum"

  @javascript
  Scenario: Insert an h5p using Tiny editor
    Given I log in as "admin"
    And I change window size to "large"
    And I follow "Manage private files..."
    And I upload "h5p/tests/fixtures/guess-the-answer.h5p" file to "Files" filemanager
    And I click on "Save changes" "button"
    And I am on the "PageName1" "page activity editing" page
    And I click on the "Configure H5P content" button for the "Page content" TinyMCE editor
    And I click on "Browse repositories..." "button" in the "Insert H5P content" "dialogue"
    And I click on "Private files" "link" in the ".fp-repo-area" "css_element"
    And I click on "guess-the-answer.h5p" "link"
    And I click on "Select this file" "button"
    And I click on "Insert H5P content" "button" in the "Insert H5P content" "dialogue"
    And I wait until the page is ready
    When I click on "Save and display" "button"
    Then ".h5p-placeholder" "css_element" should exist

  @javascript
  Scenario: Test an invalid url
    Given I change window size to "large"
    And I am on the PageName1 "page activity editing" page logged in as admin
    And I click on the "Configure H5P content" button for the "Page content" TinyMCE editor
#   This is not a real external URL, so this scenario shouldn't be labeled as external.
    And I set the field "H5P URL or file upload" to "ftp://moodle.h5p.com/content/1290772960722742119"
    When I click on "Insert H5P content" "button"
    And I wait until the page is ready
    Then I should see "Invalid URL" in the "Insert H5P" "dialogue"