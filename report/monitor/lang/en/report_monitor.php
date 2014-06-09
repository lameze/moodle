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
 * Lang strings
 *
 * This files lists lang strings related to report_monitor.
 *
 * @package    report_monitor
 * @copyright  2014 onwards Ankit Agarwal <ankit.agrr@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['allevents'] = 'All events';
$string['allmodules'] = 'All modules';
$string['areyousure'] = 'You are going to delete the following rule : {$a} This can not be undone. Are you sure?';
$string['copy'] = 'Copy';
$string['copysuccess'] = 'Rule copied';
$string['core'] = 'Core';
$string['deleterule'] = 'Delete a rule';
$string['deletesuccess'] = 'Rule deleted';
$string['eventnotfound'] = 'Event not found';
$string['invalidmodule'] = 'Invalid module';
$string['managerules'] = 'Manage rules';
$string['managesubscriptions'] = 'Manage subscriptions';
$string['pluginname'] = 'Event monitor';
$string['processevents'] = 'Process events';
$string['ruledetails'] = 'Rule details';
$string['ruledetails_help'] = '
<b>Name:</b> {$a->name} </br>
<b>Description:</b> {$a->description} </br>
<b>Plugin:</b> {$a->plugin} </br>
<b>Criteria:</b> {$a->description} </br>';
$string['goback'] = 'Go back';
$string['selectcourse'] = 'Visit this report at course level to get a list of possible modules';
$string['subscribesuccess'] = 'Subscription created';
$string['title'] = '{$a->coursename} : {$a->reportname}';
$string['unsubscribesuccess'] = 'Subscription removed';

$string['title'] = '{$a->coursename} : {$a->reportname}';
$string['name'] = 'Name';
$string['plugin'] = 'Select the plugin type:';
$string['customize'] = 'Customize your rule';
$string['event'] = 'Select the event:';
$string['description'] = 'Description:';
$string['frequency'] = 'Frequency of events:';
$string['minutes'] = 'in minutes:';
$string['message_header'] = 'Customize your notification message';
$string['message_template'] = 'Message template';
$string['defaultmessagetpl'] = 'Create your customized rule message here.';
$string['erroremptyname'] = 'You must add a name to your rule.';
$string['pluginname'] = 'Event monitor';
$string['processevents'] = 'Process events';

// Help icons descriptions
$string['name_help'] = "Choose a name for your rule.";
$string['plugin_help'] = "Select the plugin that you want to monitor.";
$string['event_help'] = "Select the event that you want to monitor.";
$string['frequency_help'] = "Select the frequency in minutes that your want to monitor.";
$string['message_template_help'] = "Create a customized message to you rule.";
$string['description_help'] = "Description of your rule.";
// Error messages
$string['erroremptyname'] = 'Please select a name to your rule.';
$string['errorplugin'] = 'Please select your plugin.';
$string['errorevent'] = 'Please select an event related to the selected plugin.';
$string['erroremptyfreq'] = 'Please select a frequency and time.';
$string['erroremptymessage'] = 'The field template message cannot be empty.';

