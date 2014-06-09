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
 * Observer to monitor events.
 *
 * @package    report_monitor
 * @copyright  2014 onwards Ankit Agarwal <ankit.agrr@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_monitor;

defined('MOODLE_INTERNAL') || die();

/**
 * Task to monitor events and process them.
 *
 * @package    report_monitor
 * @copyright  2014 onwards Ankit Agarwal <ankit.agrr@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class process_events_observer {

    public static function process_event(\core\event\base $event) {
        $filtermanger = new filter_manager();
        $filters = $filtermanger->get_filters();

        // Get the events.
        $subscriptions = array(); // Get it from subscription manager.
        $rules = \report_monitor\rule_manager::get_rules_by_event($event);
        foreach ($subscriptions as $sub) {
            $sendmsg = true;
            if ($sub->cmid === 0 || ($sub->cmid === $event->contextinstanceid && $event->contextlevel = CONTEXT_MODULE)) {
                foreach ($filters as $filter) {
                    if (!$filter->process_event($event, $sub)) {
                        // One of the filters are not satisfied. So no message should be sent.
                        $sendmsg = false;
                    }
                }
            }

            if ($sendmsg) {
                self::send_notification($event, $sub);
            }
        }

        // Process events.
        $filtermanger->dispose();
        return true;
    }

    public static function send_notification(\core\event\base $event, \stdClass $subscription) {

    }
}
