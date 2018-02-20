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
 * Question related functions.
 *
 * This file was created just because Fragment API expects callbacks to be defined on lib.php.
 *
 * Please, do not add new functions to this file.
 *
 * @package   core_question
 * @copyright 2018 Simey Lameze <simey@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Question tags fragment callback.
 *
 * @param array $args Arguments to the form.
 * @return null|string The rendered form.
 */
function core_question_output_fragment_tags_form($args) {

    if (!empty($args['id'])) {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/question/type/tags_form.php');
        require_once($CFG->libdir . '/questionlib.php');

        $id = clean_param($args['id'], PARAM_INT);
        $cmid = !empty($args['cmid']) ? clean_param($args['cmid'], PARAM_INT) : null;
        $courseid = !empty($args['courseid']) ? clean_param($args['courseid'], PARAM_INT) : null;

        $question = $DB->get_record('question', ['id' => $id]);
        get_question_options($question, true);

        $category = $DB->get_record('question_categories', array('id' => $question->category));
        $categorycontext = \context::instance_by_id($category->contextid);

        $toform['id'] = $question->id;
        $toform['cmid'] = $cmid;
        $toform['courseid'] = $courseid;
        $toform['questioncategory'] = $category->name;
        $toform['questionname'] = $question->name;
        $toform['categoryid'] = $category->id;
        $toform['contextid'] = $category->contextid;
        $toform['context'] = $categorycontext->get_context_name();

        if ($cmid){
            $thiscontext = context_module::instance($cmid);
        } elseif ($courseid) {
            $thiscontext = context_course::instance($courseid);
        } else {
            print_error('missingcourseorcmid', 'question');
        }
        $coursecontext = $thiscontext->get_course_context();
        $contexts = new question_edit_contexts($thiscontext);
        $iscoursequestion = ($categorycontext->id == $coursecontext->id || $categorycontext->id == $thiscontext->id);

        // If the question has course tags then we need to filter them to only
        // include the tags for this course.
        if (isset($question->coursetagobjects)) {
            // Get the tag objects for the course being viewed.
            $coursetagobjects = array_filter(
                $question->coursetagobjects,
                function($tagobject) use ($coursecontext) {
                    return $coursecontext->id == $tagobject->taginstancecontextid;
                }
            );

            // Set them on the form to be rendered as existing tags.
            $toform['coursetags'] = [];
            foreach ($coursetagobjects as $tagobject) {
                $toform['coursetags'][$tagobject->id] = $tagobject->get_display_name();
            }
        }
        $toform['tags'] = $question->tags;

        $formoptions = new stdClass();
        $formoptions->iscoursequestion = $iscoursequestion;
        $formoptions->context = $categorycontext;
        $formoptions->contexts = $contexts;

        $cantag = question_has_capability_on($question, 'tag');
        $mform = new \core_question\form\tags(null, $formoptions, 'post', '', null, $cantag, $toform);
        $mform->set_data($toform);

        return $mform->render();
    }
}
