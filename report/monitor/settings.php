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
 * Links and settings
 *
 * This file contains links and settings used by report_monitor
 *
 * @package    report_monitor
 * @copyright  2014 onwards Ankit Agarwal <ankit.agrr@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $ADMIN->add('reports', new admin_category('reportmonitor', new lang_string('pluginname', 'report_monitor')));

    // Manage subscriptions page.
    $url = new moodle_url('/report/monitor/index.php', array('id' => 0));
    $temp = new admin_externalpage('reprotmonitorrules', get_string('managesubscriptions', 'report_monitor'), $url,
        'report/monitor:subscribe');

    $ADMIN->add('reportmonitor', $temp);

    // Manage rules page.
    $url = new moodle_url('/report/monitor/managerules.php', array('id' => 0));
    $temp = new admin_externalpage('reprotmonitorsubscriptions', get_string('managerules', 'report_monitor'), $url,
            'report/monitor:managerules');

    $ADMIN->add('reportmonitor', $temp);
}
