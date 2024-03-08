@javascript @_file_upload
Feature: Restore a Moodle 1.9 backup
  As an admin
  I want to restore a Moodle 1.9 backup
  So that I can verify the content of the backup is restored

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |

    Scenario: Restore a Moodle 1.9 without user data
      Given I am on the "Course 1" "restore" page
      And I press "Manage course backups"
      And I upload "backup/moodle2/tests/fixtures/moodle_19_features_demo.zip" file to "Files" filemanager
      And I press "Save changes"
      And I restore "moodle_19_features_demo.zip" backup into a new course using this options:
      | Schema | Course name | Moodle 1.9 features demo |
      And I pause

          # General section.
      And I should see "Moodle 1.9 features demo"
      And I should see "Welcome to the Moodle Features Demo Course" in the "General" "section"
      And I should see "New forum" in the "General" "section"

          # Topic 1 - Assignments.
      And I should see "Assignments book" in the "Topic 1" "section"
      And I should see "Assignments enable teachers to grade and give comments on uploaded files and assignments created on and off line." in the "Topic 1" "section"
      And the "Assignment module documentation" link should exist in topic 1

          # Topic 2 - Chats.
      And I should see "Chats" in the "Topic 2" "section"
      And I should see " The chat module allows participants to have a real-time synchronous discussion via the web." in the "Topic 2" "section"
      And the "Chat module documentation " link should exist in topic 1

          # Topic 3 - Choices.
      And I should see "Choices" in the "Topic 3" "section"
      And I should see "Here a teacher asks a question and specifies a choice of multiple responses."
      And I click on "A choice with anonymous results" "link" in the "Topic 3" "section"
      And I should see "A choice is like a poll. In this case the activity is set to display anonymous results to you after you have made your choice.

      Complete this sentence: So far I think Moodle is _______ " in the "Topic 3" "section"
      And I click on "A choice with non-anonymous results" "link" in the "Topic 3" "section"
      And I should see "When did you first hear of Moodle?"
      And the link "Choice module documentation" should exist in the "Topic 3" "section"

          # Topic 4 - Databases.
      And I should see "Databases" in the "Topic 4" "section"
      And I should see "The database module enables participants to create, maintain and search a bank of record entries." in the "Topic 4" "section"
      And I click on "Image gallery" "link" in the "Topic 4" "section"
      And I should see "This activity uses the Image Gallery preset included in Moodle."
      And I click on "Features Demo" "link"
      And I click on "A database of web links" "link" in the "Topic 4" "section"
      And I should see "In this database, entries require approval by a teacher before they are viewable by everyone. Teachers can rate entries."
      And I click on "Features Demo" "link"
      And the "Database module documentation" link should exist in the "Topic 4" "section"

          # Topic 5 - Forums.
      And I should see "Forums" in the "Topic 5" "section"
      And I should see "Forums provide the opportunity for asynchronous discussions." in the "Topic 5" "section"
      And I click on "A standard forum for general use" "link" in the "Topic 5" "section"
      And I should see "An open forum in which anyone can start a new topic at any time. Teachers can rate posts."
      And I click on "Features Demo" "link"
      And I click on "Each person posts one discussion" "link" in the "Topic 5" "section"
      And I should see "In this forum, each participant can only start one discussion. Everyone can then reply to any of the discussions."
      And I should see "This forum allows each person to start one discussion topic"
      And I click on "Features Demo" "link"
      And I click on "A single discussion" "link" in the "Topic 5" "section"
      And I should see "

      A single discussion
      Friday, 8 March 2024, 4:37 PM
      Number of replies: 0
      There are no separate discussions here - just one thread. Useful for short, focussed discussions.
      "
      And I click on "Features Demo" "link"
      And the "Forum module documentation" link should exist in the "Topic 5" "section"
      And I should see "Click on Forum module documentation to open the resource."
      And I click on "Features Demo" "link"

          # Topic 6 - Glossaries.
      And I should see "Glossaries" in the "Topic 6" "section"
      And I should see "The glossary module enables participants to create and maintain a list of definitions, like a dictionary." in the "Topic 6" "section"
      And I click on "A dictionary-style glossary" "link" in the "Topic 6" "section"
      And I should see "
      Participants can add entries and can comment on all entries.

      Automatic linking is enabled, meaning that entries in this glossary are automatically linked whenever the concept words and phrases appear throughout the rest of the course.
      "
      And I should see "Browse the glossary using this index"
      And I follow "Features Demo"
      And I click on "An FAQ-style glossary" "link" in the "Topic 6" "section"
      And I should see "In this glossary, entries are displayed as questions (concepts) and answers (definitions). Teachers can rate entries."
      And I click on "Glossary module documentation" "link" in the "Topic 6" "section"
      And I should see "Click on Glossary module documentation to open the resource."

          # Topic 7 - Lessons.
      And I should see "Lessons" in the "Topic 7" "section"
      And I should see "Lesson activities can deliver content in interesting and flexible ways. It consists of a number of pages. Each page generally ends with a question and a number of possible answers, and leads to another page based on the student's choice. "
      And I click on "Lesson 1 - Basic pasts" "link" in the "Topic 7" "section"
      And I should see "A Demonstration Lesson"
      And I should see "Opened: Wednesday, 11 February 2004, 12:55 AM"
      And I should see "Closed: Thursday, 11 February 2010, 12:55 AM"
