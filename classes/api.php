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
    public static function add_extraparam(string $cachetype, string $name, string $value) {
        if (!in_array($cachetype, ['request', 'session']))
            throw new \moodle_exception('Invalid cache type ' . $cachetype);
        if ($name == '__index')
            throw new \moodle_exception('Invalid cache name ' . $name);
        $cache = \cache::make('local_stats', "extraparams_{$cachetype}");
        $cache->set($name, $value);

        // TODO: was macht __index, für was wird das gebraucht? geht die Sortierung der Parameter im cache verloren?
        // TODO: besser als array lösen: [[key1, value1], [key2, value2], ...]

        // TODO: für request parameter: ginge das nicht einfach als statische variable anstatt mit dem cache?
        $index = $cache->get('__index');
        if (!is_array($index))
            $index = [];
        if (!in_array($name, $index))
            $index[] = $name;
        $cache->set('__index', $index);
    }

    /**
     * Add an entry to the local_stats-logging.
     * @param \moodle_url $url
     * @param int $contextid
     * @param int $userid, defaults to $USER->id
     * @return void
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function log_record(\moodle_url $url, int $contextid = 0, int $userid = 0) {
        \local_stats\lib::log_record($url, $contextid, $userid);
    }

    public static function remove_extraparam(string $cachetype, string $name) {
        if (!in_array($cachetype, ['request', 'session']))
            throw new \moodle_exception('Invalid cache type ' . $cachetype);
        if ($name == '__index')
            throw new \moodle_exception('Invalid cache name ' . $name);
        $cache = \cache::make('local_stats', "extraparams_{$cachetype}");
        $cache->delete($name);
        $index = $cache->get('__index');
        if (!is_array($index))
            $index = [];
        if (($key = array_search($name, $index)) !== false) {
            unset($index[$key]);
        }
        $cache->set('__index', $index);
    }

    public static function get_extraparams(string $cachetype): string {
        if (!in_array($cachetype, ['request', 'session']))
            throw new \moodle_exception('Invalid cache type ' . $cachetype);
        $cache = \cache::make('local_stats', "extraparams_{$cachetype}");

        // TODO: was macht __index, für was wird das gebraucht? geht die Sortierung der Parameter im cache verloren?
        $index = $cache->get('__index');
        if (!is_array($index))
            return '';
        $index = array_unique($index);
        $params = [];
        foreach ($index as $name) {
            $params[] = urlencode($name) . '=' . urlencode($cache->get($name));
        }
        return implode('&', $params);
    }
}
