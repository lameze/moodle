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
 * Mailpit mail handling implementation.
 *
 * @package    core
 * @category   test
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright Simey Lameze <simey@moodle.com>
 */
namespace core\test;

use core\http_client;

/**
 * Mailpit email handling class.
 *
 * @package    core
 * @category   test
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mailpit_email_catcher implements email_catcher {

    /** @var http_client The http client object */
    protected $httpclient;

    /**
     * Constructor.
     *
     * @param string $baseuri The base uri for the mailpit server.
     */
    public function __construct(string $baseuri) {
        $this->httpclient = new http_client(['base_uri' => $baseuri]);
    }


    /**
     * Reset the mailpit server after a test.
     */
    public function reset_after_test(): void {
        $this->httpclient->delete_all();
    }

    /**
     * Delete a message from the mailpit server.
     *
     * @param array|null $ids The message ids.
     * @return void
     */
    public function delete(?array $ids = null): bool {
        $response = $this->httpclient->delete('api/v1/messages', [
            'json' => ['ids' => $ids]
        ]);

        return json_decode($response->getBody());
    }

    /**
     * Delete all messages from the mailpit server.
     *
     * @return void
     */
    public function delete_all() {
        $this->httpclient->delete( 'api/v1/messages');
    }

    /**
     * Get the message summary for a specific message.
     *
     * @param string $id The message id.
     * @return mixed
     */
    public function get_message_summary(string $id): stdClass {
        $response = $this->httpclient->get("api/v1/message/{$id}");

        return json_decode($response->getBody());
    }

    /**
     * Get a list of messages from the mailpit server.
     *
     * @return mixed
     */
    public function list(): \Generator {
        $uri = 'api/v1/messages';

        while ($uri !== null) {
            $response = $this->httpclient->get($uri);

            $data = json_decode($response->getBody());

            yield mailpit_message::create_from_api_response($data);
            $uri = $data->next_page ?: null;

        }
    }

    /**
     * Search for a message in the mailpit server.
     *
     * @param string $query The search query.
     * @return mixed
     */
    public function search(string $query): \Iterator {
        $uri = "api/v1/search?query={$query}";

        while ($uri !== null) {
            $response = $this->httpclient->get($uri);

            $data = json_decode($response->getBody());

            yield mailpit_message::create_from_api_response($data);
            $uri = $data->next_page ?: null;

        }
    }

    /**
     * Set the read status of a message (read, unread).
     *
     * @param array $ids The message ids.
     * @param bool $status The status to set.
     */
    public function set_read_status(array $ids, bool $status): void {
        $params = [
            'ids' => $ids,
            'read' => $status
        ];

        $this->httpclient->put('api/v1/messages', ['json' => $params]);
    }
}
