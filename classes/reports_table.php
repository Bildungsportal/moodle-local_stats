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
    protected function define_table_configs() {
        $this->set_sql('*', 'local_stats_report');

        $this->set_table_columns([
            // 'id' => 'ID',
            'name' => get_string('name'),
            'enabled' => get_string('active'),
            'nextrun' => get_string('reporting:nextrun', 'local_stats'),
        ]);
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
                ['text' => get_string('yes'), 'value' => 1],
                ['text' => get_string('no'), 'value' => 0],
            ]
        );
        $this->sortable(true, 'name', SORT_ASC);
    }

    function col_enabled($row) {
        return $row->enabled ? get_string('yes') : get_string('no');
    }

    function col_nextrun($row) {
        if (empty($row->enabled) || empty($data->payload->cron_enabled))
            return get_string('never');
        return $row->nextrun ? date('Y-m-d H:i:s', $row->nextrun) : get_string('now');
    }
}
