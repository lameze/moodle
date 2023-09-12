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
 * Generic email catcher interface.
 *
 * @package    core
 * @category   test
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright Simey Lameze <simey@moodle.com>
 */
interface email_catcher {

    /**
     * Delete a message from the mailpit server.
     *
     * @param array $ids The message ids.
     * @return bool
     */
    public function delete(array $ids): bool;

    /**
     * Delete all messages from the mailpit server.
     */
    public function delete_all();

    /**
     * Get the message summary for a specific message.
     *
     * @param string $id The message id.
     * @return stdClass
     */
    public function get_message_summary(string $id): stdClass;

    /**
     * Get a list of messages from the mailpit server.
     *
     * @return \Generator
     */
    public function list(): \Generator;

    /**
     * Search for a message in the mailpit server.
     *
     * @param string $query The search query.
     * @return \Iterator
     */
    public function search(string $query): \Iterator;

    /**
     * Update the status (read, unread) of a message.
     *
     * @param array $ids The message ids.
     * @param bool $status The status to set.
     */
    public function set_read_status(array $ids, bool $status): void;

}
