<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Steps definitions to verify sent emails.
 *
 * @package    core
 * @category   test
 * @copyright  2023 Simey Lameze <simey@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use Behat\Mink\Exception\ExpectationException;
use Behat\Gherkin\Node\TableNode as TableNode;
use Moodle\BehatExtension\Exception\SkippedException;

require_once(__DIR__ . '/../../behat/behat_base.php');

/**
 * Steps definitions to assist with email testing.
 *
 * @package    core
 * @category   test
 * @copyright  2023 Simey Lameze <simey@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_email extends behat_base {

    /** @var \core\mailpit The email catcher object */
    private $emailcatcher;

    /**
     * Check if a email catcher is configured.
     *
     * @Given /^a email catcher server is configured$/
     */
    public function emailcatcher_is_configured(): void {
        if (!defined('TEST_EMAILCATCHER_SERVER')) {
            throw new SkippedException(
                'The TEST_EMAILCATCHER_SERVER constant must be defined in config.php to use the mailcatcher steps.'
            );
        }

        // Initialise the email catcher.
        $this->emailcatcher = new \core\mailpit(TEST_EMAILCATCHER_SERVER);
    }

    /**
     * Clean up the email inbox after each scenario.
     *
     * @AfterScenario
     */
    public function clean_up_email_inbox() {
        if (empty($this->emailcatcher)) {
            return;
        }

        $this->emailcatcher->delete_all();
    }

    /**
     * Custom Behat test to verify an email with a specific subject for a user.
     *
     * @Given the email to :user with subject containing :subject should contain :content
     *
     * @param string $user The user to check for.
     * @param string $subject The subject to check for.
     * @param string $content The content to check for.
     */
    public function verify_email_content(string $user, string $subject, string $content) {
        $list = $this->emailcatcher->list();
        $id = $list->messages[0]->ID;
        $summary = $this->emailcatcher->get_message_summary($id);

        $this->validate([
            'expecteduser' => $user,
            'actualuser' => $summary->To[0]->Address,
            'expectedsubject' => $subject,
            'actualsubject' => $summary->Subject,
            'expectedcontent' => $content,
            'actualcontent' => $summary->Text,
        ]);
    }

    /**
     * Custom Behat test to verify the number of emails for a user.
     *
     * @Then user :user should have :count emails
     *
     * @param int $count The number of emails to check for.
     */
    public function verify_email_count(string $user, int $count) {
        $emails = $this->emailcatcher->list();

        $emailcount = 0;
        foreach ($emails->messages as $message) {
            $this->validate(['expecteduser' => $user, 'actualuser' => $message->To[0]->Address]);
            $emailcount++;
        }

        $this->validate(['expectedcount' => $count, 'actualcount' => $emailcount]);
    }

    /**
     * Custom Behat test to empty the email inbox.
     *
     * @When I empty the email inbox
     */
    public function empty_email_inbox() {
        $this->emailcatcher->delete_all();
    }

    /**
     * Custom Behat test to delete an email.
     *
     * @When I delete the email to :user with subject containing :subject
     * @param string $user The user to check for.
     * @param string $subject The subject to check for.
     */
    public function delete_email(string $user, string $subject) {
        $list = $this->emailcatcher->search($subject);
        $id = $list->messages[0]->ID;
        $summary = $this->emailcatcher->get_message_summary($id);

        $this->validate([
            'expecteduser' => $user,
            'actualuser' => $summary->To[0]->Address,
            'expectedsubject' => $subject,
            'actualsubject' => $summary->Subject,
        ]);

        $this->emailcatcher->delete([$id]);
    }

    /**
     * Custom Behat test to mark an email as read.
     *
     * @When I mark the email to :user with subject containing :subject as read
     *
     * @param string $user The user to check for.
     * @param string $subject The subject to check for.
     */
    public function mark_as_read(string $user, string $subject) {
        $list = $this->emailcatcher->search($subject);

        if (empty($list->messages)) {
            throw new ExpectationException("No messages found with subject containing {$subject}", $this->getSession()->getDriver());
        }

        $id = $list->messages[0]->ID;
        $summary = $this->emailcatcher->get_message_summary($id);

        $this->validate([
            'expecteduser' => $user,
            'actualuser' => $summary->To[0]->Address,
            'expectedsubject' => $subject,
            'actualsubject' => $summary->Subject,
        ]);

        $this->emailcatcher->update_status([$id], true);
    }

    /**
     * Custom Behat test to mark an email as unread.
     *
     * @When I mark the email to :user with subject containing :subject as unread
     *
     * @param string $user The user to check for.
     * @param string $subject The subject to check for.
     */
    public function mark_as_unread(string $user, string $subject) {
        $list = $this->emailcatcher->list();
        $id = $list->messages[0]->ID;
        $summary = $this->emailcatcher->get_message_summary($id);

        $this->validate([
            'expecteduser' => $user,
            'actualuser' => $summary->To[0]->Address,
            'expectedsubject' => $subject,
            'actualsubject' => $summary->Subject,
        ]);

        $this->emailcatcher->update_status([$id], false);
    }

    /**
     * Custom behat step to mark all messages as read or unread.
     *
     * @When I mark all messages as :status
     *
     * @param string $status The status to mark all messages as.
     */
    public function mark_all_messages_as(string $status) {
        $status = ($status === 'read') ? true : false;

        $this->emailcatcher->update_status([], $status);
    }

    /**
     * Custom Behat test to check if an email is marked as read or unread.
     *
     * @Then the email to :user with subject containing :subject should be marked as :status
     *
     * @param string $user The user to check for.
     * @param string $subject The subject to check for.
     * @param string $status The status to check for.
     */
    public function verify_email_status(string $user, string $subject, string $status) {
        $list = $this->emailcatcher->search($subject);

        $id = $list->messages[0]->ID;
        $summary = $this->emailcatcher->get_message_summary($id);

        $this->validate([
            'expecteduser' => $user,
            'actualuser' => $summary->To[0]->Address,
            'expectedsubject' => $subject,
            'actualsubject' => $summary->Subject,
            'expectedstatus' => $status == 'read' ? 1 : 0,
            'actualstatus' => (int) $list->messages[0]->Read,
        ]);
    }

    /**
     * Behat step to send emails.
     *
     * @Given the following emails have been sent:
     *
     * @param TableNode $table The table of emails to send.
     */
    public function the_following_emails_have_been_sent(TableNode $table) {
        if (!$rows = $table->getRows()) {
            return;
        }

        // Allowed fields.
        $allowedfields = ['to', 'subject', 'message'];

        // Create a map of header to index.
        $headers = array_flip($rows[0]);
        // Remove header row.
        unset($rows[0]);

        // Validate supplied headers.
        foreach ($headers as $header => $index) {
            if (!in_array($header, $allowedfields)) {
                throw new ExpectationException("Invalid header {$header} found in table", $this->getSession()->getDriver());
            }
        }

        foreach ($rows as $row) {
            // Check if the required headers are set in the $headers map.
            $to = isset($headers['to']) ? $row[$headers['to']] : 'userto@example.com';
            $subject = isset($headers['subject']) ? $row[$headers['subject']] : 'Default test subject';
            $message = isset($headers['message']) ? $row[$headers['message']] : 'Default test message';

            // Use no-reply user as dummy user to send emails from.
            $noreplyuser = \core_user::get_user(\core_user::NOREPLY_USER);

            // Create a dummy user to send emails to.
            $emailuserto = new stdClass();
            $emailuserto->id = -99;
            $emailuserto->email = $to;
            $emailuserto->firstname = 'Test';
            $emailuserto->lastname = 'User';

            // Send test email.
            email_to_user($emailuserto, $noreplyuser, $subject, $message);
        }
    }

    /**
     * Validate the emails expected and actual values.
     *
     * @param array $data The data to validate.
     */
    protected function validate(array $data) {

        $validations = ['user', 'subject', 'status', 'count', 'content'];

        foreach ($validations as $key) {
            if (array_key_exists('expected' . $key, $data) && array_key_exists('actual' . $key, $data)) {

                $expected = $data['expected' . $key];
                $actual = $data['actual' . $key];

                if (($key === 'subject' || $key === 'content') && !str_contains($actual, $expected)) {
                    throw new ExpectationException(sprintf('Expected %s %s to contain %s, but it does not', $key, $actual, $expected), $this->getSession());
                } elseif ($key !== 'subject' && $key !== 'content' && $expected != $actual) {
                    throw new ExpectationException(sprintf('Expected %s %s, but found %s', $expected, $key, $actual), $this->getSession());
                }
            }
        }
    }
}
