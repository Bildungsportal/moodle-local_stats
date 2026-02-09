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

class raw_table extends \local_table_sql\table_sql {
    protected function define_table_configs() {
        $this->set_sql('*', 'local_stats');
        $this->set_table_columns([
            'id' => '#',
            'userid' => 'userid',
            'contextid' => 'contextid',
            'lang' => 'lang',
            'method' => 'method',
            'path' => 'path',
            'params' => 'params',
            'extraparams' => 'extraparams',
            'extrasession' => 'extrasession',
            'postparams' => 'postparams',
            'referer' => 'referer',
            'remoteaddr' => 'remoteaddr',
            'useragent' => 'useragent',
            'timecreated' => 'timecreated',
        ]);
        $this->sortable(true, 'timecreated', SORT_DESC);
        $this->set_hidden_columns(['method', 'referer', 'remoteaddr', 'useragent']);
    }
}
