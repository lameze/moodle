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
 * Unit tests for core_calendar\filter\update web service;
 *
 * @package   core_calendar
 * @category  test
 * @copyright 2020 Simey Lameze <simey@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU Public License
 */

declare(strict_types = 1);

namespace core_calendar\external\filter;
global $CFG;


use advanced_testcase;
use coding_exception;
use external_api;
use moodle_exception;

/**
 * Unit tests for core_calendar\filter\update web service;
 *
 * @package   core_calendar
 * @category  test
 * @copyright 2020 Simey Lameze <simey@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU Public License
 */
class update_test extends advanced_testcase {
    public static function setupBeforeClass(): void {
        global $CFG;

        require_once("{$CFG->dirroot}/calendar/lib.php");
        require_once("{$CFG->libdir}/externallib.php");
    }

    public function test_execute_invalid_eventkey(): void {
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $this->expectException(moodle_exception::class);
        $this->expectExceptionMessage("The provided event type 'personal' is invalid.");

        update::execute('personal', true);
    }

    public function test_execute_hide_user_events(): void {
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        // Let's enable calendar_persistflt setting to keep track of user preferences.
        set_user_preference('calendar_persistflt', 1, $user);

        // User has choosen to not see user events.
        update::execute('user', true);

        $this->assertNotNull(calendar_show_event_type(CALENDAR_EVENT_USER, $user));
    }
}