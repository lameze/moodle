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
 * Graphic report renderer class.
 *
 * @package    report_graphic
 * @copyright  2015 Simey Lameze <simey@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

/**
 * Graphic report renderable class.
 *
 * @package    report_graphic
 * @copyright  2015 Simey Lameze <simey@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
global $CFG;

require_once($CFG->dirroot . '/report/graphic/classes/report_graphic.php');

class report_graphic_renderable implements renderable {

    /** @var int|\stdClass the course object */
    public $course;

    /** @var int|\stdClass controls course visibility */
    public $showcourses;
    public $mostactiveusers;
    public $mosttriggeredevents;
    public $activitybyperiod;
    public $mostactivecourses;

    /**
     * Constructor.
     *
     * @param stdClass|int $course (optional) course record or id
     */
    public function __construct($course = null) {

        // Use site course id, if course is empty.
        if (!empty($course) && is_int($course)) {
            $course = get_course($course);
            $this->course = $course;
        }
    }

    /**
     * Return list of courses to show in selector.
     *
     * @return array list of courses.
     */
    public function get_course_list() {
        global $DB;

        $courses = array();
        $sitecontext = context_system::instance();
        // First check to see if we can override showcourses and showusers.
        $numcourses = $DB->count_records("course");
        if ($numcourses < COURSE_MAX_COURSES_PER_DROPDOWN && !$this->showcourses) {
            $this->showcourses = 1;
        }

        // Check if course filter should be shown.
        if ($this->showcourses) {
            if ($courserecords = $DB->get_records("course", null, "fullname", "id,shortname,fullname,category")) {
                foreach ($courserecords as $course) {
                    if ($course->id == SITEID) {
                        $courses[$course->id] = format_string($course->fullname) . ' (' . get_string('site') . ')';
                    } else {
                        $courses[$course->id] = format_string(get_course_display_name_for_list($course));
                    }
                }
            }
            core_collator::asort($courses);
        }
        return $courses;
    }

    public function get_gcharts_data() {


        $logreader = get_log_manager()->get_readers();
        $logreader = reset($logreader);

        $graph_report = new report_graphic($logreader, $this->course->id);

        // User Activity Pie Chart.
        $this->mostactiveusers = $graph_report->get_most_active_users();

        // Most triggered events. rename this attr
        $this->mosttriggeredevents = $graph_report->get_most_triggered_events();

        // Monthly user activity.
        $this->activitybyperiod = $graph_report->get_monthly_user_activity();
    }

    public function get_courses_activity() {
        $logreader = get_log_manager()->get_readers();
        $logreader = reset($logreader);

        $graph_report = new report_graphic($logreader);

        $this->mostactivecourses = $graph_report->get_courses_activity();
    }
}