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

class reports_table extends \local_table_sql\table_sql {
    private array $reportcache = [];

    private function get_report(int $id): object {
        if (!isset($this->reportcache[$id])) {
            $this->reportcache[$id] = reportlib::get($id);
        }
        return $this->reportcache[$id];
    }

    protected function define_table_configs() {
        $this->set_sql_query('
            SELECT *, CASE WHEN enabled = 1 THEN nextrun ELSE -1 END AS nextrun_sort
            FROM {local_stats_report}
        ');

        $this->set_table_columns([
            // 'id' => 'ID',
            'name' => get_string('name'),
            'enabled' => get_string('active'),
            'cron' => get_string('cron', 'local_stats'),
            'nextrun' => get_string('reporting:nextrun', 'local_stats'),
        ]);
        $this->set_column_options('cron', no_sorting: true);
        $this->set_column_options('nextrun', sql_column: 'nextrun_sort');
        $this->add_row_action(
            new \moodle_url('/local/stats/run.php', ['id' => '{id}']),
            'execute',
            get_string('execute', 'webservice'),
            '',
            false,
            'fa-solid fa-play'
        );
        $this->add_row_action(
            new \moodle_url('/local/stats/data.php', ['id' => '{id}']),
            'view',
            get_string('view'),
            '',
            false,
            'fa-solid fa-table'
        );
        $this->add_row_action(
            new \moodle_url('/local/stats/report.php', ['id' => '{id}']),
            'edit',
            get_string('edit')
        );
        $this->set_column_options('enabled',
            sql_column: 'enabled',
            select_options: [
                1 => get_string('yes'),
                0 => get_string('no'),
            ]
        );
        $this->sortable(true, 'name', SORT_ASC);
    }

    function col_enabled($row) {
        return $row->enabled ? get_string('yes') : get_string('no');
    }

    function col_cron($row) {
        $report = $this->get_report($row->id);
        if (!$report->payload->cron_enabled) {
            return '';
        }
        return $report->payload->cronexpression ?? '';
    }

    function col_nextrun($row) {
        $report = $this->get_report($row->id);
        if (!$row->enabled || !$report->payload->cron_enabled) {
            return '';
        }
        return $row->nextrun ? date('Y-m-d H:i:s', $row->nextrun) : get_string('now');
    }

    function col_name($row) {
        $report = $this->get_report($row->id);
        if (empty($report->payload->query)) {
            return s($row->name) . '<br/><i class="fa fa-info-circle text-info"></i> <span class="text-info">' . get_string('report:query:empty', 'local_stats') . '</span>';
        }
        if ($match = reportlib::validate($report->payload->query)) {
            return s($row->name) . '<br/><i class="fa fa-exclamation-triangle text-danger"></i> <span class="text-danger">' . get_string('report:query:invalid', 'local_stats') . ': ' . s($match) . '</span>';
        }
        return s($row->name);
    }
}
