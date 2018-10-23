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
 * Starred courses block external API
 *
 * @package    block_favourite_courses
 * @category   external
 * @copyright  2018 Simey Lameze <simey@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.6
 */
defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/externallib.php');

require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/course/externallib.php');

use \core_course\external\course_summary_exporter;


class block_starredcourses_external extends core_course_external {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 3.6
     */
    public static function get_starred_courses_parameters() {
        return new external_function_parameters([
            'limit' => new external_value(PARAM_INT, 'Limit', VALUE_DEFAULT, 0),
            'offset' => new external_value(PARAM_INT, 'Offset', VALUE_DEFAULT, 0),
            'userid' => new external_value(PARAM_INT, 'id of user, empty for current user', VALUE_DEFAULT, 0)
        ]);
    }

    /**
     * Get users starred courses appending additional course information like images.
     *
     * @param  int $limit Limit
     * @param int $offset Offset
     * @param  int|null $userid The user's userid to fetch the favourite courses.
     * @return  array list of courses and warnings
     */
    public static function get_starred_courses($limit, $offset, $userid = null) {
        global $USER, $PAGE;

        if (!$userid) {
            $userid = $USER->id;
        }

        $params = self::validate_parameters(self::get_starred_courses_parameters(), [
            'limit' => $limit,
            'offset' => $offset,
            'userid' => $userid
        ]);

        $userid = $params['userid'];
        $limit = $params['limit'];
        $offset = $params['offset'];

        $usercontext = context_user::instance($userid);

        self::validate_context($usercontext);
        $PAGE->set_context($usercontext);
        $output = $PAGE->get_renderer('block_starredcourses');

        // Get the user favourites service, scoped to a single user (their favourites only).
        $userservice = \core_favourites\service_factory::get_service_for_user_context($usercontext);

        // Get the favourites, by type, for the user.
        $favourites = $userservice->find_favourites_by_type('core_course', 'course', $offset, $limit);

        $results = [];
        foreach ($favourites as $favourite) {
            $courseid = $favourite->itemid;
            if (!isset($results[$courseid])) {
                $exporter = new course_summary_exporter(get_course($courseid),
                    ['context' => \context_course::instance($courseid)]);
                $courseinlist = new \core_course_list_element(get_course($courseid));
                foreach ($courseinlist->get_course_overviewfiles() as $file) {
                    if ($file->is_valid_image()) {
                        $url = new moodle_url("/pluginfile.php".'/'.$file->get_contextid(). '/'. $file->get_component(). '/'.
                            $file->get_filearea(). $file->get_filepath(). $file->get_filename());
                        $courseimage = $url->__toString();
                    }
                }
                $results[$courseid] = $exporter->export($output);
                if(!empty($courseimage)) {
                    $results[$courseid]['courseimage'] = $courseimage;
                }
            }
        }

        return $results;
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 3.6
     */
    public static function get_starred_courses_returns() {
        return new external_multiple_structure(course_summary_exporter::get_read_structure());
    }
}
