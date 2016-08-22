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
 * Block udemoodle renderable.
 *
 * @package    block_udemoodle
 * @copyright  2016 Simey Lameze <simey@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_udemoodle\output;

defined('MOODLE_INTERNAL') || die();

use renderable;
use renderer_base;
use templatable;
use moodle_url;
use aEscarcha\UdemyApiClient;
use GuzzleHttp\Client;
class block_content implements renderable, templatable {

    public function __construct() {
//        $this->manager = $manager;
    }
    public function export_for_template(renderer_base $output) {
        global $USER;

//        $useritems = $this->manager->get_all_user_items_in_stash($USER->id);
//        foreach ($useritems as $item) {
//            $exporter = new item_exporter($item->item, ['context' => $this->manager->get_context()]);
//            $exported = $exporter->export($output);
//            $exported->quantity = $item->useritem->get_quantity();
//            $data['items'][] = $exported;
//        }
        //$request =
//        $ch = curl_init();
//        $timeout = 5;
//        $udemy_client_id = 'ouvVz2OU75BFkDelBNh3FWrgmTat8KGsaX6RZulO';
//        $udemy_client_secret = 'HWLP6AkQB87RhMcXh2qjzWmgd8fdO6zmHpkL86blTaRZuvDzdr8pFlRlQEEePzxyWUtO9IIRagY7PNFHEtQ5Kxea4ZJOx2H53NGQM5LeuA8TkmlxvkBYWgPrACIIFL0v';
//        $ch = curl_init();
//        curl_setopt($ch, CURLOPT_URL, 'https://www.udemy.com/api-2.0/courses');
//        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
//
//$data = curl_exec($ch);
//        print_object(json_decode($data, true));
//curl_close($ch);
        global $CFG;
        \core_component::classloader($CFG->dirroot . '/blocks/udemoodle/lib/vendor/aescarcha/udemy-api-client/src/aEscarcha/UdemyApiClient/Api.php');
        //require_once $CFG->dirroot . '/blocks/udemoodle/lib/vendor/aescarcha/udemy-api-client/src/aEscarcha/UdemyApiClient/Api.php';
        $api = new Api('ouvVz2OU75BFkDelBNh3FWrgmTat8KGsaX6RZulO', 'HWLP6AkQB87RhMcXh2qjzWmgd8fdO6zmHpkL86blTaRZuvDzdr8pFlRlQEEePzxyWUtO9IIRagY7PNFHEtQ5Kxea4ZJOx2H53NGQM5LeuA8TkmlxvkBYWgPrACIIFL0v');
        //$api = new Api('clientId', 'clientSecret');
        $course = $api->getUrl( 'http://www.udemy.com/api-2.0/courses/1');
//        $response = file_get_contents('https://www.udemy.com/api-2.0/courses');
//
//$response = json_decode($response);
//        print_object($response);
        $data = array();
        $data['items'][0] = ['a' => 'a'];
        $data['canmanage'] = true;
        $data['hasitems'] = !empty($useritems);
        $data['inventoryurl'] = new moodle_url('/blocks/udemoodle/items.php');
        return $data;
    }
}