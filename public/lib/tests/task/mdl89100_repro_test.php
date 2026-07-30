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

namespace core\task;

/**
 * Deterministic reproduction of the MDL-89100 random CI failures.
 *
 * THIS FILE IS A DIAGNOSTIC, NOT A FIX, AND IS EXPECTED TO FAIL.
 *
 * Both flaky tests derive "now" from the real clock and then assert that a task
 * scheduled for hour 0 is not due within a small tolerance. That holds for most
 * of the day and is false for a few seconds, so the tests pass or fail purely
 * according to what time of day CI happens to reach them.
 *
 * Each test below runs the same body against a frozen clock at several fixed
 * times: the 'safe' data sets pass, the 'bad' ones fail. A failing 'bad' data
 * set means the bug is still present.
 *
 * Run with:
 *   php vendor/bin/phpunit --no-coverage public/lib/tests/task/mdl89100_repro_test.php
 *
 * See mdl89100_repro/check_windows.php for the full failure windows.
 *
 * @package    core
 * @copyright  2026 Simey Lameze <simey@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \core\task\scheduled_task::get_next_scheduled_time
 */
final class mdl89100_repro_test extends \advanced_testcase {

    public static function setUpBeforeClass(): void {
        global $CFG;
        require_once($CFG->dirroot . '/lib/tests/fixtures/task_fixtures.php');
        parent::setUpBeforeClass();
    }

    /**
     * Build a timestamp for a given local time of day.
     *
     * PHPUnit forces $CFG->timezone to Australia/Perth, so "local" here means Perth
     * unless a test has overridden the timezone.
     *
     * @param string $timeofday A H:i:s time, e.g. '00:37:55'.
     * @return int
     */
    private function local_time(string $timeofday): int {
        // A fixed, arbitrary date with no DST transition. Only the time of day matters.
        return (new \DateTimeImmutable("2026-08-15 {$timeofday}", \core_date::get_server_timezone_object()))
            ->getTimestamp();
    }

    /**
     * Release a task's lock so that a failed assertion does not abort the whole run.
     *
     * An unreleased lock throws a coding_exception from lock::__destruct() during
     * PHPUnit's teardown, which PHPUnit reports as "An error occurred inside PHPUnit"
     * and which kills every remaining test in the run. That is the secondary bug that
     * turns this flake into a dead build rather than one red test.
     *
     * @param null|scheduled_task $task
     */
    private function release(?scheduled_task $task): void {
        if ($task && $task->get_lock()) {
            $task->get_lock()->release();
        }
    }

    /**
     * Times of day for the manager_test flake: the assertion allows 10 seconds.
     *
     * @return array[]
     */
    public static function manager_test_provider(): array {
        return [
            'safe - 00:37:05' => ['00:37:05', true],
            'safe - 00:37:45' => ['00:37:45', true],
            'safe - 12:00:00' => ['12:00:00', true],
            'bad  - 00:37:51' => ['00:37:51', false],
            'bad  - 00:37:55' => ['00:37:55', false],
            'bad  - 23:59:59' => ['23:59:59', false],
        ];
    }

    /**
     * Reproduces core\task\manager_test::test_set_scheduled_task_nextruntime.
     *
     * The task is hour='0' with the default minute='*', so inside hour 0 the next
     * scheduled time is simply the next minute boundary. Once fewer than 10 seconds
     * remain in the current minute, the task is already due at $clock->time() + 10
     * and the assertNull() below fails.
     *
     * @dataProvider manager_test_provider
     * @param string $timeofday Frozen local time of day.
     * @param bool $expectedpass Whether the upstream test would pass at this time.
     */
    public function test_manager_test_flake(string $timeofday, bool $expectedpass): void {
        global $DB;

        $this->resetAfterTest();
        $this->preventResetByRollback();
        $clock = $this->mock_clock_with_frozen($this->local_time($timeofday));

        // Disable all the tasks, so we can insert our own and be sure it's the only one being run.
        $DB->set_field('task_scheduled', 'disabled', 1);

        $task = new scheduled_test_task();
        $task->set_month('*');
        $task->set_hour('0');
        $task->set_next_run_time($clock->time() - HOURSECS);
        $DB->insert_record('task_scheduled', manager::record_from_scheduled_task($task));

        $first = manager::get_next_scheduled_task($clock->time());
        $this->assertNotNull($first);
        manager::scheduled_task_complete($first);

        // Diagnostic: how far away did the task get rescheduled to?
        $record = $DB->get_record('task_scheduled', ['classname' => '\core\task\scheduled_test_task']);
        $offset = $record->nextruntime - $clock->time();

        $next = manager::get_next_scheduled_task($clock->time() + 10);
        $this->release($next);

        $message = "frozen at {$timeofday}, task rescheduled {$offset}s ahead, "
            . "test needs >= 10s to pass";
        if ($expectedpass) {
            $this->assertNull($next, "UNEXPECTED: {$message}");
        } else {
            $this->assertNull($next, "MDL-89100 REPRODUCED: {$message}");
        }
    }

    /**
     * Times of day for the scheduled_task_test flake: the assertion allows 120 seconds.
     *
     * @return array[]
     */
    public static function scheduled_task_test_provider(): array {
        return [
            'safe - 12:00:00' => ['12:00:00', true],
            'safe - 23:57:59' => ['23:57:59', true],
            'safe - 23:58:00' => ['23:58:00', true],
            'bad  - 23:58:01' => ['23:58:01', false],
            'bad  - 23:58:42' => ['23:58:42', false],
            'bad  - 23:59:59' => ['23:59:59', false],
        ];
    }

    /**
     * Reproduces core\task\scheduled_task_test::test_get_next_scheduled_task.
     *
     * The tasks are hour='0' minute='0', so after the first one completes its next
     * run time is the next local midnight. Once midnight is less than 120 seconds
     * away, that task also matches the $now + 120 query. get_next_scheduled_task()
     * orders by "lastruntime, id ASC", and Postgres sorts NULLs last in an ASC sort,
     * so the completed scheduled_test_task (lastruntime set) is returned ahead of the
     * failed scheduled_test2_task (lastruntime still NULL) and the assertion fails
     * with "scheduled_test_task is not an instance of scheduled_test2_task".
     *
     * MySQL and MariaDB sort NULLs first, so this particular ordering flip is
     * Postgres-specific, which matches the console output on the tracker.
     *
     * Note this reproduction depends on the MDL-89100 patch being applied: without
     * get_next_scheduled_time() reading the injected clock, freezing the clock has no
     * effect on the rescheduled time at all and the outcome stays non-deterministic.
     *
     * @dataProvider scheduled_task_test_provider
     * @param string $timeofday Frozen local time of day.
     * @param bool $expectedpass Whether the upstream test would pass at this time.
     */
    public function test_scheduled_task_test_flake(string $timeofday, bool $expectedpass): void {
        global $DB;

        $this->resetAfterTest(true);
        $clock = $this->mock_clock_with_frozen($this->local_time($timeofday));

        // Delete all existing scheduled tasks, then add ours, exactly as upstream does.
        $DB->delete_records('task_scheduled');

        $record = new \stdClass();
        $record->blocking = true;
        $record->minute = '0';
        $record->hour = '0';
        $record->dayofweek = '*';
        $record->day = '*';
        $record->month = '*';
        $record->component = 'test_scheduled_task';
        $record->classname = '\core\task\scheduled_test_task';
        $DB->insert_record('task_scheduled', $record);

        $record->classname = '\core\task\scheduled_test2_task';
        $DB->insert_record('task_scheduled', $record);

        $record->classname = '\core\task\scheduled_test3_task';
        $record->disabled = 1;
        $DB->insert_record('task_scheduled', $record);

        $now = $clock->time();

        // Should get handed the first task.
        $task = manager::get_next_scheduled_task($now);
        $this->assertInstanceOf(scheduled_test_task::class, $task);
        $task->execute();
        manager::scheduled_task_complete($task);

        // Should get handed the second task.
        $task = manager::get_next_scheduled_task($now);
        $this->assertInstanceOf(scheduled_test2_task::class, $task);
        $task->execute();
        manager::scheduled_task_failed($task);

        // Should not get any task.
        $task = manager::get_next_scheduled_task($now);
        $this->release($task);
        $this->assertNull($task);

        // Diagnostic: how far away is the first task now scheduled?
        $first = $DB->get_record('task_scheduled', ['classname' => '\core\task\scheduled_test_task']);
        $offset = $first->nextruntime - $now;

        // Should get the second task back (retry after the fail delay). This is the
        // assertion that fails in CI, at scheduled_task_test.php:632.
        $task = manager::get_next_scheduled_task($now + 120);
        $this->release($task);

        $message = "frozen at {$timeofday}, scheduled_test_task rescheduled {$offset}s ahead, "
            . "test needs >= 120s to pass";
        if ($expectedpass) {
            $this->assertInstanceOf(scheduled_test2_task::class, $task, "UNEXPECTED: {$message}");
        } else {
            $this->assertInstanceOf(scheduled_test2_task::class, $task, "MDL-89100 REPRODUCED: {$message}");
        }
    }
}
