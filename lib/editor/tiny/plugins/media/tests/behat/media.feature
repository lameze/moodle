@editor @editor_tiny @tiny_media @javascript
Feature: Use the TinyMCE editor to upload a media file
  In order to work with media files
  As a user
  I need to be able to upload and change settings of media files

  @_file_upload
  Scenario: Insert video in the TinyMCE editor
    Given I log in as "admin"
    And I open my profile in edit mode
    And I click on the "Multimedia" button for the "Description" TinyMCE editor
    And I click on "Browse repositories" "button"
    When I upload "/lib/editor/tiny/tests/behat/fixtures/moodle-logo.mp4" to the file picker for TinyMCE
    Then "Media details" "dialogue" should exist
    And I pause
    And "Media title" "field" should exist in the "Media details" "dialogue"
    And the field "Media title" in the "Media details" "dialogue" matches value "moodle-logo"
    And the field "Show controls" in the "Media details" "dialogue" matches value "1"
    And the field "Autoplay" in the "Media details" "dialogue" matches value "0"
    And the field "Muted" in the "Media details" "dialogue" matches value "0"
    And the field "Loop" in the "Media details" "dialogue" matches value "0"
    And the field "Original size" in the "Media details" "dialogue" matches value "1"
    And the field "Custom size" in the "Media details" "dialogue" matches value "0"

  @_file_upload
  Scenario: Update video in the TinyMCE editor
    Given I log in as "admin"
    And I open my profile in edit mode
    And I click on the "Multimedia" button for the "Description" TinyMCE editor
    And I click on "Browse repositories" "button"
    And I upload "/lib/editor/tiny/tests/behat/fixtures/moodle-logo.mp4" to the file picker for TinyMCE
    And I click on "Overwrite" "button"
    And I set the field "Media title" to "Moodle LMS Logo"
    And I set the field "Autoplay" to "1"
    And I set the field "Muted" to "1"
    And I set the field "Loop" to "1"
    And I click on "Custom size" "radio" in the "Media details" "dialogue"
    And I set the field "Width" in the "Media details" "dialogue" to "300"
    And I click on "Save" "button" in the "Media details" "dialogue"
    And I switch to the "Description" TinyMCE editor iframe
    And "//*[contains(@data-id, 'id_description_editor')]//video[@title='Moodle LMS Logo' and @autoplay='autoplay' and @loop='loop' and @muted='true' and @controls='controls']" "xpath_element" should exist
    And "//*[contains(@data-id, 'id_description_editor')]//video//source[contains(@src, 'moodle-logo.mp4')]" "xpath_element" should exist

  @_file_upload
  Scenario: Update audio in the TinyMCE editor
    Given I log in as "admin"
    And I open my profile in edit mode
    And I click on the "Multimedia" button for the "Description" TinyMCE editor
    And I click on "Browse repositories" "button"

    And I upload "/lib/editor/tiny/tests/behat/fixtures/audio-sample.mp3" to the file picker for TinyMCE
    And I click on "Overwrite" "button"
    And I set the field "Media title" to "Sample Audio File"
    And I set the field "Autoplay" to "1"
    And I set the field "Muted" to "1"
    And I set the field "Loop" to "1"
    And I click on "Save" "button" in the "Media details" "dialogue"
    And I switch to the "Description" TinyMCE editor iframe
    And "//*[contains(@data-id, 'id_description_editor')]//audio[@title='Sample Audio File' and @autoplay='autoplay' and @loop='loop' and @muted='true' and @controls='controls']" "xpath_element" should exist
    And "//*[contains(@data-id, 'id_description_editor')]//audio//source[contains(@src, 'audio-sample.mp3')]" "xpath_element" should exist

  @_file_upload
  Scenario: Insert audio in the TinyMCE editor
    Given I log in as "admin"
    And I open my profile in edit mode
    And I click on the "Multimedia" button for the "Description" TinyMCE editor
    And I click on "Browse repositories" "button"

    When I upload "/lib/editor/tiny/tests/behat/fixtures/audio-sample.mp3" to the file picker for TinyMCE
    Then "Media details" "dialogue" should exist
    And "Media title" "field" should exist in the "Media details" "dialogue"
    And the field "Media title" in the "Media details" "dialogue" matches value "audio-sample"
    And the field "Show controls" in the "Media details" "dialogue" matches value "1"
    And the field "Autoplay" in the "Media details" "dialogue" matches value "0"
    And the field "Muted" in the "Media details" "dialogue" matches value "0"
    And the field "Loop" in the "Media details" "dialogue" matches value "0"
    And "Original size" "field" should not exist in the "Media details" "dialogue"
    And "Custom size" "field" should not exist in the "Media details" "dialogue"

  @_file_upload
  Scenario Outline: Delete media in the TinyMCE editor
    Given I log in as "admin"
    And I open my profile in edit mode
    And I click on the "Multimedia" button for the "Description" TinyMCE editor
    And I click on "Browse repositories" "button"

    And I upload "/lib/editor/tiny/tests/behat/fixtures/<fixturefile>" to the file picker for TinyMCE
    And I click on "Delete media" "button" in the "Media details" "dialogue"
    Then "Delete media" "dialogue" should exist
    And "Delete" "button" should exist in the "Delete media" "dialogue"
    And I click on "Delete" "button" in the "Delete media" "dialogue"
    And "Insert media" "dialogue" should exist

    Examples:
      | fixturefile      |
      | moodle-logo.mp4  |
      | audio-sample.mp3 |

  @_file_upload
  Scenario: Add custom thumbnail to a video in TinyMCE editor
    Given I log in as "admin"
    And I open my profile in edit mode
    And I click on the "Multimedia" button for the "Description" TinyMCE editor
    And I click on "Browse repositories" "button" in the "Insert media" "dialogue"

    And I upload "/lib/editor/tiny/tests/behat/fixtures/moodle-logo.mp4" to the file picker for TinyMCE
    When I click on "Add custom thumbnail" "button" in the "Media details" "dialogue"
    Then "Insert media thumbnail" "dialogue" should exist
    And I click on "Browse repositories" "button" in the "Insert media thumbnail" "dialogue"
    And I upload "lib/editor/tiny/tests/behat/fixtures/moodle-logo.png" to the file picker for TinyMCE
    And "Media thumbnail" "dialogue" should exist
    And "tiny-media-thumbnail-preview" "region" should exist in the "Media thumbnail" "dialogue"
    And "Delete media thumbnail" "button" should exist in the "Media thumbnail" "dialogue"
    And I click on "Delete media thumbnail" "button" in the "Media thumbnail" "dialogue"
    And "Delete media thumbnail" "dialogue" should exist
    And "Delete" "button" should exist in the "Delete media thumbnail" "dialogue"
    And I click on "Delete" "button" in the "Delete media thumbnail" "dialogue"
    And I click on "Browse repositories" "button" in the "Insert media thumbnail" "dialogue"
    And I upload "lib/editor/tiny/tests/behat/fixtures/moodle-logo.png" to the file picker for TinyMCE
    And I click on "Overwrite" "button" in the "File exists" "dialogue"
    And I click on "Next" "button" in the "Media thumbnail" "dialogue"
    But "Add custom thumbnail" "button" should not exist in the "Media details" "dialogue"
    And "Change thumbnail" "button" should exist in the "Media details" "dialogue"
    And "Delete thumbnail" "button" should exist in the "Media details" "dialogue"
    And I click on "Delete thumbnail" "button" in the "Media details" "dialogue"
    And "Delete thumbnail" "dialogue" should exist
    And "Delete" "button" should exist in the "Delete thumbnail" "dialogue"
    And I click on "Delete" "button" in the "Delete thumbnail" "dialogue"
    And "Media details" "dialogue" should exist
    And "Change thumbnail" "button" should not exist in the "Media details" "dialogue"
    And "Delete thumbnail" "button" should not exist in the "Media details" "dialogue"
    And "Add custom thumbnail" "button" should exist in the "Media details" "dialogue"
    And I click on "Add custom thumbnail" "button" in the "Media details" "dialogue"
    And "Insert media thumbnail" "dialogue" should exist
    And I click on "Browse repositories" "button" in the "Insert media thumbnail" "dialogue"
    And I upload "lib/editor/tiny/tests/behat/fixtures/moodle-logo.png" to the file picker for TinyMCE
    And I click on "Overwrite" "button" in the "File exists" "dialogue"
    And I click on "Next" "button" in the "Media thumbnail" "dialogue"
    And I click on "Save" "button" in the "Media details" "dialogue"
    And I switch to the "Description" TinyMCE editor iframe
    And "//*[contains(@data-id, 'id_description_editor')]//video[contains(@poster, 'moodle-logo.png')]" "xpath_element" should exist

  Scenario: Embed external video link - External video service
    Given the "mediaplugin" filter is "on"
    And I enable "youtube" "media" plugin
    And I disable "videojs" "media" plugin
    Given I log in as "admin"
    And I open my profile in edit mode
    And I click on the "Multimedia" button for the "Description" TinyMCE editor
    When I set the field "Or add via URL" to "https://www.youtube.com/watch?v=JeimE8Wz6e4"
    And I click on "Add" "button" in the "Insert media" "dialogue"
    Then "Media details" "dialogue" should exist
    And "Media title" "field" should exist in the "Media details" "dialogue"
    And "Show controls" "field" should not exist in the "Media details" "dialogue"
    And "Autoplay" "field" should not exist in the "Media details" "dialogue"
    And "Muted" "field" should not exist in the "Media details" "dialogue"
    And "Loop" "field" should not exist in the "Media details" "dialogue"
    And the field "Media title" in the "Media details" "dialogue" matches value "https://www.youtube.com/watch?v=JeimE8Wz6e4"
    And I set the field "Media title" to "Hey, that is pretty good!"
    And I click on "Save" "button" in the "Media details" "dialogue"
    And I switch to the "Description" TinyMCE editor iframe
    And "//*[contains(@data-id, 'id_description_editor')]//a[@class='external-media-provider' and @href='https://www.youtube.com/watch?v=JeimE8Wz6e4' and normalize-space(text())='Hey, that is pretty good!']" "xpath_element" should exist
    And I switch to the main frame
    And I select the "a" element in position "0" of the "Description" TinyMCE editor
    And I click on the "Multimedia" button for the "Description" TinyMCE editor
    And the field "Media title" in the "Media details" "dialogue" matches value "Hey, that is pretty good!"
    And I click on "Cancel" "button" in the "Media details" "dialogue"
    And I press "Update profile"
    And "//span[contains(@class, 'mediaplugin_youtube')]//iframe[@title='Hey, that is pretty good!']" "xpath_element" should exist
