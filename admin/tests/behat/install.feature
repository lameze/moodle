@install
  Feature: Moodle web installation

    @javascript
    Scenario: Moodle web installation
      Given I mark this test as slow setting a timeout factor of 4
      And I visit the external url
      And I should see "Installation"
      And I click on "Next »" "button"
      And I should see "Confirm paths"
      And I click on "Next »" "button"

      And I should see "Choose database driver"
      And I set the field "dbtype" to "pgsql"

      And I click on "Next »" "button"

      And I should see "Database settings"
      And I set the field "dbname" to "install_3"
      And I set the field "dbuser" to "postgres"
      And I set the field "dbpass" to "moodle"
      And I click on "Next »" "button"
      And I click on "Continue" "button"
      And I click on "Continue" "button"
      # Modules installation.
      And I click on "Continue" "button"
      And I wait until the page is ready
      And I click on "Click to enter text" "link"
      And I set the field "id_newpassword" to "Simey1234!"
      And I set the field "email" to "simey@moodle.com"
      And I click on "Update profile" "button"
      And I should see "Site home settings"
      And I set the field "Full site name" to "new site"
      And I set the field "Short name for site (eg single word)" to "newsite"
      And I set the field "Support email" to "simey@moodle.com"
      And I set the field "No-reply address" to "simey@moodle.com"
      And I click on "Save changes" "button"
      And I click on "Got it" "button"
      And I should see "Welcome, Admin!"


