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
 * @package    tool_monitor
 * @copyright  2014 onwards Ankit Agarwal <ankit.agrr@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_monitor;

defined('MOODLE_INTERNAL') || die();

/**
 * Filter manager.
 *
 * @package    tool_monitor
 * @copyright  2014 onwards Ankit Agarwal <ankit.agrr@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class filter_manager {

    /** @var \report_monitor\local\filters\base[] $filters list of all installed filters */
    protected $filters;

    /**
     * Delayed initialisation of singleton.
     */
    protected function init() {
        if (isset($this->filters)) {
            // We already have the information we need.
            return;
        }
        $this->filters = array();

        $directory = \core_component::get_plugin_directory('report', 'monitor');
        $directory = $directory . '/classes/local/filters'; // Location for filters.

        foreach ($files = util\helper::get_file_list($directory) as $name => $path) {
            if ($name == 'base') {
                continue; // Ignore the base abstract class.
            }
            $classname = "report_monitor\\local\\filters\\$name";
            if (class_exists($classname)) {
                $filter = new $classname($this);
                $this->filters[$name] = $filter;
            }
        }
    }

    /**
     * Returns list of available filters.
     *
     * @return \report_monitor\local\filters\base[] list of available filters
     */
    public function get_filters() {
        $this->init();
        return $this->filters;
    }

    /**
     * Usually called from the scheduled task, at the end of processing the task.
     */
    public function dispose() {
        if ($this->filters) {
            foreach ($this->filters as $filter) {
                $filter->dispose();
            }
        }
        $this->filters = null;
    }
}
