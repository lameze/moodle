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
 * Block udemoodle renderer.
 *
 * @package    block_udemoodle
 * @copyright  2016 Simey Lameze <simey@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_udemoodle\output;
defined('MOODLE_INTERNAL') || die();
use context;
use moodle_url;
use plugin_renderer_base;
use renderable;

/**
 * Block Udemoodle renderer class.
 *
 * @package    block_udemoodle
 * @copyright  2016 Adrian Greeve
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends plugin_renderer_base {

//    /**
//     * Outputs the navigation.
//     *
//     * @param block_xp_manager $manager The manager.
//     * @param string $page The page we are on.
//     * @return string The navigation.
//     */
//    public function navigation($manager, $page) {
//        $tabs = [];
//        $courseid = $manager->get_courseid();
//        if ($manager->can_manage()) {
//            $tabs[] = new tabobject(
//                'items',
//                new moodle_url('/blocks/stash/items.php', ['courseid' => $courseid]),
//                get_string('navitems', 'block_stash')
//            );
//            // Presently we hide the drops page by default.
//            if ($page == 'drops') {
//                $tabs[] = new tabobject(
//                    'drops',
//                    new moodle_url('/blocks/stash/drops.php', ['courseid' => $courseid]),
//                    get_string('navdrops', 'block_stash')
//                );
//            }
//        }
//        // If there is only one page, then that is the page we are on.
//        if (count($tabs) == 1) {
//            return '';
//        }
//        return $this->tabtree($tabs, $page);
//    }
    public function render_block_content(renderable $page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('block_udemoodle/main_content', $data);
    }

}