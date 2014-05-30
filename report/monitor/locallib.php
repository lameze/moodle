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


function display_rules($rules, $filtermanager, $courseid, $context) {
    $systemcontext = context_system::instance();
    $hassystemcap = has_capability('report/monitor:managerules', $systemcontext);
    $hascurrentcap = has_capability('report/monitor:managerules', $context);
    echo html_writer::start_tag('table' , array('class' => 'generaltable'));
    foreach ($rules as $rule) {
        echo html_writer::start_tag('tr');
        $rule = new \report_monitor\rule($rule, $filtermanager);
        echo html_writer::tag('td', $rule->get_name($context));
        echo html_writer::tag('td', $rule->get_module_name());
       // echo html_writer::tag('td', $rule->get_name($context));
        echo html_writer::tag('td', $rule->get_event_name($context));
        echo html_writer::tag('td', $rule->get_filters_description($context));
        echo html_writer::checkbox('subscribe', 1, $rule->is_user_subscribed($courseid));
        if ($hassystemcap || ($rule->courseid !== 0 && $hascurrentcap)) {
            echo html_writer::checkbox('subscribe', 1, $rule->is_user_subscribed($courseid));
        }
        echo html_writer::end_tag('tr');
    }
    echo html_writer::end_tag('table');
}