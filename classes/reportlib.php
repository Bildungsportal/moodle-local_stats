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

use core\chart_base;

defined('MOODLE_INTERNAL') || die;

class reportlib {
    public const REPORT_VERSION = 2024120900;

    public static function get_chart(object $report, string $forcedtype = null): chart_base {
        $type = $forcedtype ?? $report->payload->charttype;

        switch ($type) {
            case 'bar':
                $chart = new \core\chart_bar();
                break;
            case 'bar_stacked':
                $chart = new \core\chart_bar();
                $chart->set_stacked(true);
                break;
            case 'bar_horiz':
                $chart = new \core\chart_bar();
                $chart->set_horizontal(true);
                break;
            case 'bar_stacked_horiz':
                $chart = new \core\chart_bar();
                $chart->set_horizontal(true);
                $chart->set_stacked(true);
                break;
            case 'line':
                $chart = new \core\chart_line();
                break;
            case 'line_smooth':
                $chart = new \core\chart_line();
                $chart->set_smooth(true);
                break;
            case 'pie':
                $chart = new \core\chart_pie();
                break;
            case 'pie_doughnut':
                $chart = new \core\chart_pie();
                $chart->set_doughnut(true);
                break;
            default:
                throw new \moodle_exception("Invalid chart type '{$type}'");
        }

        return $chart;
    }

    public static function get_chart_filled(object $report) {
        global $CFG;
        $data = static::get_data_for_purpose($report, 'graph');
        $chart = static::get_chart($report, $report->payload->charttype);
        $CFG->chart_colorset = array_map('trim', explode("\n", get_config('local_stats', 'template_color_codes')));
        $chart->set_legend_options(['position' => 'bottom']);
        $chart->set_title($report->name);

        if (count($data->sumserie) > 0) {
            $serie = new \core\chart_series(get_string('aggregationsum', 'reportbuilder'), array_values($data->sumserie));
            $serie->set_type(\core\chart_series::TYPE_LINE);
            $chart->add_series($serie);
        }
        if (in_array($report->payload->charttype, ['pie', 'pie_doughnut'])) {
            $chart->set_labels($data->subids);
            $serie = [];
            foreach ($data->subids as $subid) {
                $serie[] = $data->series[$subid][$data->periodids[0]];
            }
            $chart->add_series(new \core\chart_series($data->periodids[0], $serie));
        } else {
            $chart->set_labels($data->periodids);
            foreach ($data->series as $subid => $serie) {
                $chart->add_series(new \core\chart_series($subid, array_values($serie)));
            }
        }
        return $chart;
    }

    public static function get(int $id, bool $with_config_overrides = true): object {
        global $CFG, $DB;
        if (!empty($id)) {
            $report = $DB->get_record('local_stats_report', ['id' => $id], '*', MUST_EXIST);
        } else {
            $report = (object)[
                'enabled' => 1,
                'id' => 0,
                'name' => '',
                'description' => '',
                'lasttimecreated' => 0,
            ];
        }

        static::upgrade($report);

        // allow overriding the report settings from config.php
        if ($with_config_overrides && isset($CFG->local_stats_reports[$report->id])) {
            $report_config = $CFG->local_stats_reports[$report->id];

            // if entry is just a string, then it is the query
            if (is_string($report_config)) {
                $report_config = ['query' => $report_config];
            }

            // else override all settings
            if (is_array($report_config) || is_object($report_config)) {
                foreach ($report_config as $key => $value) {
                       $report->payload->$key = $value;
                }
            }
        }

        return $report;
    }

    /**
     * Get the data of the report for PDF-export.
     * @param object $report
     * @return object
     */
    public static function get_data(object $report): object {
        global $DB;
        $data = (object)[
            'periodids' => [],
            'series' => [],
            'subids' => [],
            'sumserie' => [],
        ];
        $data->periodids = array_keys($DB->get_records_sql('SELECT DISTINCT(periodid) FROM {local_stats_data} WHERE reportid = ? ORDER BY periodid ASC', [$report->id]));
        while ($report->payload->amount_keep > 0 && count($data->periodids) > $report->payload->amount_keep) {
            // Shift the oldest period until amount fits adn delete obsolete data.
            $removeperiod = array_shift($data->periodids);
            $DB->delete_records('local_stats_data', ['reportid' => $report->id, 'periodid' => $removeperiod]);
        }
        while ($report->payload->amount_show > 0 && count($data->periodids) > $report->payload->amount_show) {
            // Just shift the oldest period until amount fits.
            array_shift($data->periodids);
        }

        $data->subids = [];

        [$insql, $inparams] = $DB->get_in_or_equal($data->periodids, onemptyitems: -1);
        $params = array_merge([$report->id], $inparams);
        $sql = "
            SELECT DISTINCT(subid)
            FROM {local_stats_data}
            WHERE reportid = ?
                AND periodid $insql
            ORDER BY subid ASC
        ";
        $subids = $DB->get_records_sql($sql, $params);
        foreach ($subids as $rec) {
            $data->subids[] = !empty($rec->subid) ? $rec->subid : get_string('subid:empty', 'local_stats');
        }
        foreach ($data->subids as $subid) {
            if (empty($subid)) {
                $subid = get_string('subid:empty', 'local_stats');
            }
            $data->series[$subid] = [];
            foreach ($data->periodids as $periodid) {
                $data->series[$subid][$periodid] = 0;
                if (!empty($report->payload->sumgraph)) {
                    $data->sumserie[$periodid] = 0;
                }
            }
        }
        $sql = "
            SELECT *
            FROM {local_stats_data}
            WHERE reportid = ?
                AND periodid $insql
            ORDER BY periodid ASC, subid ASC
        ";
        $records = $DB->get_records_sql($sql, $params);
        foreach ($records as $record) {
            if (empty($record->subid)) {
                $record->subid = get_string('subid:empty', 'local_stats');
            }
            $data->series[$record->subid][$record->periodid] = $record->periodvalue;
            if (!empty($report->payload->sumgraph)) {
                $data->sumserie[$record->periodid] = $data->sumserie[$record->periodid] + $record->periodvalue;
            }
        }
        if ($report->payload->hide_empty_subids == 1) {
            foreach ($data->subids as $subid) {
                if (array_sum($data->series[$subid]) == 0) {
                    unset($data->series[$subid]);
                }
            }
        }
        switch ($report->payload->sort_by) {
            case 'name':
                if ($report->payload->sort_type == 'ASC') {
                    ksort($data->series);
                } else {
                    krsort($data->series);
                }
                break;
            case 'value':
                if ($report->payload->sort_type == 'ASC') {
                    asort($data->series);
                } else {
                    arsort($data->series);
                }
                break;
        }
        // Recalculate subids to follow new sorting
        $data->subids = array_keys($data->series);
        return $data;
    }

    /**
     * Switch axes based on switch_axes_data or switch_axes_graph and the given purpose
     * @param object $report the report object
     * @param string $purpose 'data' or 'graph'
     * @return void
     */
    public static function get_data_for_purpose(object $report, string $purpose, object $data = null): object {
        if (empty($data)) {
            $data = static::get_data($report);
        }
        if (!in_array($purpose, ['data', 'graph']))
            return $data;
        if (empty($report->payload->{"switch_axes_{$purpose}"}))
            return $data;
        // Switch axes -> subids become periodids, re-calcluate sum-serie
        // All variables with "_"-prefix follow the new ordering.
        $_data = (object)[
            'periodids' => [],
            'series' => [],
            'subids' => [],
            'sumserie' => [],
        ];

        $subids = array_keys($data->series);
        foreach ($subids as $subid) {
            $_data->periodids[] = $subid;
            if (!empty($report->payload->sumgraph) && empty($_data->sumserie[$subid])) {
                $_data->sumserie[$subid] = 0;
            }
            foreach ($data->series[$subid] as $periodid => $periodvalue) {
                if (empty($_data->series[$periodid]))
                    $_data->series[$periodid] = [];
                $_data->series[$periodid][$subid] = $periodvalue;
                if (!empty($report->payload->sumgraph)) {
                    $_data->sumserie[$subid] = $_data->sumserie[$subid] + $periodvalue;
                }
            }
        }
        $_data->periodids = $subids;
        $_data->subids = array_values($data->periodids);

        return $_data;
    }

    /**
     * Run a report.
     * @param int $reportid
     * @return object the report
     * @throws \dml_exception
     */
    public static function run(int $reportid): object {
        global $DB;
        $report = static::get($reportid);

        if (empty($report->payload->query)) {
            throw new \moodle_exception('report_query_is_empty');
        }

        if ($match = static::validate($report->payload->query)) {
            throw new \moodle_exception('report:query:contains_malicious_sql', 'local_stats', '', $match);
        }

        if ($report->payload->wipedata) {
            static::wipe($reportid);
            $report->lasttimecreated = 0;
        }
        $results = $DB->get_recordset_sql($report->payload->query, [$report->lasttimecreated]);
        $lasttimecreated = $report->lasttimecreated;
        foreach ($results as $result) {
            if (empty($result->periodid)) {
                \mtrace("Adding result to {$report->name} ({$report->id}) denied due to missing periodid");
            } else {
                $result->reportid = $report->id;
                $cntparams = ['reportid' => $report->id, 'periodid' => $result->periodid, 'subid' => $result->subid];
                if ($DB->count_records('local_stats_data', $cntparams) > 0) {
                    $DB->set_field('local_stats_data', 'periodvalue', $result->periodvalue, $cntparams);
                } else {
                    $DB->insert_record('local_stats_data', $result);
                }
                if ($lasttimecreated < $result->lasttimecreated) {
                    $lasttimecreated = $result->lasttimecreated;
                }
            }
        }
        $results->close();
        $report->lasttimecreated = $lasttimecreated;
        if (empty($report->payload->cron_enabled)) {
            $report->nextrun = 0;
        } else {
            $report->nextrun = \local_stats\cronlib::get_next_run_time($report->payload->cronexpression);
        }
        $report->payload = json_encode($report->payload, JSON_PRETTY_PRINT);
        $DB->update_record('local_stats_report', $report);

        return $report;
    }

    public static function set(object $report) {
        global $DB;
        if (!is_string($report->payload)) {
            $report->payload->cronexpression = implode(' ', [
                $report->payload->cron_minute, $report->payload->cron_hour, $report->payload->cron_day,
                $report->payload->cron_month, $report->payload->cron_dayofweek,
            ]);
            if (empty($report->payload->cron_enabled)) {
                $report->nextrun = 0;
            } else {
                $report->nextrun = \local_stats\cronlib::get_next_run_time($report->payload->cronexpression);
            }
            $report->payload = json_encode($report->payload, JSON_PRETTY_PRINT);
        }
        if (!empty($report->id)) {
            $DB->update_record('local_stats_report', $report);
        } else {
            $DB->insert_record('local_stats_report', $report);
        }
    }

    private static function upgrade(&$report) {
        global $DB;
        if (!empty($report->payload)) {
            $report->payload = json_decode($report->payload);
        } else {
            $report->payload = (object)[
                'version' => static::REPORT_VERSION,
                'cron_enabled' => 0,
                'cron_minute' => '0',
                'cron_hour' => '12',
                'cron_day' => '*',
                'cron_month' => '*',
                'cron_dayofweek' => '*',
                // don't load query from config anymore, config could contain malicious sql
                // 'query' => get_config('local_stats', 'template_sql_query'),
                'query' => '',
                'type' => 'line',
            ];
        }
        $changed = false;
        if ($report->payload->version < 2024010800) {
            $report->payload->wipedata = 0;
            $changed = true;
        }
        if ($report->payload->version < 2024030100) {
            $report->payload->sumgraph = 0;
            $changed = true;
        }
        if ($report->payload->version < 2024041600) {
            $report->payload->cron_enabled = 0;
            $report->payload->cron_minute = '0';
            $report->payload->cron_hour = '12';
            $report->payload->cron_day = '*';
            $report->payload->cron_month = '*';
            $report->payload->cron_dayofweek = '*';
            $changed = true;
        }
        if ($report->payload->version < 2024051300) {
            $report->payload->amount_keep = 0;
            $report->payload->amount_show = 0;
            $changed = true;
        }
        if ($report->payload->version < 2024052400) {
            $report->payload->max_number_for_col_captions = 10;
            $changed = true;
        }
        if ($report->payload->version < 2024061100) {
            $report->payload->switch_axes_data = 0;
            $report->payload->switch_axes_graph = 0;
            $changed = true;
        }
        if ($report->payload->version < 2024120400) {
            $report->payload->hide_empty_subids = 0;
            $changed = true;
        }
        if ($report->payload->version < 2024120900) {
            $report->payload->sort_by = 'name'; // or 'value'
            $report->payload->sort_type = 'ASC'; // or 'DESC'
            $changed = true;
        }
        if ($changed) {
            $report->payload->version = static::REPORT_VERSION;
            $DB->set_field('local_stats_report', 'payload', json_encode($report->payload, JSON_PRETTY_PRINT), ['id' => $report->id]);
        }
    }

    /**
     * Validate that certain keywords are not used within the query.
     * @param string $query
     * @return string Empty string if valid, otherwise the matched keyword.
     */
    public static function validate(string $query): string {
        $prohibited = [
            '\bUPDATE\b',
            '\bINSERT\b',
            '\bDELETE\b',
            '\bTRUNCATE\b',
            '\bDROP\b',
            '\bALTER\b',
            '\bCREATE\b',
            '\bREPLACE\s+INTO\b',
            '\bRENAME\b',
            '\bGRANT\b',
            '\bREVOKE\b',
            '\bEXECUTE\b',
            '\bLOCK\b',
            '\bUNLOCK\b',
        ];
        $query = strtoupper($query);

        foreach ($prohibited as $pattern) {
            if (preg_match('!' . $pattern . '!', $query, $matches)) {
                return $matches[0];
            }
        }
        return '';
    }

    /**
     * Wipe all data of a report.
     * @param int $reportid
     * @return void
     * @throws \dml_exception
     */
    public static function wipe(int $reportid) {
        global $DB;
        $DB->delete_records('local_stats_data', ['reportid' => $reportid]);
        $DB->set_field('local_stats_report', 'lasttimecreated', 0, ['id' => $reportid]);
    }
}
