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

use core_block\navigation\views\secondary;

defined('MOODLE_INTERNAL') || die;

class lib {
    public static function add_nav($reportingorid = 0, $reportorid = 0) {
        global $PAGE;
        $report = is_int($reportorid) && $reportorid > 0 ? reportlib::get($reportorid) : $reportorid;
        $reporting = is_int($reportingorid) && $reportingorid > 0 ? reportinglib::get($reportingorid) : $reportingorid;

        $nav = new secondary($PAGE);
        $nav->initialise();
        $PAGE->set_secondarynav($nav);

        $entries = [
            (object)[
                'class' => 'btn btn-link',
                'icon' => 'fa-solid fa-magnifying-glass',
                'label' => get_string('raw_data', 'local_stats'),
                'link' => new \moodle_url('/local/stats/raw.php', []),
                'target' => '_self',
            ],
            (object)[
                'class' => 'btn btn-link',
                'icon' => 'fa-solid fa-file-waveform',
                'label' => get_string('reportings', 'local_stats'),
                'link' => new \moodle_url('/local/stats/reportings.php', []),
                'target' => '_self',
            ],
        ];
        if ($reportingorid > 0) {
            $entries[] = (object)[
                'class' => 'btn btn-link',
                'icon' => 'fa-solid fa-folder-tree',
                'label' => $reporting->name,
                'link' => new \moodle_url('/local/stats/reporting.php', ['id' => $reporting->id]),
                'target' => '_self',
            ];
        }
        $entries = array_merge($entries, [
            (object)[
                'class' => 'btn btn-link',
                'icon' => 'fa-list',
                'label' => get_string('reports', 'local_stats'),
                'link' => new \moodle_url('/local/stats/reports.php', []),
                'target' => '_self',
            ],
            (object)[
                'class' => 'btn btn-link',
                'icon' => 'fa-add',
                'label' => get_string('create'),
                'link' => new \moodle_url('/local/stats/report.php', []),
                'target' => '_self',
            ],
        ]);

        if ($reportorid > 0) {
            $entries[] = (object)[
                'class' => 'btn btn-link',
                'icon' => 'fa-solid fa-table',
                'label' => $report->name,
                'link' => new \moodle_url('/local/stats/data.php', ['id' => $report->id]),
                'target' => '_self',
            ];
            $entries[] = (object)[
                'class' => 'btn btn-link',
                'icon' => 'fa fa-edit',
                'label' => get_string('edit'),
                'link' => new \moodle_url('/local/stats/report.php', ['id' => $report->id]),
                'target' => '_self',
            ];
            $entries[] = (object)[
                'class' => 'btn btn-link',
                'icon' => 'fa-solid fa-play',
                'label' => get_string('execute', 'webservice'),
                'link' => new \moodle_url('/local/stats/run.php', ['id' => $report->id, 'backto' => $PAGE->url]),
                'target' => '_self',
            ];
        }

        foreach ($entries as $entry) {
            $icon = !empty($entry->icon) ? "<i class=\"fa {$entry->icon}\"></i>&nbsp;" : '';
            $nav->add($icon . $entry->label, $entry->link);
        }
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
        global $DB, $USER;
        if (empty($userid)) {
            $userid = $USER->id;
        }
        // Exit if page was not setup correctly.
        if (!is_object($url))
            return;
        $record = (object)[
            'userid' => $userid,
            'contextid' => $contextid,
            'extraparams' => \local_stats\api::get_extraparams('request'),
            'extrasession' => \local_stats\api::get_extraparams('session'),
            'lang' => current_language(),
            'path' => substr($url->get_path(), 0, 1333),
            'params' => substr($url->get_query_string(false), 0, 1333),
            'timecreated' => time(),
        ];
        $DB->insert_record('local_stats', $record);
    }

    // keine Rechte am Server um npm install auszuführen
    // public static function npm_install() {
    //     passthru('cd ' . __DIR__ . '/../chart-app && npm install --omit=dev 2>&1');
    // }
}
