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
 * Event documentation
 *
 * @package    report_trigger
 * @copyright  2014 onwards Ankit Agarwal <ankit.agrr@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_trigger;

defined('MOODLE_INTERNAL') || die();

/**
 * Class for returning system event information.
 *
 * @package    report_trigger
 * @copyright  2014 onwards Ankit Agarwal <ankit.agrr@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class eventlist {

    /**
     * Convenience method. Returns all of the core events either with or without details.
     *
     * @param bool $abstract True will return all events. False returns events with no abstract classes.
     * @return array All events.
     */
    public static function get_all_events_list($abstract = false) {
        return array_merge(array('core' => self::get_core_events_list($abstract)), self::get_non_core_event_list($abstract));
    }

    /**
     * Return all of the core event files.
     *
     * @param bool $abstract True will return all events. False returns events with no abstract classes.
     * @return array Core events.
     */
    public static function get_core_events_list($abstract = false) {
        global $CFG;

        // Disable developer debugging as deprecated events will fire warnings.
        // Setup backup variables to restore the following settings back to what they were when we are finished.
        $debuglevel          = $CFG->debug;
        $debugdisplay        = $CFG->debugdisplay;
        $debugdeveloper      = $CFG->debugdeveloper;
        $CFG->debug          = 0;
        $CFG->debugdisplay   = false;
        $CFG->debugdeveloper = false;

        $eventinformation = array();
        $directory = $CFG->libdir . '/classes/event';
        $files = \report_trigger\util\helper::get_file_list($directory);

        // Remove exceptional events that will cause problems being displayed.
        if (isset($files['unknown_logged'])) {
            unset($files['unknown_logged']);
        }
        foreach ($files as $file => $location) {
            $classname = '\\core\\event\\' . $file;
            // Check to see if this is actually a valid event.
            if (method_exists($classname, 'get_static_info')) {
                if (!$abstract) {
                    $ref = new \ReflectionClass($classname);
                    // Ignore abstracts.
                    if (!$ref->isAbstract() && $file != 'manager') {
                        $eventinformation[$classname] = $classname::get_name();
                    }
                } else {
                    $eventinformation[$classname] = $classname::get_name();
                }
            }
        }
        // Now enable developer debugging as event information has been retrieved.
        $CFG->debug          = $debuglevel;
        $CFG->debugdisplay   = $debugdisplay;
        $CFG->debugdeveloper = $debugdeveloper;
        return $eventinformation;
    }

    /**
     * This function returns an array of all events for the plugins of the system.
     *
     * @param bool $abstract True will return details, but no abstract classes, False will return all events, but no details.
     * @return array A list of events from all plug-ins.
     */
    public static function get_non_core_event_list($abstract = false) {
        global $CFG;
        // Disable developer debugging as deprecated events will fire warnings.
        // Setup backup variables to restore the following settings back to what they were when we are finished.
        $debuglevel          = $CFG->debug;
        $debugdisplay        = $CFG->debugdisplay;
        $debugdeveloper      = $CFG->debugdeveloper;
        $CFG->debug          = 0;
        $CFG->debugdisplay   = false;
        $CFG->debugdeveloper = false;

        $noncorepluginlist = array();
        $plugintypes = \core_component::get_plugin_types();
        foreach ($plugintypes as $plugintype => $notused) {
            $pluginlist = \core_component::get_plugin_list($plugintype);
            foreach ($pluginlist as $plugin => $directory) {
                $noncorepluginlist[$plugintype . '_' . $plugin] = array();
                $plugindirectory = $directory . '/classes/event';
                foreach (report_trigger_get_file_list($plugindirectory) as $eventname => $notused) {
                    $plugineventname = '\\' . $plugintype . '_' . $plugin . '\\event\\' . $eventname;
                    // Check that this is actually an event.
                    if (method_exists($plugineventname, 'get_static_info')) {
                        if (!$abstract) {
                            $ref = new \ReflectionClass($plugineventname);
                            if (!$ref->isAbstract() && $plugin != 'legacy') {
                                $noncorepluginlist[$plugintype . '_' . $plugin][$plugineventname] = $plugineventname::get_name();
                            }
                        } else {
                            $noncorepluginlist[$plugintype . '_' . $plugin][$plugineventname] = $plugineventname::get_name();
                        }
                    }
                }
            }
        }
        // Now enable developer debugging as event information has been retrieved.
        $CFG->debug          = $debuglevel;
        $CFG->debugdisplay   = $debugdisplay;
        $CFG->debugdeveloper = $debugdeveloper;

        return $noncorepluginlist;
    }
}
