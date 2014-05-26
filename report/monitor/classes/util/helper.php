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

namespace report_monitor\util;

defined('MOODLE_INTERNAL') || die();

class helper {
    /**
     * Returns a list of files with a full directory path in a specified directory.
     *
     * @param string $directory location of files.
     * @return array full location of files from the specified directory.
     */
    public static function get_file_list($directory) {
        global $CFG;
        $directoryroot = $CFG->dirroot;
        $finalfiles = array();
        if (is_dir($directory)) {
            if ($handle = opendir($directory)) {
                $files = scandir($directory);
                foreach ($files as $file) {
                    if ($file != '.' && $file != '..') {
                        // Ignore the file if it is external to the system.
                        if (strrpos($directory, $directoryroot) !== false) {
                            $location = substr($directory, strlen($directoryroot));
                            $name = substr($file, 0, -4);
                            $finalfiles[$name] = $location  . '/' . $file;
                        }
                    }
                }
            }
        }
        return $finalfiles;
    }
}