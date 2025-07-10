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

class reportings_table extends \local_table_sql\table_sql_form {
    public function __construct() {
        parent::__construct([], []);
    }

    protected function define_table_configs() {
        $this->set_sql('*', 'local_stats_reporting');

        $this->set_table_columns([
            'enabled' => get_string('active'),
            'name' => get_string('name'),
            'nextrun' => get_string('reporting:nextrun', 'local_stats'),
        ]);
        $this->sortable(true, 'name', SORT_ASC);

        $this->add_form(
            'reporting',
            new reportings_form(),
            title: get_string('reporting', 'local_stats'),
        );

        $this->add_row_action(
            new \moodle_url('/local/stats/run_reporting.php', ['id' => '{id}']),
            'execute',
            get_string('execute', 'webservice'),
            '',
            false,
            'fa-solid fa-play'
        );
        $this->add_row_action(
            new \moodle_url('/local/stats/run_reporting.php', ['id' => '{id}', 'direct' => 1]),
            'download',
            get_string('download'),
            '',
            false,
            'fa-solid fa-paperclip'
        );
        $this->add_form_action('reporting');
        $this->add_row_action(
            new \moodle_url('/local/stats/reporting.php', ['id' => '{id}']),
            'other',
            get_string('files'),
            icon: 'fa-solid fa-folder-tree'
        );
    }

    function col_enabled($row) {
        return $row->enabled ? get_string('yes') : get_string('no');
    }

    function col_nextrun($row) {
        if (empty($row->enabled))
            return get_string('never');
        return $row->nextrun ? date('Y-m-d H:i:s', $row->nextrun) : get_string('now');
    }

    public function ajax_permission_check() {
        require_admin();
    }

    public function wrap_html_start() {
        parent::wrap_html_start();
        echo $this->as_modal_formfield(
            formid: 'reporting', btnlabel: get_string('create'), btnclass: 'btn btn-primary', btnlabelclass: '',
            icon: 'fa-solid fa-plus'
        );
    }
}
