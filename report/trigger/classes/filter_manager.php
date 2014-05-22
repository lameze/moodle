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
 * @package    tool_trigger
 * @copyright  2014 onwards Ankit Agarwal <ankit.agrr@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_trigger;

defined('MOODLE_INTERNAL') || die();

class filter_manager {

    /** @var \report_trigger\local\filters\base[] $filters list of all installed filters */
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

        // Register shutdown handler - this may be useful for buffering, file handle closing, etc.
        \core_shutdown_manager::register_function(array($this, 'dispose'));

        $directory = \core_component::get_plugin_directory('report', 'trigger');
        $directory = $directory . '/classes/local/filters'; // Location for filters.

        foreach ($files = \report_trigger\util\helper::get_file_list($directory) as $name => $path) {
            if ($name == 'base') {
                continue; // Ignore the base abstract class.
            }
            $classname = "report_trigger\\local\\filters\\$name";
            if (class_exists($classname)) {
                $filter = new $classname($this);
                $this->filters[$name] = $filter;
            }
        }
    }

    /**
     * Returns list of available filters.
     *
     * @return \report_trigger\local\filters\base[] list of available filters
     */
    public function get_filters() {
        $this->init();
        return $this->filters;
    }

    /**
     * Usually called automatically from shutdown manager,
     * this allows us to implement buffering of write operations.
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
