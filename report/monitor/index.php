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

$id = optional_param('id', 0, PARAM_INT);

if (empty($id)) {
    require_login();
    $context = context_system::instance();
    $coursename = format_string($SITE->fullname, true, array('context' => $context));
    $PAGE->set_context($context);
} else {
    $course = get_course($id);
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
$url = new moodle_url("/report/monitor/index.php", array('id' => $id));

$PAGE->set_url($url);
$PAGE->set_pagelayout('report');
$PAGE->set_title($title);
$PAGE->set_heading($title);

// Site level report.
if (empty($id)) {
    admin_externalpage_setup('reportmonitor', '', null, '', array('pagelayout' => 'report'));
}

echo $OUTPUT->header();
echo $OUTPUT->footer();
