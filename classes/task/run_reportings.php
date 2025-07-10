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

namespace local_stats\task;

defined('MOODLE_INTERNAL') || die;

class run_reportings extends \core\task\scheduled_task {
    public function get_name() {
        return get_string('task:run_reportings', 'local_stats');
    }

    public function execute() {
        global $DB;
        $reportingids = array_keys($DB->get_records_select('local_stats_reporting', 'enabled = 1 AND ((modescheduled = 1 AND nextrun < ?) OR modetriggered = 1)', [time()], 'id'));
        foreach ($reportingids as $reportingid) {
            mtrace("=> Run #{$reportingid}");
            \local_stats\reportinglib::run($reportingid);
        }
    }
}
