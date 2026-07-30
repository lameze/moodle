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
 * MDL-89100 diagnostic: maps the wall-clock windows in which the two flaky
 * scheduled-task tests fail.
 *
 * Both tests derive "now" from the real clock and then assert that a task
 * scheduled for hour 0 is NOT due within a small tolerance. That assertion is
 * false for a handful of seconds each day, which is the random CI failure.
 *
 * This script only reads: it never writes to the database and does not need the
 * PHPUnit database to be initialised.
 *
 * Usage:
 *   php mdl89100_repro/check_windows.php
 *   php mdl89100_repro/check_windows.php --timezone=Europe/London
 *
 * @package    core
 * @copyright  2026 Simey Lameze <simey@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../public/config.php');
require_once($CFG->libdir . '/clilib.php');

[$options, $unrecognised] = cli_get_params(
    [
        'help' => false,
        // Moodle's PHPUnit environment always resets $CFG->timezone to Australia/Perth,
        // so that is the timezone the CI failures actually happen in.
        'timezone' => 'Australia/Perth',
    ],
    ['h' => 'help'],
);

if ($unrecognised) {
    cli_error(get_string('cliunknowoption', 'core_admin', implode(PHP_EOL . '  ', $unrecognised)));
}

if ($options['help']) {
    cli_writeln(<<<EOF
Maps the wall-clock windows in which the MDL-89100 flaky tests fail.

Options:
  -h, --help              Print this help.
      --timezone=TZ       Server timezone to evaluate (default: Australia/Perth,
                          which is what Moodle's PHPUnit environment forces).
EOF);
    exit(0);
}

// Emulate the PHPUnit environment's server timezone. advanced_testcase resets
// $CFG->timezone to Australia/Perth for every test, so an installation-specific
// timezone from config.php would give misleading results.
$CFG->timezone = $options['timezone'];
core_date::set_default_server_timezone();
$tz = core_date::get_server_timezone_object();

/**
 * Minimal concrete scheduled task, standing in for core\task\scheduled_test_task.
 *
 * Declared locally so this script does not depend on the unit test fixtures.
 */
$maketask = function (string $minute, string $hour): \core\task\scheduled_task {
    $task = new class extends \core\task\scheduled_task {
        public function get_name() {
            return 'MDL-89100 probe';
        }
        public function execute() {
        }
    };
    $task->set_minute($minute);
    $task->set_hour($hour);
    $task->set_day('*');
    $task->set_month('*');
    $task->set_day_of_week('*');
    return $task;
};

/**
 * Walk every second of a day and report those where the scheduled task is due
 * sooner than the test's tolerance allows.
 *
 * @param callable $maketask
 * @param DateTimeZone $tz
 * @param string $minute Task minute field.
 * @param string $hour Task hour field.
 * @param int $tolerance The offset the test adds to "now" before re-querying.
 * @return string[] Local times (H:i:s) at which the test fails.
 */
function mdl89100_scan(callable $maketask, DateTimeZone $tz, string $minute, string $hour, int $tolerance): array {
    $failing = [];
    // A fixed, arbitrary non-DST-transition date. Only the time of day matters.
    $day = new DateTimeImmutable('2026-08-15 00:00:00', $tz);
    for ($offset = 0; $offset < DAYSECS; $offset++) {
        $now = $day->getTimestamp() + $offset;
        // get_next_scheduled_time() is deterministic for an explicit $now, so this
        // measures the behaviour independently of which clock supplies "now".
        if ($maketask($minute, $hour)->get_next_scheduled_time($now) - $now < $tolerance) {
            $failing[] = $offset;
        }
    }
    return $failing;
}

/**
 * Collapse a sorted list of second-of-day offsets into contiguous ranges.
 *
 * The failing seconds are not one continuous block, so reporting only the first
 * and last would badly overstate the window.
 *
 * @param int[] $offsets Sorted second-of-day offsets.
 * @return array[] List of [start, end] offset pairs, inclusive.
 */
function mdl89100_ranges(array $offsets): array {
    $ranges = [];
    foreach ($offsets as $offset) {
        $last = count($ranges) - 1;
        if ($last >= 0 && $ranges[$last][1] === $offset - 1) {
            $ranges[$last][1] = $offset;
        } else {
            $ranges[] = [$offset, $offset];
        }
    }
    return $ranges;
}

/**
 * Format a second-of-day offset as a local H:i:s time.
 *
 * @param int $offset
 * @return string
 */
function mdl89100_hms(int $offset): string {
    return sprintf('%02d:%02d:%02d', intdiv($offset, HOURSECS), intdiv($offset % HOURSECS, MINSECS), $offset % MINSECS);
}

$cases = [
    [
        'test' => 'core\task\manager_test::test_set_scheduled_task_nextruntime',
        'line' => 'public/lib/tests/task/manager_test.php:687',
        'schedule' => "hour='0', minute='*' (default)",
        'assertion' => 'assertNull(get_next_scheduled_task($clock->time() + 10))',
        'minute' => '*',
        'hour' => '0',
        'tolerance' => 10,
    ],
    [
        'test' => 'core\task\scheduled_task_test::test_get_next_scheduled_task',
        'line' => 'public/lib/tests/task/scheduled_task_test.php:631',
        'schedule' => "hour='0', minute='0'",
        'assertion' => 'assertInstanceOf(scheduled_test2_task, get_next_scheduled_task($now + 120))',
        'minute' => '0',
        'hour' => '0',
        'tolerance' => 120,
    ],
];

cli_writeln('MDL-89100 - wall-clock failure windows');
cli_writeln('=====================================');
cli_writeln('');
cli_writeln('Server timezone under test : ' . $tz->getName());
cli_writeln('Local time right now       : ' . date('H:i:s'));
cli_writeln('');

$innowindow = false;
foreach ($cases as $case) {
    $failing = mdl89100_scan($maketask, $tz, $case['minute'], $case['hour'], $case['tolerance']);

    cli_writeln($case['test']);
    cli_writeln('  ' . $case['line']);
    cli_writeln('  task schedule  : ' . $case['schedule']);
    cli_writeln('  test asserts   : ' . $case['assertion']);
    cli_writeln('  needs          : get_next_scheduled_time(now) - now >= ' . $case['tolerance'] . 's');

    if (!$failing) {
        cli_writeln('  FAILS AT       : never (no window found)');
        cli_writeln('');
        continue;
    }

    $pct = round(count($failing) / DAYSECS * 100, 3);
    $ranges = mdl89100_ranges($failing);
    cli_writeln('  window size    : ' . count($failing) . ' seconds/day (' . $pct . '% of runs)'
        . ', in ' . count($ranges) . ' block(s)');

    // With minute='*' every hour-0 minute contributes its own block, so cap the listing.
    $shown = array_slice($ranges, 0, 4);
    foreach ($shown as $i => $range) {
        $label = $i === 0 ? '  FAILS AT       : ' : '                   ';
        cli_writeln($label . mdl89100_hms($range[0]) . ' .. ' . mdl89100_hms($range[1]));
    }
    if (count($ranges) > count($shown)) {
        $lastrange = end($ranges);
        cli_writeln('                   ... ' . (count($ranges) - count($shown)) . ' more block(s), last is '
            . mdl89100_hms($lastrange[0]) . ' .. ' . mdl89100_hms($lastrange[1]));
    }

    $nowoffset = (int)date('H') * HOURSECS + (int)date('i') * MINSECS + (int)date('s');
    if (in_array($nowoffset, $failing, true)) {
        cli_writeln('  >>> RIGHT NOW IS INSIDE THIS WINDOW <<<');
        $innowindow = true;
    }
    cli_writeln('');
}

cli_writeln('Reference: build W502.01.05 #72 reached scheduled_task_test at 15:58:42 UTC,');
cli_writeln('which is 23:58:42 Australia/Perth - inside the second window above.');

if ($innowindow) {
    cli_writeln('');
    cli_writeln('Run the PHPUnit tests now and they should fail for real.');
}
