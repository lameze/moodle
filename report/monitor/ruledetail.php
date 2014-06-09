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

$ruleid = optional_param('ruleid', 0, PARAM_INT);
$filtermanager = new report_monitor\filter_manager();

if (!empty($rule->courseid)) {
    $context = context_course::instance($rule->courseid);
} else {
    $context = context_system::instance();
}

$PAGE->set_context($context);
$PAGE->set_pagelayout('base');

$rule = new \report_monitor\rule($ruleid);
$a = new stdClass();
$a->name = $rule->get_name($context);
$a->description = $rule->get_description($context);
$a->plugin = $rule->get_plugin_name();
$a->criteria = $rule->get_filters_description($filtermanager);
echo $OUTPUT->box(get_string('ruledetails_help', 'report_monitor', $a));