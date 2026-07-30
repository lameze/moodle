# MDL-89100 — deterministic reproduction of the random scheduled-task failures

Diagnostic material for [MDL-89100](https://moodle.atlassian.net/browse/MDL-89100).

**Nothing in this directory is intended for integration.** It exists to make the
random failure reproducible on demand, and to show that the proposed one-line
change to `scheduled_task::get_next_scheduled_time()` does not fix it.

## TL;DR

The two flaky tests are **time-of-day dependent**. Both take "now" from the real
clock, then assert that a task scheduled for hour 0 is *not* due within a small
tolerance. That is true for most of the day and false for a few seconds, so the
tests pass or fail according to nothing but when CI reaches them.

| Test | Tolerance | Fails at (server local time) | Window |
|---|---|---|---|
| `manager_test::test_set_scheduled_task_nextruntime` | 10s | seconds 51–59 of each minute in hour 0, plus 23:59:51–59 | 540s/day (0.625%) |
| `scheduled_task_test::test_get_next_scheduled_task` | 120s | 23:58:01 – 23:59:59 | 119s/day (0.138%) |

Moodle's PHPUnit environment resets `$CFG->timezone` to **Australia/Perth**
(UTC+8) for every test — see `advanced_testcase::setTimezone()` and the
assertion in `public/lib/phpunit/tests/advanced_test.php`. So "server local
time" above means Perth, not UTC.

### Why the linked build died

Build [W502.01.05 #72](https://ci.moodle.org/job/W502.01.05%20-%20PHPUnit%20-%20Postgres%20-%20Composed/72/consoleText)
started its PHPUnit run at `15:47:14 UTC` and reached `scheduled_task_test` about
11½ minutes later, at **`15:58:42 UTC` = `23:58:42` Perth** — inside the second
window. This is not really a lottery: the weekly job starts at a fixed time of
day, so it lands near 23:58 Perth every week, which is why the same failure keeps
recurring.

The build *aborted* (at test 6490 of 29642, "An error occurred inside PHPUnit")
rather than just going red because `get_next_scheduled_task()` returns a task
holding a cron lock. When the assertion fails, nothing releases it, and
`lock::__destruct()` throws a `coding_exception` from inside PHPUnit's teardown.
One flaky assertion therefore costs the remaining ~23,000 tests. That is a
separate bug from the clock issue and is arguably the more valuable one to fix.

### Why the proposed patch is not the fix

`core\system_clock::time()` is literally `time()`:

```php
public function time(): int {
    return $this->now()->getTimestamp();   // new DateTimeImmutable('now')
}
```

So swapping `time()` for `di::get(clock::class)->time()` changes nothing for any
caller that has not mocked the clock — and
`scheduled_task_test::test_get_next_scheduled_task`, the test in the linked
build, never mocks it. For `manager_test`, `mock_clock_with_frozen()` is called
with **no argument**, and `frozen_clock::__construct(null)` freezes at the real
`time()`, so that test stays wall-clock dependent too.

The change is still worth keeping: it is a genuine prerequisite, because without
it, freezing the clock has no effect on `get_next_scheduled_time()` at all. It
just is not the fix on its own.

### Suggested actual fix

Pin both tests to a fixed time of day instead of chasing the clock source:

```php
$clock = $this->mock_clock_with_frozen(
    (new \DateTimeImmutable('2026-01-01 12:00:00', \core_date::get_server_timezone_object()))->getTimestamp()
);
$now = $clock->time();   // replaces $now = time()
```

Local 12:00 is safely clear of both midnight and hour 0 in any timezone. Note
the flake also exists on `MOODLE_502_STABLE` (the linked job is a 5.2 build), so
whatever lands needs backporting.

## Contents

### `check_windows.php` — needs no PHPUnit database

Maps the failure windows by calling `get_next_scheduled_time()` with an explicit
`$now` for every second of a day. Read-only; touches no tables.

```
php mdl89100_repro/check_windows.php
php mdl89100_repro/check_windows.php --timezone=Europe/London
```

```
core\task\scheduled_task_test::test_get_next_scheduled_task
  public/lib/tests/task/scheduled_task_test.php:631
  task schedule  : hour='0', minute='0'
  test asserts   : assertInstanceOf(scheduled_test2_task, get_next_scheduled_task($now + 120))
  needs          : get_next_scheduled_time(now) - now >= 120s
  window size    : 119 seconds/day (0.138% of runs), in 1 block(s)
  FAILS AT       : 23:58:01 .. 23:59:59
```

It also tells you if the current wall-clock time is inside a window, in which
case the real upstream tests should fail right now.

### `public/lib/tests/task/mdl89100_repro_test.php` — needs the PHPUnit database

Runs each flaky test's body against a frozen clock at six fixed times: three
safe, three inside the failure window. **The three "bad" data sets are expected
to fail — that is the reproduction succeeding.**

```
php vendor/bin/phpunit --no-coverage public/lib/tests/task/mdl89100_repro_test.php
```

Result on pgsql 16, PHP 8.4 — 12 tests, 6 pass, 6 fail:

```
 ✔ Manager test flake with data set "safe - 00:37:05"
 ✔ Manager test flake with data set "safe - 00:37:45"
 ✔ Manager test flake with data set "safe - 12:00:00"
 ✘ Manager test flake with data set "bad  - 00:37:51"
 ✘ Manager test flake with data set "bad  - 00:37:55"
 ✘ Manager test flake with data set "bad  - 23:59:59"
 ✔ Scheduled task test flake with data set "safe - 12:00:00"
 ✔ Scheduled task test flake with data set "safe - 23:57:59"
 ✔ Scheduled task test flake with data set "safe - 23:58:00"
 ✘ Scheduled task test flake with data set "bad  - 23:58:01"
 ✘ Scheduled task test flake with data set "bad  - 23:58:42"
 ✘ Scheduled task test flake with data set "bad  - 23:59:59"
```

The `23:58:42` data set is the exact moment build #72 hit the test, and it
reproduces the tracker's Postgres console output verbatim:

```
MDL-89100 REPRODUCED: frozen at 23:58:42, scheduled_test_task rescheduled 78s ahead, test needs >= 120s to pass
Failed asserting that an instance of class core\task\scheduled_test_task is an instance of class core\task\scheduled_test2_task.
```

The ordering flip that makes `scheduled_test_task` win is Postgres-specific:
`get_next_scheduled_task()` orders by `lastruntime, id ASC`, and Postgres sorts
NULLs **last** in an ASC sort, so the completed task (`lastruntime` set) is
returned ahead of the failed one (`lastruntime` still NULL). MySQL and MariaDB
sort NULLs first, which is why the two databases fail in different tests.

Unlike the upstream tests, this file releases the cron lock before asserting, so
a failure stays a failure instead of aborting the run.
