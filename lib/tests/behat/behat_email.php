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

use Behat\Gherkin\Node\TableNode as TableNode;
use Behat\Mink\Exception\ExpectationException;
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

    /**
     * Get the email catcher object or thrown a SkippedException if TEST_EMAILCATCHER_SERVER is not defined.
     *
     * @return \core\test\email_catcher
     * @throws SkippedException
     */
    private function get_catcher(): \core\test\email_catcher {
        if (!defined('TEST_EMAILCATCHER_SERVER')) {
            throw new SkippedException(
                'The TEST_EMAILCATCHER_SERVER constant must be defined in config.php to use the mailcatcher steps.',
            );
        }

        return new \core\test\mailpit_email_catcher(TEST_EMAILCATCHER_SERVER);
    }

    /**
     * Clean up the email inbox after each scenario.
     *
     * @AfterScenario
     */
    public function reset_after_test(): void {
        $this->get_catcher()->delete_all();
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
    public function verify_email_content(string $user, string $subject, string $content): void {
        foreach ($this->get_catcher()->list() as $message) {
            if (!$message->has_recipient($user)) {
                continue;
            }

            if (!$message->matches_subject($subject)) {
                continue;
            }

            $this->validate_data('content', $message->get_content(), $content);
        }

        throw new ExpectationException("No messages found with subject containing {$subject}", $this->getSession()->getDriver());
    }

    /**
     * Custom Behat test to verify the number of emails for a user.
     *
     * @Then user :user should have :count emails
     *
     * @param string $user The user to check for.
     * @param int $expected The number of emails to check for.
     */
    public function verify_email_count(string $user, int $expected): void {

        $messages = new \CallbackFilterIterator(
            iterator: $this->get_catcher()->list(),
            callback: fn($message) => $message->has_recipient($user),
        );

        $count = iterator_count($messages);
        if ($count !== $expected) {
            throw new ExpectationException(
                sprintf(
                    'Expected %d messages, but found %d',
                    $expected,
                    $count,
                ),
                $this->getSession(),
            );
        }
    }

    /**
     * Custom Behat test to empty the email inbox.
     *
     * @When I empty the email inbox
     */
    public function empty_email_inbox() {
        $this->get_catcher()->delete_all();
    }

    /**
     * Custom Behat test to delete an email.
     *
     * @When I delete the email to :user with subject containing :subject
     * @param string $user The user to check for.
     * @param string $subject The subject to check for.
     */
    public function delete_email(string $user, string $subject): void {
        $list = $this->get_catcher()->search($subject);
        $id = $list->messages[0]->ID;
        $summary = $this->get_catcher()->get_message_summary($id);

        $this->validate_data(
            'user',
            $user,
            $summary->get_first_recipient(),
        );

        $this->validate_data(
            'subject',
            $subject,
            $summary->get_subject(),
        );

        $this->get_catcher()->delete([$id]);
    }

    /**
     * Custom Behat test to mark an email as read.
     *
     * @When I mark the email to :user with subject containing :subject as read
     *
     * @param string $user The user to check for.
     * @param string $subject The subject to check for.
     */
    public function mark_as_read(string $user, string $subject): void {
        $list = $this->get_catcher()->search($subject);

        if (empty($list->messages)) {
            throw new ExpectationException("No messages found with subject containing {$subject}", $this->getSession()->getDriver());
        }

        $id = $list->messages[0]->ID;
        $summary = $this->get_catcher()->get_message_summary($id);

        $this->validate_data(
            'user',
            $user,
            $summary->get_first_recipient(),
        );

        $this->validate_data(
            'subject',
            $subject,
            $summary->get_subject(),
        );

        $this->get_catcher()->set_read_status([$id], true);
    }

    /**
     * Custom Behat test to mark an email as unread.
     *
     * @When I mark the email to :user with subject containing :subject as unread
     *
     * @param string $user The user to check for.
     * @param string $subject The subject to check for.
     */
    public function mark_as_unread(string $user, string $subject): void {
        $list = $this->get_catcher()->list();
        $id = $list->messages[0]->ID;
        $summary = $this->get_catcher()->get_message_summary($id);

        $this->validate_data(
            'user',
            $user,
            $summary->get_first_recipient(),
        );

        $this->validate_data(
            'subject',
            $subject,
            $summary->get_subject(),
        );

        $this->get_catcher()->set_read_status([$id], false);
    }

    /**
     * Custom behat step to mark all messages as read or unread.
     *
     * @When I mark all messages as :status
     *
     * @param string $status The status to mark all messages as.
     */
    public function mark_all_messages_as(string $status): void {
        $status = ($status === 'read') ? true : false;

        $this->get_catcher()->set_read_status([], $status);
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
    public function verify_email_status(string $user, string $subject, string $status): void {
        $list = $this->get_catcher()->search($subject);

        $id = $list->messages[0]->ID;
        $summary = $this->get_catcher()->get_message_summary($id);

        $this->validate_data(
            'user',
            $user,
            $summary->get_first_recipient(),
        );

        $this->validate_data(
            'subject',
            $subject,
            $summary->get_subject(),
        );

        $this->validate_data(
            'status',
            $subject,
            $status == 'read' ? 1 : 0,
        );
    }

    /**
     * Behat step to send emails.
     *
     * @Given the following emails have been sent:
     *
     * @param TableNode $table The table of emails to send.
     */
    public function the_following_emails_have_been_sent(TableNode $table): void {
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
     * @param string $field The field to validate.
     * @param string $expected The expected value.
     * @param string $actual The actual value.
     */
    private function validate_data(string $field, string $expected, string $actual): void {

        switch ($field) {
            case 'subject':
            case 'content':
                if (!str_contains($actual, $expected)) {
                    throw new ExpectationException(
                        sprintf(
                            'Expected %s %s to contain %s, but it does not',
                            $field,
                            $actual,
                            $expected,
                        ),
                        $this->getSession(),
                    );
                }
                break;
            default:
                throw new ExpectationException(
                    sprintf(
                        'Expected %s %s, but found %s',
                        $expected,
                        $field,
                        $actual,
                    ),
                    $this->getSession(),
                );
        }
    }

}
