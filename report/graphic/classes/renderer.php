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
 * Graphic report renderer.
 *
 * @package    report_graphic
 * @copyright  2015 Simey Lameze <simey@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Report log renderer's for printing reports.
 *
 * @package    report_graphic
 * @copyright  2015 Simey Lameze <simey@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report_graphic_renderer extends plugin_renderer_base {
    protected $renderable;
    protected function render_report_graphic(report_graphic_renderable $renderable) {
        $this->renderable = $renderable;
        $this->report_selector_form();

        //$this->report_generate_charts();

    }

    /**
     * This function is used to generate and display selector form.
     *
     */
    public function report_selector_form() {
        $renderable = $this->renderable;
        $courses = $renderable->get_course_list();
        $selectedcourseid = empty($renderable->course) ? 0 : $renderable->course->id;

        echo html_writer::start_tag('form', array('class' => 'logselecform', 'action' => 'course.php', 'method' => 'get'));
        echo html_writer::start_div();
        echo html_writer::label(get_string('selectacourse'), 'menuid', false);
        echo html_writer::select($courses, "id", $selectedcourseid, null);
        echo html_writer::empty_tag('input', array('type' => 'submit', 'value' => 'Generate'));
        echo html_writer::end_div();
        echo html_writer::end_tag('form');
    }

    public function report_generate_charts() {
        $renderable = $this->renderable;
        echo $renderable->get_gcharts_data();
        echo $renderable->mostactiveusers;
        echo $renderable->mosttriggeredevents;
        echo $renderable->activitybyperiod;
    }

    public function report_course_activity_chart() {
        $this->renderable->get_courses_activity();
        echo $this->renderable->mostactivecourses;
    }
}

