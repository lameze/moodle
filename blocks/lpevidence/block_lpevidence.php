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
 * Learning Plans - Evidence block
 *
 * @package    block_lpevidence
 * @copyright  2015 Simey Lameze <lameze@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_lpevidence extends block_list {

    function init() {
        $this->title = get_string('pluginname', 'block_lpevidence');
    }

    public function get_content() {
        global $DB;

//        $evidencescount = $DB->count_records_sql("SELECT id
//                                               FROM {lp_evidence} lpe
//                                              WHERE lpe.status <> 0");

        $evidencescount = 127;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content         =  new stdClass;
        $this->content->text = '';

        if ($evidencescount > 0) {
            $this->content->text .= get_string("evidencewaiting", "block_lpevidence", $evidencescount);
        } else {
            $this->content->text .= get_string("noevidence", "block_lpevidence");
        }

        $this->content->text .= 'lala';



        // maybe add a icon for alert and no evidence (success)
        //$manageevidenceurl = new \moodle_url();
        $this->content->footer = 'Click here too see...';

        return $this->content;
    }

    function has_config() {
        return false;
    }
}
