<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Handles interaction with gradeservice api.
 *
 * @package     mod_jupyter
 * @copyright   KIB3 StuPro SS2022 Development Team of the University of Stuttgart
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_jupyter;

defined('MOODLE_INTERNAL') || die();
require($CFG->dirroot . '/mod/jupyter/vendor/autoload.php');

use coding_exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Handles interaction with gradeservice api.
 *
 * @package mod_jupyter
 */
class gradeservice_handler {

    /** @var Client guzzle http client */
    private $client;

    /**
     * Constructor
     */
    public function __construct() {
        $baseuri = get_config('mod_jupyter', 'gradeservice_url');

        if (getenv('IS_CONTAINER') == 'yes') {
            $baseuri = str_replace(['127.0.0.1', 'localhost'], 'host.docker.internal', $baseuri);
        }

        $this->client = new Client([
            'base_uri' => $baseuri,
        ]);
    }

    /**
     * Create an assignment.
     *
     * @param int $contextid activity context id
     * @param int $courseid id of the moodle course
     * @param int $instanceid activity instance id
     * @param string $token authorization token
     * @return string filename of the created assignment
     * @throws coding_exception
     * @throws GuzzleException
     */
    public function create_assignment(int $contextid, int $courseid,  int $instanceid, string $token) : string {
        global $DB;

        $fs = get_file_storage();
        $files = $fs->get_area_files($contextid, 'mod_jupyter', 'package', 0, 'id', false);
        $file = reset($files);
        $filename = $file->get_filename();

        $route = "/{$courseid}/{$instanceid}";

        $res = $this->client->request("POST", $route, [
            'headers' => [
                'Authorization' => $token,
            ],
            'multipart' => [
                [
                    'name' => 'file',
                    'contents' => $file->get_content(),
                    'filename' => $filename,
                ]
            ]
        ]);

        $file = base64_decode(json_decode($res->getBody(), true)[$filename]);

        $fs->delete_area_files($contextid, 'mod_jupyter', 'assignment');
        $fileinfo = array(
            'contextid' => $contextid,
            'component' => 'mod_jupyter',
            'filearea' => 'assignment',
            'itemid' => 0,
            'filepath' => '/',
            'filename' => $filename
        );
        $fs->create_file_from_string($fileinfo, $file);
        $DB->set_field('jupyter', 'assignment', $filename, ['id' => $instanceid, 'course' => $courseid]);

        return $filename;
    }
}