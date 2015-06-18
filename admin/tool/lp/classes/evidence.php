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
 * Class for loading/storing evidence of prior learning.
 *
 * @package    tool_lp
 * @copyright  2015 Simey Lameze <lameze@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lp;

use stdClass;

/**
 * Class for loading/storing evidence of prior learning from the DB.
 *
 * @copyright  2015 Simey Lameze <lameze@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class evidence extends persistent {

    /** @var string $name Name for this evidence */
    private $name = '';

    /** @var string $description Description for this evidence */
    private $description = '';

    /** @var string $description Description format for this evidence */
    private $descriptionformat = '';

    /** @var string $userid The user who submitted the evidence */
    private $userid = 0;

    /** @var string $url Link of the evidence */
    private $url = '';

    /** @var string $fileattachments file attachments of the evidence */
    private $fileattachments = '';

    /** @var int $timecreated Time of evidence creation */
    private $timecreated = 0;

    /** @var int $timemodified Evidence last modified date  */
    private $timemodified = 0;

    /** @var int $usermodified The user that has modified the evidence */
    private $usermodified = 0;

    /**
     * Method that provides the table name matching this class.
     *
     * @return string
     */
    public function get_table_name() {
        return 'tool_lp_evidence';
    }

    /**
     * Get the evidence name.
     *
     * @return string The name
     */
    public function get_name() {
        return $this->name;
    }

    /**
     * Set the evidence name.
     *
     * @param string $name The evidence name
     */
    public function set_name($name) {
        $this->name = $name;
    }

    /**
     * Get the evidence description.
     *
     * @return string The evidence description
     */
    public function get_description() {
        return $this->description;
    }

    /**
     * Set the evidence description.
     *
     * @param string $description The evidence description.
     */
    public function set_description($description) {
        $this->description = $description;
    }

    /**
     * Get the description format.
     *
     * @return string The description format
     */
    public function get_descriptionformat() {
        return $this->descriptionformat;
    }

    /**
     * Set the description format.
     *
     * @param string $descriptionformat The description format.
     */
    public function set_descriptionformat($descriptionformat) {
        $this->descriptionformat = $descriptionformat;
    }

    /**
     * Get the evidence link.
     *
     * @return string The evidence link.
     */
    public function get_url() {
        return $this->url;
    }

    /**
     * Set the evidence link.
     *
     * @param string $url The evidence link.
     */
    public function set_url($url) {
        $this->url = $url;
    }

    /**
     * Get the time created.
     *
     * @return int The time created.
     */
    public function get_timecreated() {
        return $this->timecreated;
    }

    /**
     * Set the time created.
     *
     * @param int $timecreated The time created.
     */
    public function set_timecreated($timecreated) {
        $this->timecreated = $timecreated;
    }

    /**
     * Get the time modified.
     *
     * @return int $timemodified The time modified.
     */
    public function get_timemodified() {
        return $this->timemodified;
    }

    /**
     * Set the time modified.
     *
     * @param int $timemodified The time modified.
     */
    public function set_timemodified($timemodified) {
        $this->timemodified = $timemodified;
    }

    /**
     * Get the file attachments.
     *
     * @return string The file attachments.
     */
    public function get_fileattachments() {
        return $this->fileattachments;
    }

    /**
     * Set the file attachments.
     *
     * @param string $fileattachments The file attachments.
     */
    public function set_fileattachments($fileattachments) {
        $this->fileattachments = $fileattachments;
    }


    /**
     * Get the user id.
     *
     * @return int $userid The user id.
     */
     public function get_userid() {
         return $this->userid;
     }

    /**
     * Set the user id.
     *
     * @param int $userid The user id.
     */
    public function set_userid($userid) {
        $this->userid = $userid;
    }

    /**
     * Get the user id from the user who changed the evidence.
     *
     * @return int $usermodified The user modified id.
     */
    public function get_usermodified() {
        return $this->usermodified;
    }

    /**
     * Set the user id from the user who have approved the evidence.
     *
     * @param int $usermodified The user modified id.
     */
    public function set_usermodified($usermodified) {
        $this->usermodified = $usermodified;
    }
    /**
     * Populate this class with data from a DB record.
     *
     * @param stdClass $record A DB record.
     * @return template
     */
    public function from_record($record) {
        if (isset($record->id)) {
            $this->set_id($record->id);
        }
        if (isset($record->name)) {
            $this->set_name($record->name);
        }
        if (isset($record->description)) {
            $this->set_description($record->description);
        }
        if (isset($record->descriptionformat)) {
            $this->set_descriptionformat($record->descriptionformat);
        }
        if (isset($record->userid)) {
            $this->set_userid($record->userid);
        }
        if (isset($record->url)) {
            $this->set_url($record->url);
        }
        if (isset($record->fileattachments)) {
            $this->set_fileattachments($record->fileattachments);
        }
        if (isset($record->timecreated)) {
            $this->set_timecreated($record->timecreated);
        }
        if (isset($record->timemodified)) {
            $this->set_timemodified($record->timemodified);
        }
        if (isset($record->usermodified)) {
            $this->set_usermodified($record->usermodified);
        }

        return $this;
    }

    /**
     * Create a DB record from this class.
     *
     * @return stdClass The evidence object.
     */
    public function to_record() {
        $record = new stdClass();
        $record->id = $this->get_id();
        $record->name = $this->get_name();
        $record->description = $this->get_description();
        $record->descriptionformat = $this->get_descriptionformat();
        $record->userid = $this->get_userid();
        $record->url = $this->get_url();
        $record->datecompleted = '';
        $record->fileattachments = $this->get_fileattachments();
        $record->timecreated = $this->get_timecreated();
        $record->timemodified = $this->get_timemodified();
        $record->usermodified = $this->get_usermodified();

        return $record;
    }

    /**
     * Add a default for the sortorder field to the default create logic.
     *
     * @return persistent
     */
    public function create() {
        $this->sortorder = $this->count_records();
        return parent::create();
    }

}
