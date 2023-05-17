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
 * Handles interaction with jupyter api.
 *
 * Reference for the used jupyterhub and jupyterlab api's:
 * https://jupyterhub.readthedocs.io/en/stable/reference/rest-api.html
 * https://jupyter-server.readthedocs.io/en/latest/developers/rest-api.html
 *
 * @package     mod_jupyter
 * @copyright   KIB3 StuPro SS2022 Development Team of the University of Stuttgart
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_jupyter;

defined('MOODLE_INTERNAL') || die();
require($CFG->dirroot . '/mod/jupyter/vendor/autoload.php');

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;

/**
 * Handles interaction with jupyter api.
 *
 * @package mod_jupyter
 */
class jupyterhub_handler {

    /** @var Client guzzle http client */
    private $client;

    /**
     * Constructor.
     */
    public function __construct() {
        $baseuri = get_config('mod_jupyter', 'jupyterhub_url');

        // If moodle is running in a docker container we have to replace '127.0.0.1' and 'localhost' with 'host.docker.internal'.
        // This is only relevant for local testing.
        if (getenv('IS_CONTAINER') == 'yes') {
            $baseuri = str_replace(['127.0.0.1', 'localhost'], 'host.docker.internal', $baseuri);
        }

        $this->client = new Client([
        'base_uri' => $baseuri,
        'headers' => [
          'Authorization' => 'token ' . get_config('mod_jupyter', 'jupyterhub_api_token')
        ]
          ]);
    }

    /**
     * Sets the private $client variable.
     *
     * @param Client $client guzzle http client
     */
    public function set_client($client) {
        $this->client = $client;
    }

    /**
     * Returns the url to users notebook and notebook file.
     *
     * @param string $user current user's username
     * @param int $contextid activity context id
     * @param int $courseid id of the moodle course
     * @param int $instanceid activity instance id
     * @param string $filename notebook filename
     * @return string path to file on jupyterhub server
     * @throws ConnectException
     * @throws RequestException
     */
    public function get_notebook_path(string $user, int $contextid, int $courseid, int $instanceid, string $filename) : string {
        $this->check_user_status($user);

        $route = "/user/{$user}/api/contents";

        try {
            // Check if file is already there.
            $this->client->get("{$route}/{$courseid}/{$instanceid}/{$filename}", ['query' => ['content' => '0']]);
        } catch (RequestException $e) {
            if ($e->hasResponse() && $e->getCode() == 404) {
                $fs = get_file_storage();
                $files = $fs->get_area_files($contextid, 'mod_jupyter', 'assignment', 0, 'id', false);
                $file = reset($files);

                // Jupyter api doesnt support creating directorys recursively so we have to it like this.
                $this->client->put("{$route}/{$courseid}", ['json' => ['type' => 'directory']]);
                $this->client->put("{$route}/{$courseid}/{$instanceid}", ['json' => ['type' => 'directory']]);

                $this->client->put("{$route}/{$courseid}/{$instanceid}/{$filename}", ['json' => [
                'type' => 'file',
                'format' => 'base64',
                'content' => base64_encode($file->get_content()),
                ]]);
            } else {
                throw $e;
            }
        }

        return "/hub/user-redirect/lab/tree/{$courseid}/{$instanceid}/{$filename}";
    }

    /**
     * Check if user exists and spawn server
     * @param string $user current user's username
     * @throws ConnectException
     * @throws RequestException
     */
    private function check_user_status(string $user) {
        $route = "/hub/api/users/{$user}";
        // Check if user exists.
        try {
            $res = $this->client->get($route);
        } catch (RequestException $e) {
            // Create user if not found.
            if ($e->hasResponse() && $e->getCode() == 404) {
                $res = $this->client->post($route);
            } else {
                // For other errors we throw the exception.
                throw $e;
            }
        }

        // Spawn users server if not running.
        if (json_decode($res->getBody(), true)["server"] == null) {
            $res = $this->client->post($route . "/server");
        }
    }
}
