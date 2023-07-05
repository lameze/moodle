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
 * Steps definitions to verify a downloaded file.
 *
 * @package    core
 * @category   test
 * @copyright  2023 Simey Lameze <simey@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\ExpectationException;
use Moodle\BehatExtension\Exception\SkippedException;

require_once(__DIR__ . '/../../behat/behat_base.php');

/**
 * Steps definitions to verify a downloaded file.
 *
 * @package    core
 * @category   test
 * @copyright  2023 Simey Lameze <simey@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_download extends behat_base {

    /**
     * Downloads the file from a link on the page and checks the size is in a given range.
     *
     * @Then /^following "(?P<link_string>[^"]*)" "(?P<download_element>[^"]*)" should download a file that:$/
     *   | Has size at least | 1000 |
     *   | Has size at most  | 1200 |
     */
    public function following_should_download_a_file_that($link, $element, TableNode $table) {
        $client = new \core\http_client();
        $response = $client->get($link);

        // Check response status code.
        if ($response->getStatusCode() !== 200) {
            throw new ExpectationException('Error while downloading data from ' . $link, $this->getSession());
        }

        $content = $response->getBody()->getContents();

        foreach ($table as $row) {
            $assertion = $row['Contains'];

            switch ($assertion) {
                case 'Has size at least':
                    $minSize = (int)$row[1];
                    if (strlen($content) < $minSize) {
                        throw new ExpectationException('Downloaded file size is less than the expected minimum', $this->getSession());
                    }
                    break;

                case 'Has size at most':
                    $maxSize = (int)$row[1];
                    if (strlen($content) > $maxSize) {
                        throw new ExpectationException('Downloaded file size exceeds the expected maximum', $this->getSession());
                    }
                    break;

                default:
                    throw new InvalidArgumentException('Invalid assertion: ' . $assertion);
            }
        }
    }
}
