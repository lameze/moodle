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
 * Class represents a single subscription.
 *
 * @package    report_monitor
 * @copyright  2014 onwards Ankit Agarwal <ankit.agrr@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_monitor;

defined('MOODLE_INTERNAL') || die();

/**
 * Class represents a single subscription instance (i.e with all the subscription info).
 *
 * @package    report_monitor
 * @copyright  2014 onwards Ankit Agarwal <ankit.agrr@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class subscription {
    /**
     * @var \stdClass
     */
    protected $subscription;

    /**
     * TODO use subscription manager?
     * @param \stdClass|int $subscriptionorid
     */
    public function __construct($subscriptionorid) {
        global $DB;
        if (!is_object($subscriptionorid)) {
            $subscription = $DB->get_record('report_monitor_subscriptions', array('id' => $subscriptionorid), '*', MUST_EXIST);
        } else {
            $subscription = $subscriptionorid;
        }
        $this->subscription = $subscription;
    }

    /**
     * @param $prop
     *
     * @return mixed
     * @throws \coding_exception
     */
    public function __get($prop) {
        if (property_exists($this->subscription, $prop)) {
            return $this->subscription->$prop;
        }
        throw new \coding_exception('Property doesn\'t exist');
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
            $desc .= $filter->get_description($this);
        }

        return $desc;
    }

    public function get_name($context) {
        return format_text($this->name, $context);
    }

    public function get_description($context) {
        return format_text($this->description, $context);
    }

    /**
     * @return string
     * @throws \coding_exception
     */
    public function get_plugin_name() {
        if (get_string_manager()->string_exists('pluginname', $this->subscription->plugin)) {
            $string = get_string('pluginname', $this->subscription->plugin);
        } else if ($this->plugin === 'core') {
            $string = get_string('core', 'report_monitor');
        } else {
            $string = $this->subscription->plugin;
        }
        return $string;
    }

    /**
     * @return string
     * @throws \coding_exception
     */
    public function get_instance_name() {
        if ($this->plugin === 'core') {
            $string = get_string('allevents', 'report_monitor');
        } else {
            if ($this->cmid == 0) {
                $string = get_string('allmodules', 'report_monitor');
            } else {
                $cms = get_fast_modinfo($this->courseid);
                $cms = $cms->get_cms();
                if (isset($cms[$this->cmid])) {
                    $string = $cms[$this->cmid]->get_formatted_name(); // Instance name.
                } else {
                    // Something is wrong, instance is not present anymore.
                    $string = get_string('invalidmodule', 'report_monitor');
                }
            }
        }
        return $string;
    }

    /**
     */
    public function delete_subscription() {
        global $DB;
        $DB->delete_records('report_monitor_subscriptions', array('id' => $this->id));
    }
}