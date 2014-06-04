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
 * This file gives an way to delete a rule.
 *
 * @package    report_monitor
 * @copyright  2014 onwards Ankit Agarwal <ankit.agrr@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once('locallib.php');

$confirm = optional_param('confirm', 0, PARAM_BOOL);
$ruleid = optional_param('ruleid', 0, PARAM_INT);

$rule = new \report_monitor\rule($ruleid);
$courseid = $rule->courseid;

if (empty($courseid)) {
    require_login();
    $context = context_system::instance();
    $coursename = format_string($SITE->fullname, true, array('context' => $context));
    $PAGE->set_context($context);
} else {
    $course = get_course($courseid);
    require_login($course);
    $context = context_course::instance($course->id);
    $coursename = format_string($course->fullname, true, array('context' => $context));
}
require_capability('report/monitor:managerules', $context);

// Set up the page.
$a = new stdClass();
$a->coursename = $coursename;
$a->reportname = get_string('pluginname', 'report_monitor');
$title = get_string('title', 'report_monitor', $a);
$url = new moodle_url("/report/monitor/delete.php", array('ruleid' => $ruleid));

$PAGE->set_url($url);
$PAGE->set_pagelayout('report');
$PAGE->set_title($title);
$PAGE->set_heading(get_string('deleterule', 'report_monitor', $rule->get_name($context)));

$redirecturl = new moodle_url($CFG->wwwroot. '/report/monitor/managerules.php', array('id' => $rule->courseid));
$PAGE->navigation->override_active_url($redirecturl);

echo $OUTPUT->header();
if ($confirm && $ruleid) {
    $rule = new \report_monitor\rule($ruleid);
    $rule->delete_rule();
    echo $OUTPUT->notification(get_string('deletesuccess', 'report_monitor'), 'notifysuccess');
    $button = html_writer::tag('button', get_string('goback', 'report_monitor'));
    echo html_writer::link($redirecturl, $button);
} else {
    $deleteurl = new moodle_url($CFG->wwwroot . '/report/monitor/delete.php', array('ruleid' => $rule->id, 'confirm' => 1));
    echo $OUTPUT->confirm(get_string('areyousure', 'report_monitor', $rule->get_name($context)), $deleteurl, $redirecturl);
}
echo $OUTPUT->footer();
