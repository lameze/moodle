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
 * Class containing data for manage evidences page.
 *
 * @package    tool_lp
 * @copyright  2015 Simey Lameze <lameze@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_lp\output;

use renderable;
use templatable;
use renderer_base;
use single_button;
use stdClass;
use moodle_url;
use context_system;
use tool_lp\api;

/**
 * Class containing data for manage evidences page
 *
 * @copyright  2015 Simey Lameze <lameze@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manage_evidences_page implements renderable, templatable {

    /** @var array $navigation List of links to display on the page. Each link contains a url and a title. */
    var $navigation = array();

    /** @var array $templates List of evidences. */
    var $evidences = array();

    /** @var bool $canmanage Result of permissions checks. */
    var $canmanage = false;

    /**
     * Construct this renderable.
     */
    public function __construct() {
        $addpage = new single_button(
           new moodle_url('/admin/tool/lp/edittemplate.php'),
           get_string('addnewtemplate', 'tool_lp')
        );
        $this->navigation[] = $addpage;

        $this->evidences = api::list_evidences(array(), 'sortorder', 'ASC', 0, 0);
        $context = context_system::instance();
        $this->canmanage = has_capability('tool/lp:planmanage', $context);
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        $data = new stdClass();
        $data->canmanage = $this->canmanage;
        $data->evidences = array();
        //print_object ($data);
        foreach ($this->evidences as $evidence) {
            $record = $evidence->to_record();
            $data->evidences[] = $record;
        }
        //print_object($record);
        $data->pluginbaseurl = (new moodle_url('/admin/tool/lp'))->out(true);
        $data->navigation = array();
        foreach ($this->navigation as $button) {
            $data->navigation[] = $output->render($button);
        }

        return $data;
    }
}
