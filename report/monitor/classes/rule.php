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
 * Class represents a single rule.
 *
 * @package    report_monitor
 * @copyright  2014 onwards Ankit Agarwal <ankit.agrr@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_monitor;

defined('MOODLE_INTERNAL') || die();

/**
 * Class represents a single rule.
 *
 * @package    report_monitor
 * @copyright  2014 onwards Ankit Agarwal <ankit.agrr@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class rule {
    /**
     * @var \stdClass
     */
    protected $rule;

    /**
     * @var filter_manager
     */
    protected $filtermanager;

    /**
     * TODO use rule manager?
     * TODO may be remove filtermanager from the arguments
     * @param \stdClass|int $ruleorid
     * @param filter_manager $filtermanager
     */
    public function __construct($ruleorid, filter_manager $filtermanager) {
        global $DB;
        if (!is_object($ruleorid)) {
            $rule = $DB->get_record('report_monitor_rules', array('id' => $ruleorid), '*', MUST_EXIST);
        } else {
            $rule = $ruleorid;
        }
        $this->rule = $rule;
        $this->filtermanager = $filtermanager;
    }

    /**
     * @param $prop
     *
     * @return mixed
     * @throws \coding_exception
     */
    public function __get($prop) {
        if (isset($this->rule->$prop)) {
            return $this->rule->$prop;
        }
        throw new \coding_exception('Property doesn\'t exist');
    }

    /**
     * @param $courseid
     * @param $cmid
     * @param int $userid
     *
     * @throws \coding_exception
     */
    public function subscribe_user($courseid, $cmid, $userid = 0) {
        if ($this->courseid !== $courseid && $this->courseid !== 0) {
            // Trying to subscribe to a rule that belongs to a different course.
            throw new \coding_exception('Can not subscribe to rules from a different course');
        }
        subscription_manager::subscribe($this->id, $courseid, $cmid, $userid);
    }

    /**
     * TODO this is not right
     * @param int $userid
     */
    public function unsubscribe_user($userid = 0) {
        subscription_manager::unsubscribe($this->id, $this->courseid, $this->cmid, $userid);
    }

    /**
     * @return string
     * @throws \coding_exception
     */
    public function get_event_name() {
        $eventclass = $this->event;
        if (class_exists($eventclass)) {
            return $eventclass::get_name();
        }
        return get_string('eventnotfound');
    }

    /**
     * @return string
     */
    public function get_filters_description() {
        $filters = $this->filtermanager->get_filters();
        $desc = '';
        foreach ($filters as $filter) {
            //$desc .= $filter->get_description($this->rule);
        }

        return $desc;
    }

    public function get_name($context) {
        return format_text($this->rule->name, $context);
    }

    /**
     * @return string
     * @throws \coding_exception
     */
    public function get_module_name() {
        if (get_string_manager()->string_exists('pluginname', $this->rule->plugin)) {
            $string = get_string('pluginname', $this->rule->plugin);
        } else if ($this->plugin === 'core') {
            $string = get_string('core', 'report_monitor');
        } else {
            $string = $this->rule->plugin;
        }
        return $string;
    }

    /**
     * TODO optimise this method.
     */
    public function is_user_subscribed($courseid, $userid = 0) {
        global $USER, $DB;
        if ($userid === 0) {
            $userid = $USER->id;
        }
        return (bool)$DB->get_record('report_monitor_subscriptions', array('ruleid' => $this->id, 'courseid' => $courseid,
                'userid' => $userid));
    }

    /**
     * @param $courseid
     *
     * @return string
     * @throws \coding_exception
     */
    public function get_module_select($courseid) {
        $options = array();
        $options[0] = get_string('allmodules', 'report_monitor');
        if (strpos($this->plugin, 'mod_') === 0) {
            if ($courseid == 0) {
                // They need to be in a course to select module instance.
                return get_string('selectcourse', 'report_monitor');;
            }
            // Let them select an instance.
            $cms = get_fast_modinfo($courseid);
            $instances = $cms->get_instances_of(str_replace('mod_', '', 'forum'));
            foreach ($instances as $cminfo) {
                $options[$cminfo->id] = $cminfo->get_formatted_name();
            }
        }
        return \html_writer::select($options, 'cmid');
    }
}