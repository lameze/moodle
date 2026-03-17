@javascript
Feature: Additional HTML footer content and scripting
  In order to verify Additional HTML footer behavior
  As an admin
  I need to confirm script execution and footer popover rendering

  Scenario: Admin sets additionalhtmlfooter and sees alert, injected text, and bold footer popover text
    Given I log in as "admin"
    And I navigate to "Appearance > Additional HTML" in site administration
    And I set the field "additionalhtmlfooter" to multiline:
      """
      <strong>Bold test text</strong><br>
      <script>
      alert('Oh look, an alert!');
      window.onload = () => {
          let testtext = document.createElement('div');
          testtext.innerHTML = 'Test bottom of page text';
          document.getElementById('page-footer').appendChild(testtext);
      }
      </script>
      """
    When I press "Save changes"
    Then I should see "Oh look, an alert!" in current page dialog
    And I accept current page dialog
    And I should see "Test bottom of page text"
    When I click on "Display page footer" "button"
    Then I should see "Bold test text"
    And I should see "Bold test text" in the "strong" "css_element"
