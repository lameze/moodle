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
 * Filter base class.
 *
 * @package    tool_monitor
 * @copyright  2014 onwards Ankit Agarwal <ankit.agrr@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_monitor\local\filters;

defined('MOODLE_INTERNAL') || die();

/**
 * Filter base class.
 *
 * @package    tool_monitor
 * @copyright  2014 onwards Ankit Agarwal <ankit.agrr@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

abstract class base {

    /**
     * Add elements to the new monitor form.
     *
     * @param $mform
     */
    abstract public function add_form_elements($mform);

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

    /**
     * Called at the end of a request, can be used to update custom flags.
     *
     * @return bool
     */
    public function dispose() {
        return true;
    }

    /**
     * Get a human readable description of criteria for a given rule.
     *
     * @param \report_monitor\rule $rule the rule object.
     *
     * @return string
     */
    public function get_description($rule) {
        return '';
    }
    // More processing apis here.
}
