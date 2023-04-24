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

namespace mod_jupyter\external;

use external_function_parameters;
use external_multiple_structure;
use external_single_structure;

class submit_notebook extends \external_api {

    /**
     * Returns description of method parameters.
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
        ]);
    }

    /**
     * Does stuff
     */
    public static function execute() {
    }

    /**
     * Returns description of return values.
     * @return external_function_parameters
     */
    public static function execute_returns() {
        return new external_multiple_structure(
            new external_single_structure([
            ])
        );
    }
}
