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
     * TODO use rule manager?
     * @param \stdClass|int $ruleorid
     */
    public function __construct($ruleorid) {
        global $DB;
        if (!is_object($ruleorid)) {
            $rule = $DB->get_record('report_monitor_rules', array('id' => $ruleorid), '*', MUST_EXIST);
        } else {
            $rule = $ruleorid;
        }
        $this->rule = $rule;
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
        if ($this->courseid != $courseid && $this->courseid != 0) {
            // Trying to subscribe to a rule that belongs to a different course. Should never happen.
            throw new \coding_exception('Can not subscribe to rules from a different course');
        }
        if ($cmid !== 0) {
            $cms = get_fast_modinfo($courseid);
            $cminfo = $cms->get_cm($cmid);
            if (!$cminfo->uservisible) {
                // Trying to subscribe to a hidden cm. Should never happen.
                throw new \coding_exception('You cannot do that');
            }
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
     * @param filter_manager $filtermanager
     * @return string
     */
    public function get_filters_description(filter_manager $filtermanager) {
        $filters = $filtermanager->get_filters();
        $desc = '';
        foreach ($filters as $filter) {
            //$desc .= $filter->get_description($this->rule);
        }

        return $desc;
    }

    public function get_name($context) {
        return format_text($this->rule->name, $context);
    }

    public function get_description($context) {
        return '';
        return format_text($this->description, $context);
    }

    /**
     * @return string
     * @throws \coding_exception
     */
    public function get_plugin_name() {
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
     * TODO Does filter config need any changing?
     * // TODO Use rule manager class.
     * @param $finalcourseid
     */
    public function copy_rule($finalcourseid) {
        global $DB;
        $rule = $this->rule;
        unset($rule->id);
        $rule->courseid = $finalcourseid;
        $time = time();
        $rule->timecreated = $time;
        $rule->timemodified = $time;
        $DB->insert_record('report_monitor_rules', $rule);
    }

    /**
     * TODO Does filter config need any changing?
     * // TODO Use rule manager class.
     */
    public function delete_rule() {
        global $DB;
        $DB->delete_records('report_monitor_rules', array('id' => $this->id));
    }

    /**
     * @throws \coding_exception
     */
    public function get_module_select($courseid) {
        global $CFG;
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
                if ($cminfo->uservisible) {
                    $options[$cminfo->id] = $cminfo->get_formatted_name();
                }
            }
        }
        $url = new \moodle_url($CFG->wwwroot. '/report/monitor/index.php', array('id' => $courseid, 'ruleid' => $this->id,
                'action' => 'subscribe'));
        return new \single_select($url, 'cmid', $options, '', $nothing = array('' => 'choosedots'));
    }
}