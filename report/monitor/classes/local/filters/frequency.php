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
 * Filter class for frequency.
 *
 * @package    tool_monitor
 * @copyright  2014 onwards Ankit Agarwal <ankit.agrr@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_monitor\local\filters;

defined('MOODLE_INTERNAL') || die();

/**
 * Filter class for frequency.
 *
 * @package    tool_monitor
 * @copyright  2014 onwards Ankit Agarwal <ankit.agrr@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class frequency extends base {

    /**
     * Add elements to the new monitor form.
     *
     */
    public function add_form_elements($mform) {
        $rule = array();
        $rule[] = $mform->createElement('select', 'frequency', get_string('frequency', 'report_monitor'), $this->get_frequencies());
        $rule[] = $mform->createElement('select', 'minutes', get_string('minutes', 'report_monitor'), $this->get_minutes());
        $mform->addGroup($rule, 'rule', get_string('frequency', 'report_monitor'), array('&nbsp;&nbsp;in minutes&nbsp;&nbsp;', '<br />'), true);
        $mform->addRule('rule', get_string('required'), 'required');
        $mform->addHelpButton('rule', 'frequency', 'report_monitor');
    }

    /**
     * Get frequencies
     * @return array
     */
    protected function get_frequencies() {
        return array(1 => 1,5 => 5,10 => 10,20 => 20,30 => 30, 40 => 40, 50 => 50, 60 => 60, 70 => 70, 80 => 80, 90 => 90,
                     100 => 100);
    }

    /**
     * Get minutes
     * @return array
     */
    protected function get_minutes() {
        return array(1 => 1, 5 => 5, 10 => 10, 15 => 15, 20 => 20, 25 => 25, 30 => 30, 35 => 35, 40 => 40, 45 => 45, 50 => 50,
                     55 => 55,  60 => 60);
    }

    /**
     * Return form validation errors.
     *
     * @param $data
     * @param $files
     *
     * @return array errors if any.
     */
    public function validate_data($data, $files) {
        return array();
    }

    public function get_description($rule) {
        $a = new \stdClass();
        $a->freq = $rule->frequency;
        $a->mins = $rule->minutes;
        return get_string('freqdesc', 'report_monitor', $a);
    }

    // More processing apis here.
}
