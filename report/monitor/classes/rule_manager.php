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
 * Filter manager.
 *
 * @package    report_monitor
 * @copyright  2014 onwards Simey Lameze <lameze@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace report_monitor;

defined('MOODLE_INTERNAL') || die();

class rule_manager {

    /** Status of an active rule. */
    const RULE_ACTIVE = 1;

    /** Status of an inactive rule. */
    const RULE_INACTIVE = 0;

    /**
     * Create a new rule.
     * @param $ruledata \stdClass
     * @return bool|int
     */
    public static function add_rule(\stdClass $ruledata) {
        global $DB, $USER;
        $now = time();
        $rule = new \stdClass();
        $rule->userid = empty($ruledata->userid) ? $USER->id : $ruledata->userid;
        $rule->courseid = $ruledata->courseid;
        $rule->name = $ruledata->name;
        $rule->plugin = $ruledata->plugin;
        $rule->event = $ruledata->event;
        $rule->description = $ruledata->description;
        $rule->status = self::RULE_ACTIVE;
        $rule->frequency = $ruledata->frequency;
        $rule->minutes = $ruledata->minutes;
        $rule->message_template = $ruledata->message_template;
        $rule->timecreated = $now;
        $rule->timemodified = $now;
        return $DB->insert_record('report_monitor_rules', $rule, true);
    }

    /**
     * Delete a rule and subscription by id.
     * @param $ruleid
     * @return bool
     */
    public static function delete_rule($ruleid) {
        global $DB;

        // Should we delete the rule or change a status field?
        $del = $DB->delete_records('report_monitor_rules', array('id' => $ruleid));

        if($del) {
            // create another method to remove all subscriptions by rule id?
            subscription_manager::remove_all_subscribers_for_rule($ruleid);
        }
    }

    /**
     * Get a single rule by id.
     * @param int $ruleid
     * @return mixed
     */
    public static function get_rule($ruleid) {
        global $DB;
        return $DB->get_record('report_monitor_rules', array('id' => $ruleid), '*', IGNORE_MISSING);
    }

    /**
     * Update rule data
     * @param array $params
     * @return bool
     */
    public static function update_rule($params) {
        global $DB;
        return $DB->update_record('report_monitor_rules', $params, false);
    }

    /**
     * Get rules by courseid.
     * @param int $courseid
     * @return mixed
     */
    public static function get_rules_by_courseid($courseid) {
        global $DB;
        return $DB->get_records('report_monitor_rules', array('courseid' => $courseid), '*', IGNORE_MISSING);
    }

    /**
     * Get rules by plugin.
     * @param int $plugin
     * @return mixed
     */
    public static function get_rules_by_plugin($plugin) {
        global $DB;
        return $DB->get_records('report_monitor_rules', array('plugin' => $plugin), '*', IGNORE_MISSING);
    }

    /**
     * Get rules by event.
     * @param int $event
     * @return mixed
     */
    public static function get_rules_by_event($event) {
        global $DB;
        return $DB->get_records('report_monitor_rules', array('event' => $event), '*', IGNORE_MISSING);
    }
}