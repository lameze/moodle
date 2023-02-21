@core @core_message @javascript
Feature: Message delete conversations
  In order to communicate with fellow users
  As a user
  I need to be able to delete conversations

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | 1        | student1@example.com |
      | student2 | Student   | 2        | student2@example.com |
      | teacher1 | Teacher   | 1        | t1@example.com       |
    And the following "courses" exist:
      | name | shortname |
      | course1 | C1 |
    And the following "course enrolments" exist:
      | user | course | role |
      | student1 | C1 | student |
      | student2 | C1 | student |
      | teacher1 | C1 | editingteacher |
    And the following config values are set as admin:
      | messaging         | 1 |
      | messagingallusers | 1 |
      | messagingminpoll  | 1 |
    And the following "private messages" exist:
      | user     | contact  | message               |
      | student1 | student2 | Hi!                   |
      | student2 | student1 | What do you need?     |

  Scenario: Delete a private conversation
    And I log in as "student2"
    And I open messaging
    And I select "Student 1" conversation in the "messages" conversations list
    And I open contact menu
    And I click on "Delete conversation" "link" in the "//div[@data-region='header-container']" "xpath_element"
#   Confirm deletion, so conversation should not be there
    And I should see "Delete"
    And I click on "//button[@data-action='confirm-delete-conversation']" "xpath_element"
    And I should not see "Delete"
    And I should not see "Hi!" in the "Student 1" "core_message > Message conversation"
    And I should not see "What do you need?" in the "Student 1" "core_message > Message conversation"
    And I should not see "##today##%d %B##" in the "Student 1" "core_message > Message conversation"
#   Check user is deleting private conversation only for them
    And I log out
    And I log in as "student1"
    And I open messaging
    And I select "Student 2" conversation in the "messages" conversations list
    And I should see "Hi!" in the "Student 2" "core_message > Message conversation"
    And I should see "What do you need?" in the "Student 2" "core_message > Message conversation"
    And I should see "##today##%d %B##" in the "Student 2" "core_message > Message conversation"

  Scenario: Cancel deleting a private conversation
    Given I log in as "student1"
    And I open messaging
    And I select "Student 2" conversation in the "messages" conversations list
    And I open contact menu
    And I click on "Delete conversation" "link" in the "//div[@data-region='header-container']" "xpath_element"
#   Cancel deletion, so conversation should be there
    And I should see "Cancel"
    And I click on "//button[@data-action='cancel-confirm']" "xpath_element"
    And I should not see "Cancel"
    And I should see "Hi!" in the "Student 2" "core_message > Message conversation"
    And I should see "##today##%d %B##" in the "Student 2" "core_message > Message conversation"

  Scenario: Delete a starred conversation
    Given the following "favourite conversations" exist:
      | user     | contact  |
      | student1 | student2 |
    And I log in as "student1"
    And I open messaging
    And I select "Student 2" conversation in the "favourites" conversations list
    And I open contact menu
    And I click on "Delete conversation" "link" in the "//div[@data-region='header-container']" "xpath_element"
#   Confirm deletion, so conversation should not be there
    And I should see "Delete"
    And I click on "//button[@data-action='confirm-delete-conversation']" "xpath_element"
    And I should not see "Delete"
    And I should not see "Hi!" in the "Student 2" "core_message > Message conversation"
    And I should not see "What do you need?" in the "Student 2" "core_message > Message conversation"
    And I should not see "##today##%d %B##" in the "Student 2" "core_message > Message conversation"
#   Check user is deleting private conversation only for them
    And I log out
    And I log in as "student2"
    And I open messaging
    And I select "Student 1" conversation in the "messages" conversations list
    And I should see "Hi!" in the "Student 1" "core_message > Message conversation"
    And I should see "What do you need?" in the "Student 1" "core_message > Message conversation"
    And I should see "##today##%d %B##" in the "Student 1" "core_message > Message conversation"

  Scenario: Cancel deleting a starred conversation
    Given the following "favourite conversations" exist:
      | user     | contact  |
      | student1 | student2 |
    When I log in as "student1"
    And I open messaging
    And I select "Student 2" conversation in the "favourites" conversations list
    Then I should see "Hi!" in the "Student 2" "core_message > Message conversation"
    And I should see "##today##%d %B##" in the "Student 2" "core_message > Message conversation"
    And I open contact menu
    And I click on "Delete conversation" "link" in the "//div[@data-region='header-container']" "xpath_element"
#   Cancel deletion, so conversation should be there
    And I should see "Cancel"
    And I click on "//button[@data-action='cancel-confirm']" "xpath_element"
    And I should not see "Cancel"
    And I should see "Hi!" in the "Student 2" "core_message > Message conversation"
    And I should see "##today##%d %B##" in the "Student 2" "core_message > Message conversation"

  Scenario: Check a deleted starred conversation is still starred
    Given the following "favourite conversations" exist:
      | user     | contact  |
      | student1 | student2 |
    When I log in as "student1"
    And I open messaging
    And I select "Student 2" conversation in the "favourites" conversations list
    And I open contact menu
    And I click on "Delete conversation" "link" in the "//div[@data-region='header-container']" "xpath_element"
    Then I should see "Delete"
    And I click on "//button[@data-action='confirm-delete-conversation']" "xpath_element"
    And I should not see "Delete"
    And I should not see "Hi!" in the "Student 2" "core_message > Message conversation"
    And I go back in "view-conversation" message drawer
    And I should not see "Student 2" in the "favourites" "core_message > Message list area"
    And the following "private messages" exist:
      | user     | contact  | message       |
      | student1 | student2 | Hi!           |
    And I open messaging
    And I should see "Student 2" in the "favourites" "core_message > Message list area"

  Scenario: Users without 'deleteanymessage' capability cannot delete message for others
    Given the following "groups" exist:
      | name    | course | idnumber | enablemessaging |
      | Group 1 | C1     | G1       | 1               |
    And the following "group members" exist:
      | user     | group |
      | teacher1 | G1    |
      | student1 | G1    |
    And the following "private messages" exist:
      | user     | contact  | message               |
      | teacher1 | student1 | Hello student1        |
      | teacher1 | student1 | How are you doing?    |
    And I log in as "teacher1"
    And I open messaging
    And I open the "Group" conversations list
    And I select "Group 1" conversation in messaging
    And I send "Hello all students!" message in the message area
    And I open messaging
    And I open the "Private" conversations list
    And I select "Student 1" conversation in the "messages" conversations list
    And I click on "Hello student1" "core_message > Message content"
    When I click on "//button[@data-action='delete-selected-messages']" "xpath_element"
    Then I should see "Are you sure you would like to delete the selected messages? This will not delete them for other conversation participants."
    And I click on "//button[@data-action='confirm-delete-selected-messages']" "xpath_element"
    # Confirm that only selected message was deleted for teacher
    And I should not see "Hello student1"
    And I should see "How are you doing?"
    And I log in as "student1"
    And I open messaging
    And I select "Group 1" conversation in messaging
    # Confirm that all conversations are still there
    And I should see "Hello all students!"
    And I open messaging
    And I select "Teacher 1" conversation in the "messages" conversations list
    And I should see "Hello student1"
    And I should see "How are you doing?"

  Scenario: Users with 'deleteanymessage' capability can delete message for others
    Given the following "groups" exist:
      | name    | course | idnumber | enablemessaging |
      | Group 1 | C1     | G1       | 1               |
    And the following "group members" exist:
      | user     | group |
      | teacher1 | G1    |
      | student1 | G1    |
      | student2 | G1    |
    And the following "private messages" exist:
      | user     | contact  | message       |
      | student1 | teacher1 | Hi Teacher    |
      | student1 | teacher1 | How are you?  |
    And the following "roles" exist:
      | shortname  | name            | archetype |
      | msgdeleter | Message deleter |           |
    And the following "permission overrides" exist:
      | capability                   | permission | role       | contextlevel | reference |
      | moodle/site:deleteanymessage | Allow      | msgdeleter | System       |           |
    And the following "role assigns" exist:
      | user     | role       | contextlevel | reference |
      | teacher1 | msgdeleter | System       |           |
    And I log in as "student1"
    And I open messaging
    And I open the "Group" conversations list
    And I select "Group 1" conversation in messaging
    And I send "Hey everyone" message in the message area
    And I send "We can do it" message in the message area
    And I log out
    And I log in as "teacher1"
    And I open messaging
    And I open the "Private" conversations list
    And I select "Student 1" conversation in the "messages" conversations list
    And I click on "Hi Teacher" "core_message > Message content"
    When I click on "Delete selected messages" "button"
    Then I should see "Are you sure you would like to delete the selected messages?"
    # Delete for me and for everyone else checkbox is not ticked
    And "Delete for me and for everyone else" "checkbox" should be visible
    And I click on "//button[@data-action='confirm-delete-selected-messages']" "xpath_element"
    And I should not see "Hi Teacher"
    And I should see "How are you?"
    And I open messaging
    # Delete for me and for everyone else checkbox is ticked
    And I select "Group 1" conversation in messaging
    And I click on "We can do it" "core_message > Message content"
    And I click on "Delete selected messages" "button"
    And I click on "Delete for me and for everyone else" "checkbox"
    And I click on "//button[@data-action='confirm-delete-selected-messages']" "xpath_element"
    And I open messaging
    And I open the "Group" conversations list
    And I select "Group 1" conversation in messaging
    And I should see "Hey everyone"
    And I should not see "We can do it"
    And I log in as "student1"
    And I open messaging
    And I open the "Group" conversations list
    And I select "Group 1" conversation in messaging
    And I should see "Hey everyone"
    And I should not see "We can do it"
    And I open messaging
    And I select "Teacher 1" conversation in the "messages" conversations list
    # Confirm that no message was not deleted since checkbox was not ticked
    And I should see "Hi Teacher"
    And I should see "How are you?"
    And I open messaging
    And I open the "Group" conversations list
    And I select "Group 1" conversation in messaging
    # Confirm that only selected message was deleted since checkbox was ticked
    And I should see "Hey everyone"
    And I should not see "We can do it"
