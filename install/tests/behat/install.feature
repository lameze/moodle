Feature: Moodle Web Installation

  Scenario: Verify successful installation of Moodle
    Given I am on "http://localhost/install"

    # First page: Choose a language
    And I should see "Installation"
    And I should see "Choose a language"
    And I should see "Please choose a language for the installation. This language will also be used as the default language for the site, though it may be changed later."
    And I press "Next"

    # Second page: paths
    And I should see "Confirm paths"
    And I press "Next"

    # Choose database driver
    And I press "Next"

    # Database settings
    When I fill in "dbhost" with "localhost"
    When I fill in "dbname" with "webinstall5"
    When I fill in "dbuser" with "postgres"
    When I fill in "dbpass" with "moodle"
    And I press "Next"

  # Installation
#  And I should see "Moodle - Modular Object-Oriented Dynamic Learning Environment"
  And I should see "Have you read these conditions and understood them?"
  And I press "Continue"

  # Server checks
  And I should see "Server checks"
  And I press "Continue"

  # All modules installation.
  And I press "Continue"

  # Admin user account.
  And I should see "On this page you should configure your main administrator account"
  And I fill in "email" with "admin@moodle.com"
  And I fill in "newpassword" with "SecureM00dl1ng!"
  And I press "Update profile"

  # Site home settings
  And I fill in "s__fullname" with "site test"
  And I fill in "s__shortname" with "sitetest"
  And I fill in "s__supportemail" with "admin@moodle.com"
  And I fill in "s__noreplyaddress" with "admin@moodle.com"
  And I press "Save changes"

  # Finish.
  And I should see "Welcome, Eloy!"

