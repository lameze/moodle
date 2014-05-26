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
 * Task to monitor events and process them.
 *
 * @package    report_monitor
 * @copyright  2014 onwards Ankit Agarwal <ankit.agrr@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_monitor\task;

defined('MOODLE_INTERNAL') || die();

/**
 * Task to monitor events and process them.
 *
 * @package    report_monitor
 * @copyright  2014 onwards Ankit Agarwal <ankit.agrr@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class process_events_task extends \core\task\scheduled_task{

    public function get_name() {
        // Shown in admin screens.
        return get_string('processevents', 'report_monitor');
    }

    public function execute() {
        $filtermanger = new \report_monitor\filter_manager();

        // Setup the log reader.
        $logmanager = get_log_manager();
        $readers = $logmanager->get_readers('\core\log\sql_select_reader');
        $reader = reset($readers); // Use the preferred reader.

        if (empty($reader)) {
            // No readers, nothing to process.
            return true;
        }

        // Get the events.
        $since = $this->get_last_run_time();
        if (empty($since)) {
            // Default to now, if last run not set.
            return true;
        }

        $selectwhere = "timecreated >= :since";
        $params = array('since' => $since);
        $events = $reader->get_events_select($selectwhere, $params);
        foreach($events as $event) {
            // Process each event.
        }

        // Process events.
        $filtermanger->dispose();
        return true;
    }
}
