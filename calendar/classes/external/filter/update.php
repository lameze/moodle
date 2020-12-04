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
 * This is the external method for updating events key filter preference.
 *
 * @package    core_calendar
 * @copyright  2020 Simey Lameze <simey@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace core_calendar\external\filter;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/calendar/lib.php');

use context_system;
use external_api;
use external_function_parameters;
use external_single_structure;
use external_warnings;
use external_value;

/**
 * This is the external method for updating events key filter preference.
 *
 * @copyright  2020 Simey Lameze <simey@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class update extends external_api {

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters.
     * @since  Moodle 3.11
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'eventtype' => new external_value(PARAM_ALPHA, 'Event type key to be changed'),
            'hidden' => new external_value(PARAM_BOOL, "Hide or show the event type"),
        ]);
    }

    /**
     * Change the visibility of event key filter.
     *
     * @param string $eventtype The event type key to be changed 'site', 'course, 'category'...
     * @param bool $hidden Whether those events should be hidden or not.
     * @return array The access information
     */
    public static function execute(string $eventtype, bool $hidden) {

        [
            'eventtype' => $eventtype,
            'hidden' => $hidden,
        ] = self::validate_parameters(self::execute_parameters(), [
            'eventtype' => $eventtype,
            'hidden' => $hidden,
        ]);

        if(!calendar_is_valid_eventtype($eventtype)) {
            throw new \moodle_exception("The provided event type '" . $eventtype . "' is invalid.");
        }

        $context = context_system::instance();
        self::validate_context($context);

        // Map event types keyword to CALENDAR_EVENT_* constants.
        // That is required because calendar_set_event_type_display() works with the CALENDAR_EVENT_* constant value and
        // not the event key 'site', 'course', 'user' and etc.
        $eventtypeconst = [
            'site' => CALENDAR_EVENT_SITE,
            'course' => CALENDAR_EVENT_COURSE,
            'group' => CALENDAR_EVENT_GROUP,
            'user' => CALENDAR_EVENT_USER,
            'category' => CALENDAR_EVENT_COURSECAT
        ];

        // Change visibility of the event type.
        calendar_set_event_type_display($eventtypeconst[$eventtype], $hidden);

        return [
            'warnings' => []
        ];
    }

    /**
     * Returns description of method result value.
     *
     * @return external_single_structure.
     * @since  Moodle 3.11
     */
    public static function execute_returns() {
        return new external_single_structure([
            'warnings' => new external_warnings(),
        ]);
    }
}
