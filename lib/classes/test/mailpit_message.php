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

namespace core\test;

/**
 * Mailpit message handling implementation.
 *
 * @package    core
 * @category   test
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mailpit_message implements message {

    /**
     * Create a message from an api response.
     *
     * @param \stdClass $message The api response.
     * @return mailpit_message
     */
    public static function create_from_api_response(\stdClass $message): self {

    }

    /**
     * Get the message body.
     *
     * @return string
     */
    public function get_body(): string {

    }

    /**
     * Get the message attachments.
     *
     * @return array
     */
    public function get_attachments(): array {

    }

    /**
     * Get the message recipients.
     *
     * @return array
     */
    public function get_recipients(): array {

    }

    /**
     * Get the message cc recipients.
     *
     * @return array
     */
    public function get_cc_recipients(): array {

    }

    /**
     * Get the message title.
     *
     * @return array
     */
    public function get_title(): string {

    }

    /**
     * Get the message sender.
     *
     * @return array
     */
    public function get_sender(): string {

    }
}
