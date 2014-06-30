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
 * Rule manager class.
 *
 * @package    tool_monitor
 * @copyright  2014 onwards Simey Lameze <lameze@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_monitor;

defined('MOODLE_INTERNAL') || die();

class rule_manager {

    /**
     * Create a new rule.
     *
     * @param $ruledata \stdClass
     * @return bool|int
     */
    public static function add_rule($ruledata) {
        global $DB, $USER;
        $now = time();
        $rule = new \stdClass();
        $rule->userid = empty($ruledata->userid) ? $USER->id : $ruledata->userid;
        $rule->courseid = $ruledata->courseid;
        $rule->name = $ruledata->name;
        $rule->plugin = $ruledata->plugin;
        $rule->eventname = $ruledata->eventname;
        $rule->description = $ruledata->description;
        $rule->frequency = (int)$ruledata->frequency;
        $rule->message_template = $ruledata->message_template;
        $rule->timewindow = $now;
        $rule->timecreated = $now;
        $rule->timemodified = $now;
        $ruleid = $DB->insert_record('tool_monitor_rules', $rule, true);

        return new rule($ruleid);
    }

    /**
     * Delete a rule and subscription by id.
     *
     * @param $ruleid
     * @return bool
     */
    public static function delete_rule($ruleid) {
        global $DB;
        subscription_manager::remove_all_subscriptions_for_rule($ruleid);
        return $DB->delete_records('tool_monitor_rules', array('id' => $ruleid));
    }

    /**
     * Get a single rule by id.
     *
     * @param int|object $rule
     * @return mixed
     */
    public static function get_rule($ruleorid) {
        global $DB;
        if (!is_object($ruleorid)) {
            $rule = $DB->get_record('tool_monitor_rules', array('id' => $ruleorid), '*', MUST_EXIST);
        } else {
            $rule = $ruleorid;
        }

        return new rule($rule);
    }

    /**
     * Update rule data.
     *
     * @param object $params
     * @return bool
     */
    public static function update_rule($params) {
        global $DB;
        if (!self::get_rule($params->id)) {
            throw new coding_exception('Invalid rule ID.');
        }
        $params->timemodified = time();
        return $DB->update_record('tool_monitor_rules', $params, false);
    }

    /**
     * Get rules by courseid.
     *
     * @param int $courseid
     * @return mixed
     */
    public static function get_rules_by_courseid($courseid) {
        global $DB;
        return $DB->get_records('tool_monitor_rules', array('courseid' => $courseid));
    }

    /**
     * Get rules by plugin.
     *
     * @param string $plugin
     * @return mixed
     */
    public static function get_rules_by_plugin($plugin) {
        global $DB;
        return $DB->get_records('tool_monitor_rules', array('plugin' => $plugin));
    }

    /**
     * Get rules by event name.
     *
     * @param string $event
     * @return mixed
     */
    public static function get_rules_by_event($eventname) {
        global $DB;
        return $DB->get_records('tool_monitor_rules', array('eventname' => $eventname));
    }
}