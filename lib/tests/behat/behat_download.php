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
 * @copyright  2024 Simey Lameze <simey@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\ExpectationException;

require_once(__DIR__ . '/../../behat/behat_base.php');

/**
 * Steps definitions to verify a downloaded file.
 *
 * @package    core
 * @category   test
 * @copyright  2024 Simey Lameze <simey@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_download extends behat_base {

    /**
     * Downloads the file from a link on a specific element in the page and verify the content.
     *
     * @Then /^following "(?P<link_string>[^"]*)" in the "(?P<element_container_string>(?:[^"]|\\")*)" "(?P<text_selector_string>[^"]*)" should download a "(?P<format_string>[^"]*)" file that:$/
     * @Then /^following "(?P<link_string>[^"]*)" in the "(?P<element_container_string>(?:[^"]|\\")*)" "(?P<text_selector_string>[^"]*)" should download a "(?P<format_string>[^"]*)" archive that:$/
     *
     * @param string $link the text of the link.
     * @param string $elementcontainer the container element.
     * @param string $textselector the text selector.
     * @param string $format the expected file format.
     * @param TableNode $table the table of assertions to check.
     * @throws ExpectationException
     */
    public function following_in_element_should_download_a_file_that(string $link, string $elementcontainer,
            string $textselector, string $format, TableNode $table): void {

        $this->download_and_validate_file($link, $format, $elementcontainer, $textselector, $table);
    }

    /**
     * Downloads an image file from a link on the page and checks it is valid.
     *
     * @Then /^following "(?P<link_string>[^"]*)" should download a "(?P<format_string>[^"]*)" image file$/
     *
     * @param string $link the text of the link.
     * @param string $format the expected file format.
     * @return void
     */
    public function following_should_download_a_image_file(string $link, string $format): void {
        $this->download_and_validate_file($link, $format);
    }

    /**
     * Downloads a zip file from a link on the page and checks it contains a specific file.
     *
     * @Then /^following "(?P<link_string>[^"]*)" should download a "(?P<format_string>[^"]*)" archive that:$/
     *
     * @param string $link the text of the link.
     * @param string $format the expected file format.
     * @param TableNode $table the table of assertions to check.
     * @return void
     */
    public function following_should_download_a_archive_that(string $link, string $format, TableNode $table): void {
        $this->download_and_validate_file($link, $format, null, null, $table);
    }

    /**
     * Downloads the file from a link on the page and verify the content.

     * @Then /^following "(?P<link_string>[^"]*)" should download a "(?P<format_string>[^"]*)" file that:$/
     *
     * @param string $link the text of the link.
     * @param string $format the expected file format.
     * @param TableNode $table the table of assertions to check.
     */
    public function following_should_download_a_file_that(string $link, string $format, TableNode $table): void {
        $this->download_and_validate_file($link, $format, null, null, $table);
    }

    /**
     * Downloads the file from a link on the page and validate the content.
     *
     * @param string $link the text of the link.
     * @param string $format the expected file format.
     * @param string|null $nodeelement the container element.
     * @param string|null $nodeselectortype the text selector.
     * @param TableNode|null $table the table of assertions to check.
     * @throws ExpectationException
     */
    protected function download_and_validate_file(string $link, string $format, ?string $nodeelement = null,
            ?string $nodeselectortype = null, ?TableNode $table = null): void {

        $behatgeneralcontext = behat_context_helper::get('behat_general');
        $exception = new ExpectationException(
            "Error while downloading data from {$link}",
            $this->getSession(),
        );

        $format = strtolower($format);
        $linkparams = empty($nodeelement) && empty($nodeselectortype)
            ? [$link]
            : [$link, $nodeselectortype, $nodeelement];

        $filecontent = $this->spin(
            fn($context, $link) => $behatgeneralcontext->download_file_from_link(...$linkparams),
            $link,
            behat_base::get_extended_timeout(),
            $exception
        );

        // Images don't need to be checked for content.
        if (in_array($format, ['png', 'jpg', 'gif'])) {
            $this->assert_file_type_image($filecontent, $format);
            return;
        }

        $this->validate_mime_type($filecontent, $format);
        $this->assert_file_content($filecontent, $format, $table);
    }

    /**
     * Asserts the content of the downloaded file.
     *
     * @param string $filecontent the content of the file.
     * @param string $format the expected file format.
     * @param TableNode $table the table of assertions to check.
     * @throws ExpectationException
     */
    private function assert_file_content(string $filecontent, string $format, TableNode $table): void {
        if (!$rows = $table->getRows()) {
            return;
        }

        // Determine the assertion method to use based on the file format.
        // If the format is 'gift' or 'aiken', use the text assertion method.
        $assertmethod = ($format === 'gift' || $format === 'aiken') ? "assert_file_type_text" : "assert_file_type_{$format}";

        foreach ($rows as $row) {
            $assertion = strtolower(trim($row[0]));
            match ($assertion) {
                'contains' => $this->$assertmethod($filecontent, $row[1]),
                default => throw new ExpectationException(
                    "Invalid assertion: {$assertion}",
                    $this->getSession(),
                )
            };
        }
    }

    /**
     * Validates the MIME type of the downloaded file.
     *
     * @param string $filecontent the content of the file.
     * @param string $format the expected file format.
     * @throws ExpectationException
     */
    protected function validate_mime_type(string $filecontent, string $format): void {

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimetype = $finfo->buffer($filecontent);

        $validmimetypes = match ($format) {
            'zip' => ['application/zip', 'application/x-zip-compressed', 'multipart/x-zip'],
            'xml' => ['application/xml', 'text/xml'],
            'text' => ['text/plain'],
            'gift' => ['text/plain'],
            'aiken' => ['text/plain'],
            'png' => ['image/png'],
            default => [$format], // If the format is not one of the above, assume it is the only valid MIME type
        };

        if (!in_array($mimetype, $validmimetypes)) {
            throw new ExpectationException(
                "The file downloaded should have been a {$format} file, but got {$mimetype} instead.",
                $this->getSession(),
            );
        }
    }

    /**
     * Asserts that the given XML file is valid and contains the expected string.
     *
     * @param string $filecontent the content of the file.
     * @param string $contains the string to search for.
     * @throws ExpectationException
     */
    protected function assert_file_type_xml(string $filecontent, string $contains): void {

        // Load the XML content into a SimpleXMLElement object
        $xml = new SimpleXMLElement($filecontent);

        // Use xpath to search for the string in the XML content
        $result = $xml->xpath("//*[contains(text(), '$contains')]");

        // If the result is empty, the string was not found in the XML content
        if (empty($result)) {
            throw new ExpectationException(
                "The string '{$contains}' was not found in the XML content.",
                $this->getSession(),
            );
        }
    }

    /**
     * Assert that the given image file is valid.
     *
     * @param string $filecontent the content of the file.
     * @param string $format the expected file format.
     * @throws ExpectationException
     */
    protected function assert_file_type_image(string $filecontent, string $format): void {
        // First validate the MIME type.
        $this->validate_mime_type($filecontent, $format);

        // Then perform additional image-specific validations.
        $tempdir = make_request_directory();
        $filepath = $tempdir . '/downloaded.' . $format;
        file_put_contents($filepath, $filecontent);

        if (!getimagesize($filepath)) {
            throw new ExpectationException(
                "The file downloaded does not appear to be a valid {$format} image.",
                $this->getSession(),
            );
        }
    }

    /**
     * Asserts that the given zip archive contains the expected file.
     *
     * @param string $filecontent the content of the file.
     * @param string $expectedfile the name of the file to search for in the zip archive.
     * @throws ExpectationException
     */
    protected function assert_file_type_zip(string $filecontent, string $expectedfile): void {

        // Save the file to disk.
        $tempdir = make_request_directory();
        $filepath = $tempdir . '/downloaded.zip';
        file_put_contents($filepath, $filecontent);

        $zip = new ZipArchive();
        $res = $zip->open($filepath);

        if ($res !== true) {
            throw new ExpectationException(
                "Failed to open zip file.",
                $this->getSession(),
            );
        }

        // Check if the expected file exists in the zip archive.
        if ($zip->locateName($expectedfile) === false) {
            throw new ExpectationException(
                "The file '{$expectedfile}' was not found in the zip archive.",
                $this->getSession(),
            );
        }
    }

    /**
     * Asserts that the given string is present in the file content.
     *
     * @param string $filecontent the content of the file.
     * @param string $contains the string to search for.
     * @throws ExpectationException
     */
    protected function assert_file_type_text(string $filecontent, string $contains): void {

        // Check if the string is present in the file content.
        if (!str_contains($filecontent, $contains)) {
            throw new ExpectationException(
                "The string '{$contains}' was not found in the file content.",
                $this->getSession(),
            );
        }
    }
}
