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
 * TODO move this to renderer
 * TODO optimise this
 * TODO use proper strings
 * @package    report_monitor
 * @copyright  2014 onwards Ankit Agarwal <ankit.agrr@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function display_rules($rules, $filtermanager, $courseid, $context) {
    global $CFG;
    $systemcontext = context_system::instance();
    $hassystemcap = has_capability('report/monitor:managerules', $systemcontext);
    $hascurrentcap = has_capability('report/monitor:managerules', $context);
    echo html_writer::start_tag('form' , array('method' => 'post', 'id' => 'subscriptions'));
    echo html_writer::start_tag('table' , array('class' => 'generaltable'));
    echo html_writer::start_tag('tr');
    echo html_writer::tag('th', 'Name of the rule');
    echo html_writer::tag('th', 'Plugin');
    echo html_writer::tag('th', 'Module');
    echo html_writer::tag('th', 'Event');
    echo html_writer::tag('th', 'Criterias');
    echo html_writer::tag('th', 'Subscribe');
    echo html_writer::tag('th', 'Manage');
    echo html_writer::end_tag('tr');
    $i = 0;
    foreach ($rules as $rule) {
        $i++;
        echo html_writer::start_tag('tr');
        $rule = new \report_monitor\rule($rule, $filtermanager);
        echo html_writer::tag('input', '', array('value' => $rule->id, 'type' => 'hidden', 'name' => "[$i]id"));
        echo html_writer::tag('td', $rule->get_name($context));
        echo html_writer::tag('td', $rule->get_module_name());
        echo html_writer::tag('td', $rule->get_module_select($courseid));
        echo html_writer::tag('td', $rule->get_event_name($context));
        echo html_writer::tag('td', $rule->get_filters_description($context));

        echo html_writer::start_tag('td');
        if (!($courseid === 0 && strpos($rule->plugin, 'mod_') === 0)) {
            // We can't allow people to subscribe to a site level rule that needs module instance to be selected.
            echo html_writer::checkbox('subscribe', 1, $rule->is_user_subscribed($courseid), '', array('name' => "[$i]id"));
        } else {
            echo html_writer::checkbox('subscribe', 1, 0, '', array('disabled' => "disabled"));
        }
        echo html_writer::end_tag('td');

        if ($hassystemcap || ($rule->courseid !== 0 && $hascurrentcap)) {
            // Can manage this rule.
            echo html_writer::start_tag('td');
            $editurl = new moodle_url($CFG->wwwroot. '/reports/monitor/edit.php', array('ruleid' => $rule->id));
            $copyurl = new moodle_url($CFG->wwwroot. '/reports/monitor/copy.php', array('ruleid' => $rule->id));
            $deleteurl = new moodle_url($CFG->wwwroot. '/reports/monitor/delete.php', array('ruleid' => $rule->id));
            echo html_writer::link($editurl, get_string('edit'));
            echo ' | ';
            echo html_writer::link($copyurl, get_string('copy'));
            echo ' | ';
            echo html_writer::link($deleteurl, get_string('delete'));
            echo html_writer::end_tag('td');
        }
        echo html_writer::end_tag('tr');
    }
    echo html_writer::end_tag('table');
    echo html_writer::tag('button', 'Update subscriptions');
    echo html_writer::end_tag('form');
    $addurl = new moodle_url($CFG->wwwroot. '/reports/monitor/edit.php', array('courseid' => $courseid));
    echo html_writer::link($addurl, 'Add a new trigger');
}