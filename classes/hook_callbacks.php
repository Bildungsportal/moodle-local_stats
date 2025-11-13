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

use core\hook\after_config;

class hook_callbacks {
    public static function after_config(after_config $hook): void {
        global $CFG;

        if (during_initial_install()) {
            // Do nothing during installation
            return;
        }

        $CFG->chart_colorset = array_map('trim', explode("\n", get_config('local_stats', 'template_color_codes')));
    }
    public static function before_standard_head_html_generation($hook): void {
        global $DB, $PAGE, $USER;

        if (during_initial_install()) {
            // Do nothing during installation
            return;
        }

        if (!$DB->get_manager()->table_exists('local_stats')) {
            // During initial install of the plugin, the table does not exist yet.
            return;
        }

        if (!empty($USER->id) && is_object($PAGE->url)) {
            \local_stats\lib::log_record($PAGE->url, $PAGE->context->id);
        }
    }
}
