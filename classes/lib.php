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
    private static bool $log_postparams = false;
    private static array $other_params = [];
    private static array $request_extraparams = [];

    public static function log_postparams(): void {
        self::$log_postparams = true;
    }

    public static function add_extraparam(string $cachetype, string $name, string $value): void {
        global $SESSION;

        if ($cachetype === 'request') {
            self::$request_extraparams[$name] = $value;
        } elseif ($cachetype === 'session') {
            if (!isset($SESSION->local_stats_extraparams)) {
                $SESSION->local_stats_extraparams = [];
            }
            $SESSION->local_stats_extraparams[$name] = $value;
        } else {
            throw new \moodle_exception('Invalid cache type ' . $cachetype);
        }
    }

    public static function remove_extraparam(string $cachetype, string $name): void {
        global $SESSION;

        if ($cachetype === 'request') {
            unset(self::$request_extraparams[$name]);
        } elseif ($cachetype === 'session') {
            unset($SESSION->local_stats_extraparams[$name]);
        } else {
            throw new \moodle_exception('Invalid cache type ' . $cachetype);
        }
    }

    private static function get_extraparams(string $cachetype): array {
        global $SESSION;

        if ($cachetype === 'request') {
            return self::$request_extraparams;
        } elseif ($cachetype === 'session') {
            return $SESSION->local_stats_extraparams ?? [];
        } else {
            throw new \moodle_exception('Invalid cache type ' . $cachetype);
        }
    }


    /**
     * Provides possibility to add additional request parameters as they were GET-parameters.
     */
    public static function add_other_param(string $name, string $value): void {
        self::$other_params[$name] = $value;
    }

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
            $icon = !empty($entry->icon) ? "<i class=\"fa {$entry->icon}\"></i>&nbsp;&nbsp;" : '';
            $nav->add($icon . $entry->label, $entry->link);
        }
    }

    /**
     * Add an entry to the local_stats-logging.
     * @param \moodle_url|null $url
     * @param int $contextid
     * @param int $userid , defaults to $USER->id
     * @param bool $with_postparams
     * @return void
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function log_record(?\moodle_url $url = null, int $contextid = 0, int $userid = 0, bool $with_postparams = false): void {
        global $DB, $USER;

        if (!$userid) {
            $userid = $USER->id;
        }

        if ($url) {
            $path = $url->get_path();
            $params = $url->get_query_string(false);
        } else {
            $path = strtok($_SERVER['REQUEST_URI'] ?? '', '?');
            $params = $_SERVER['QUERY_STRING'] ?? '';
        }

        // TODO for later: remove Moodle base path if in subdirectory.
        // For now keep the whole path
        /*
        $basepath = rtrim(parse_url($CFG->wwwroot, PHP_URL_PATH) ?: '', '/');
        if ($basepath && str_starts_with($path, $basepath)) {
            $path = substr($path, strlen($basepath));
        }
        */

        $path = substr($path, 0, 1333);

        if (static::$other_params) {
            $otherparams = http_build_query(static::$other_params, '', '&');
            if ($params) {
                $params .= '&';
            }
            $params .= $otherparams;
        }

        $params = strlen($params) > 1333 ? substr($params, 0, 1330) . '...' : $params;

        $postparams = null;
        if ((self::$log_postparams || $with_postparams) && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            // Truncate values longer than 255 chars in raw POST.
            $parts = explode('&', file_get_contents('php://input'));
            foreach ($parts as &$part) {
                $pos = strpos($part, '=');
                if ($pos !== false && strlen($part) - $pos - 1 > 255) {
                    $part = substr($part, 0, $pos + 253) . '...';
                }
            }
            $postparams = implode('&', $parts);
            $postparams = strlen($postparams) > 1333 ? substr($postparams, 0, 1330) . '...' : $postparams;
        }

        $record = (object)[
            'userid' => $userid,
            'contextid' => $contextid,
            'extraparams' => http_build_query(self::get_extraparams('request'), '', '&'),
            'extrasession' => http_build_query(self::get_extraparams('session'), '', '&'),
            'lang' => current_language(),
            'method' => $_SERVER['REQUEST_METHOD'] ?? '',
            'path' => $path,
            'params' => $params,
            'postparams' => $postparams,
            'referer' => !empty($_SERVER['HTTP_REFERER']) ? substr($_SERVER['HTTP_REFERER'], 0, 1333) : '',
            'remoteaddr' => $_SERVER['REMOTE_ADDR'] ?? '',
            'useragent' => !empty($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 1333) : '',
            'timecreated' => time(),
        ];

        $DB->insert_record('local_stats', $record);
    }
}
