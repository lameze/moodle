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
        $rule = new \report_monitor\rule($rule);
        echo html_writer::tag('input', '', array('value' => $rule->id, 'type' => 'hidden', 'name' => "[$i]id"));
        echo html_writer::tag('td', $rule->get_name($context));
        echo html_writer::tag('td', $rule->get_plugin_name());
        echo html_writer::tag('td', $rule->get_module_select($courseid));
        echo html_writer::tag('td', $rule->get_event_name($context));
        echo html_writer::tag('td', $rule->get_filters_description($filtermanager));

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
            $editurl = new moodle_url($CFG->wwwroot. '/report/monitor/edit.php', array('ruleid' => $rule->id));
            $copyurl = new moodle_url($CFG->wwwroot. '/report/monitor/index.php', array('ruleid' => $rule->id, 'copy' => 1,
                    'id' => $courseid));
            $deleteurl = new moodle_url($CFG->wwwroot. '/report/monitor/delete.php', array('ruleid' => $rule->id));
            echo html_writer::link($editurl, get_string('edit'));
            echo ' | ';
            echo html_writer::link($copyurl, get_string('copy', 'report_monitor'));
            echo ' | ';
            echo html_writer::link($deleteurl, get_string('delete'));
            echo html_writer::end_tag('td');
        }
        echo html_writer::end_tag('tr');
    }
    echo html_writer::end_tag('table');
    echo html_writer::tag('button', 'Update subscriptions');
    echo html_writer::end_tag('form');
    $addurl = new moodle_url($CFG->wwwroot. '/report/monitor/edit.php', array('courseid' => $courseid));
    echo html_writer::link($addurl, 'Add a new trigger');
}

/**
 * This file gives an overview of the monitors present in site.
 * TODO move this to renderer
 * TODO optimise this
 * TODO use proper strings
 * @package    report_monitor
 * @copyright  2014 onwards Ankit Agarwal <ankit.agrr@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function display_rules_manage($rules, $filtermanager, $courseid, $context) {
    global $CFG, $OUTPUT;
    $systemcontext = context_system::instance();
    $hassystemcap = has_capability('report/monitor:managerules', $systemcontext);
    $hascurrentcap = has_capability('report/monitor:managerules', $context);
    echo html_writer::start_tag('table' , array('class' => 'generaltable'));
    echo html_writer::start_tag('tr');
    echo html_writer::tag('th', 'Name of the rule');
    echo html_writer::tag('th', 'Description');
    echo html_writer::tag('th', 'Plugin');
    echo html_writer::tag('th', 'Event');
    echo html_writer::tag('th', 'Criteria');
    echo html_writer::tag('th', 'Manage',  array('style' => "text-align:center;"));
    echo html_writer::end_tag('tr');

    foreach ($rules as $rule) {
        echo html_writer::start_tag('tr');
        $rule = new \report_monitor\rule($rule);
        echo html_writer::tag('td', $rule->get_name($context));
        echo html_writer::tag('td', $rule->get_description($context));
        echo html_writer::tag('td', $rule->get_plugin_name());
        echo html_writer::tag('td', $rule->get_event_name($context));
        echo html_writer::tag('td', $rule->get_filters_description($filtermanager));

        if ($hassystemcap || ($rule->courseid !== 0 && $hascurrentcap)) {
            // Can manage this rule. There might be site rules which the user can not manage. Should we show these here or not?
            echo html_writer::start_tag('td',  array('style' => "text-align:center;"));
            $editurl = new moodle_url($CFG->wwwroot. '/report/monitor/edit.php', array('ruleid' => $rule->id));
            $copyurl = new moodle_url($CFG->wwwroot. '/report/monitor/managerules.php', array('ruleid' => $rule->id, 'copy' => 1,
                                                                                        'id' => $courseid));
            $deleteurl = new moodle_url($CFG->wwwroot. '/report/monitor/delete.php', array('ruleid' => $rule->id));

            $icon = $OUTPUT->render(new pix_icon('t/edit', ''));
            echo html_writer::link($editurl, $icon, array('class' => 'action-icon'));

            $icon = $OUTPUT->render(new pix_icon('t/copy', ''));
            echo html_writer::link($copyurl, $icon, array('class' => 'action-icon'));

            $icon = $OUTPUT->render(new pix_icon('t/delete', ''));
            echo html_writer::link($deleteurl, $icon, array('class' => 'action-icon'));

        }
        echo html_writer::end_tag('tr');
    }
    echo html_writer::end_tag('table');
    $button = html_writer::tag('button', 'Add a new rule');
    $addurl = new moodle_url($CFG->wwwroot. '/report/monitor/edit.php', array('courseid' => $courseid));
    echo html_writer::link($addurl, $button);
}

/**
 * This file gives an overview of the monitors present in site.
 * TODO move this to renderer
 * TODO optimise this
 * TODO use proper strings
 * @package    report_monitor
 * @copyright  2014 onwards Ankit Agarwal <ankit.agrr@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function display_rules_subscriptions($subscriptions, $filtermanager, $courseid, $context) {
    global $CFG, $OUTPUT;
    echo html_writer::tag('h2', 'Your current subscriptions');
    echo html_writer::start_tag('table' , array('class' => 'generaltable'));
    echo html_writer::start_tag('tr');
    echo html_writer::tag('th', 'Name of the rule', array('colspan' => 2));
    echo html_writer::tag('th', 'Instance');
    echo html_writer::tag('th', 'Manage Subscription',  array('style' => "text-align:center;"));
    echo html_writer::end_tag('tr');
    $helpicon = new pix_icon('help', '');

    foreach ($subscriptions as $subscription) {
        echo html_writer::start_tag('tr');
        $subscription = new \report_monitor\subscription($subscription);
        $url = new moodle_url('/report/monitor/ruledetail.php', array('ruleid' => $subscription->ruleid));
        echo html_writer::tag('td', $OUTPUT->action_icon($url, $helpicon, new popup_action('click', $url)));
        echo html_writer::tag('td', $subscription->get_name($context));
        echo html_writer::tag('td', $subscription->get_instance_name());

        // Can manage this rule. There might be site rules which the user can not manage. Should we show these here or not?
        echo html_writer::start_tag('td', array('style' => "text-align:center;"));
        $deleteurl = new moodle_url($CFG->wwwroot. '/report/monitor/index.php', array('id' => $courseid,
                'subscriptionid' => $subscription->id, 'action' => 'unsubscribe'));
        $icon = $OUTPUT->render(new pix_icon('t/delete', ''));
        echo html_writer::link($deleteurl, $icon);
        echo html_writer::end_tag('td');

        echo html_writer::end_tag('tr');
    }
    echo html_writer::end_tag('table');
}

/**
 * This file gives an overview of the monitors present in site.
 * TODO move this to renderer
 * TODO optimise this
 * TODO use proper strings
 * @package    report_monitor
 * @copyright  2014 onwards Ankit Agarwal <ankit.agrr@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function display_rules_subscription_rules($rules, $filtermanager, $context, $courseid) {
    global $OUTPUT;
    echo html_writer::tag('h2', 'Rules you can subscribe to');
    echo html_writer::start_tag('table' , array('class' => 'generaltable'));
    echo html_writer::start_tag('tr');
    echo html_writer::tag('th', 'Name of the rule', array('colspan' => 2));
    echo html_writer::tag('th', 'Description');
    echo html_writer::tag('th', 'Subscribe');
    echo html_writer::end_tag('tr');
    $helpicon = new pix_icon('help', '');

    foreach ($rules as $rule) {
        echo html_writer::start_tag('tr');
        $rule = new \report_monitor\rule($rule);
        $url = new moodle_url('/report/monitor/ruledetail.php', array('ruleid' => $rule->id));
        echo html_writer::tag('td', $OUTPUT->action_icon($url, $helpicon, new popup_action('click', $url)));
        echo html_writer::tag('td', $rule->get_name($context));
        echo html_writer::tag('td', $rule->get_description($context));
        if ($courseid != 0) {
            echo html_writer::tag('td', $OUTPUT->render($rule->get_module_select($courseid)));
        } else {
            echo html_writer::tag('td', $rule->get_module_select($courseid));
        }
        echo html_writer::end_tag('tr');
    }
    echo html_writer::end_tag('table');
}