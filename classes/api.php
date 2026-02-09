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
 * @package    local_stats
 * @copyright  2024 Austrian Federal Ministry of Education
 * @author     GTN solutions
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_stats;


defined('MOODLE_INTERNAL') || die;

class api {
    public static function add_extraparam(string $cachetype, string $name, string $value): void {
        lib::add_extraparam($cachetype, $name, $value);
    }

    public static function remove_extraparam(string $cachetype, string $name): void {
        lib::remove_extraparam($cachetype, $name);
    }

    /**
     * Provides possibility to add additional request parameters as they were GET-parameters.
     */
    public static function add_other_param(string $name, string $value): void {
        lib::add_other_param($name, $value);
    }

    public static function log_postparams(): void {
        lib::log_postparams();
    }

    /**
     * Add an entry to the local_stats-logging.
     * @param \moodle_url $url
     * @param int $contextid
     * @param int $userid , defaults to $USER->id
     * @return void
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function log_record(?\moodle_url $url = null, int $contextid = 0, int $userid = 0, $with_postparams = false): void {
        lib::log_record($url, $contextid, $userid, $with_postparams);
    }
}
