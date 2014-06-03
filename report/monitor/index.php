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
 * This file gives an overview of the monitors present in site.
 *
 * @package    report_monitor
 * @copyright  2014 onwards Ankit Agarwal <ankit.agrr@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once('locallib.php');

$courseid = optional_param('id', 0, PARAM_INT);
$copy = optional_param('copy', 0, PARAM_BOOL);
$ruleid = optional_param('ruleid', 0, PARAM_INT);

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
require_capability('report/monitor:view', $context);

// Set up the page.
$a = new stdClass();
$a->coursename = $coursename;
$a->reportname = get_string('pluginname', 'report_monitor');
$title = get_string('title', 'report_monitor', $a);
$url = new moodle_url("/report/monitor/index.php", array('id' => $courseid));

$PAGE->set_url($url);
$PAGE->set_pagelayout('report');
$PAGE->set_title($title);
$PAGE->set_heading($title);

// Site level report.
if (empty($courseid)) {
    admin_externalpage_setup('reportmonitor', '', null, '', array('pagelayout' => 'report'));
}

echo $OUTPUT->header();

if ($copy && $ruleid) {
    require_capability('report/monitor:managerules', $context);
    $rule = new \report_monitor\rule($ruleid);
    $rule->copy_rule($courseid);
    echo $OUTPUT->notification(get_string('copysuccess', 'report_monitor'), 'notifysuccess');
}

//TODO Use rule manager class.
$sql = 'SELECT * FROM {report_monitor_rules} where courseid = ? OR courseid = ?';
$params = array(0, $courseid);
$rules = $DB->get_records_sql($sql, $params);
print_object($rules);
$filtermanager = new \report_monitor\filter_manager();
display_rules($rules, $filtermanager, $courseid, $context);

echo $OUTPUT->footer();
