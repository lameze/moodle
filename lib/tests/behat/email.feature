@behat_test @behat_email
Feature: Testing Email Functionality

  Background:
    Given a email catcher server is configured
    And the following config values are set as admin:
      | smtphosts       | 0.0.0.0:1025 |

  Scenario: Verifying email content to user
    When the following emails have been sent:
      | to                   | subject           | message                |
      | student1@example.com | A testing subject | This is a test message |
    Then the email to "student1@example.com" with subject containing "A testing subject" should contain "This is a test message"

  Scenario: Delete email
    When the following emails have been sent:
      | to                   | subject   |
      | student1@example.com | Apple     |
      | student1@example.com | Banana    |
      | student1@example.com | Chocolate |
    And user "student1@example.com" should have 3 emails
    And I delete the email to "student1@example.com" with subject containing "Banana"
    And user "student1@example.com" should have 2 emails
    And I delete the email to "student1@example.com" with subject containing "Chocolate"
    And user "student1@example.com" should have 1 emails
    And I delete the email to "student1@example.com" with subject containing "Apple"
    And user "student1@example.com" should have 0 emails

  Scenario: Test emptying the email inbox
    When the following emails have been sent:
      | to                   | subject   |
      | student1@example.com | Apple     |
      | student1@example.com | Banana    |
      | student1@example.com | Chocolate |
    Then user "student1@example.com" should have 3 emails
    And I empty the email inbox
    And user "student1@example.com" should have 0 emails

  Scenario: Mark as read
    When the following emails have been sent:
      | to                   | subject   |
      | student1@example.com | Apple     |
      | student1@example.com | Banana    |
      | student1@example.com | Chocolate |
    And I mark the email to "student1@example.com" with subject containing "Chocolate" as read
    Then the email to "student1@example.com" with subject containing "Apple" should be marked as "unread"
    And the email to "student1@example.com" with subject containing "Banana" should be marked as "unread"
    And the email to "student1@example.com" with subject containing "Chocolate" should be marked as "read"

  Scenario: Mark all messages as read and unread
    When the following emails have been sent:
      | to                   | subject   |
      | student1@example.com | Apple     |
      | student1@example.com | Banana    |
      | student1@example.com | Chocolate |
    And I mark all messages as "read"
    Then the email to "student1@example.com" with subject containing "Apple" should be marked as "read"
    And the email to "student1@example.com" with subject containing "Banana" should be marked as "read"
    And the email to "student1@example.com" with subject containing "Chocolate" should be marked as "read"
    And I mark all messages as "unread"
    And the email to "student1@example.com" with subject containing "Apple" should be marked as "unread"
    And the email to "student1@example.com" with subject containing "Banana" should be marked as "unread"
    And the email to "student1@example.com" with subject containing "Chocolate" should be marked as "unread"
