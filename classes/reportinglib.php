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
use core_block\navigation\views\secondary;

defined('MOODLE_INTERNAL') || die;

class reportinglib {
    public const REPORTING_VERSION = 2024051301;

    public static function get(int $id): object {
        global $DB;

        if ($id) {
            $reporting = $DB->get_record('local_stats_reporting', ['id' => $id], '*', MUST_EXIST);
        } else {
            $reporting = (object)[
                'id' => 0,
                'name' => '',
                'description' => '',
            ];
        }

        static::upgrade($reporting);

        return $reporting;
    }

    public static function get_files(int $reportingid): array {
        global $DB;
        $sql = "
                SELECT *
                FROM {files}
                WHERE component = 'local_stats'
                    AND filearea = 'reporting'
                    AND itemid = ?
                    AND filename <> '.'
                ORDER BY filepath DESC, filename ASC
            ";
        $params = [$reportingid];
        $filerecords = $DB->get_records_sql($sql, $params);
        $files = [];
        $curpath = '';
        foreach ($filerecords as $file) {
            $arfilepath = trim($file->filepath, '/');
            if ($curpath != $arfilepath) {
                $curpath = $arfilepath;
                $files[$arfilepath] = $fs = (object) [
                    'filepath' => $arfilepath,
                    'files' => [],
                ];
            }
            $file->url = \moodle_url::make_pluginfile_url(
                $file->contextid,
                $file->component,
                $file->filearea,
                $file->itemid,
                $file->filepath,
                $file->filename,
                true
            );
            $fs->files[] = $file;
        }
        return array_values($files);
    }

    /**
     * Run a reporting.
     * @param int $reportingid
     * @param bool $forcerun
     * @parma bool $directprint causes direct output of pdf instead of notifications.
     * @return object the reporting
     * @throws \dml_exception
     */
    public static function run(int $reportingid, bool $forcerun = false, bool $directprint = false): object {
        global $CFG, $DB, $PAGE, $USER;
        // Ensure the cache path of TCPDF exists
        if (!file_exists("$CFG->cachedir/tcpdf")) {
            mkdir("$CFG->cachedir/tcpdf");
        }
        $reporting = static::get($reportingid);
        $reports = [];
        $triggers = []; // Indicate which triggers fired
        // 1. Get all data
        $allreports = array_unique(array_merge($reporting->payload->reports_graph, $reporting->payload->reports_table));
        foreach ($allreports as $reportid) {
            $report = reportlib::get($reportid);
            $reports["{$report->name}-{$report->id}"] = (object) [
                'data' => reportlib::get_data($report),
                'report' => $report
            ];
        }
        ksort($reports);
        $reports = array_values($reports);

        // 2. Decide if reporting must be exported to PDF
        $SENDREPORT = $forcerun || $reporting->modescheduled && $reporting->nextrun <= time();
        // Check if any triggers fired
        $i = 0;
        while(!empty($reporting->payload->{"trigger_{$i}_operator"})) {
            foreach ($reports as $xreport) {
                foreach ($xreport->data->series as $subid => $serie) {
                    if (empty($reporting->payload->trigger_modeendless) && in_array($subid, $reporting->payload->trigger_unarmed)) continue;
                    $regex = str_replace('{empty}', get_string('subid:empty', 'local_stats'), $reporting->payload->{"trigger_{$i}_regex"});
                    if (empty($reporting->payload->{"trigger_{$i}_regex"}) || preg_match($regex, trim($subid))) {
                        $periods = array_keys($serie);
                        $operator = $reporting->payload->{"trigger_{$i}_operator"};
                        $firevalue = $reporting->payload->{"trigger_{$i}_value"};
                        $curvalue = $serie[$periods[count($periods)-1]];

                        // Detect strings
                        $curvalue = is_numeric($curvalue) ? $curvalue : "'{$curvalue}'";
                        $firevalue = is_numeric($firevalue) ? $firevalue : "'{$firevalue}'";
                        $fire = false;
                        eval("\$fire = ({$curvalue} {$operator} {$firevalue});");

                        if ($fire) {
                            $SENDREPORT = true;
                            $triggers[] = (object) [ 'curvalue' => $curvalue, 'firevalue' => $firevalue, 'operator' => $operator, 'regex' => $regex, 'reportname' => $xreport->report->name, 'subid' => $subid ];
                            $reporting->payload->trigger_unarmed[] = $subid;
                            $DB->set_field('local_stats_reporting', 'payload', json_encode($reporting->payload, JSON_PRETTY_PRINT), ['id' => $reporting->id]);
                        }
                    }
                }
            }
            $i++;
        }

        // 3. If report should not be sent, do not produce PDF as well.
        if (!$SENDREPORT) {
            if (CLI_SCRIPT) mtrace("Reporting {$reporting->name} is not sent, return");
            return $reporting;
        }

        if (CLI_SCRIPT) mtrace("Send Reporting {$reporting->name}, has " . count($triggers) . " fired triggers");

        // 4. Generate PDF
        if (CLI_SCRIPT) mtrace("Generate PDF");
        $pdf = new \local_stats\tcpdf_table($reporting->payload->orientation, 'mm', 'A4', true, 'UTF-8', false);
        // set document information
        $pdf->SetCreator('local_stats');
        $pdf->SetAuthor(\fullname($USER));
        $pdf->SetTitle(get_string('reporting', 'local_stats') . ': ' . $reporting->name);
        $pdf->SetSubject(get_string('reporting', 'local_stats') . ': ' . $reporting->name);

        $pdf->AddPage();
        $strtriggers = [];
        foreach ($triggers as $triggerparams) {
            $strtriggers[] = get_string('trigger:fired', 'local_stats', $triggerparams);
        }
        if (count($strtriggers) > 0) {
            $strtrigger = get_string('trigger', 'local_stats');
            $strtriggers = implode('</li><li>', $strtriggers);
            $strtriggers = <<<EOD
                    <div><strong>{$strtrigger}</strong><hr />
                    <ul><li>{$strtriggers}</li></ul></div>
                EOD;
        } else {
            $strtriggers=  '';
        }
        $date = date('Y-m-d H:i:s');
        $description = nl2br($reporting->payload->description);
        $pdf->WriteHTML(<<<EOD
                <h1 align="center">{$reporting->name}</h1>
                <p align="center"><i>{$description}</i></p>
                <p align="center"><i>$date</i></p>
                <p>&nbsp;</p>
                {$strtriggers}
            EOD);

        $PAGE->set_url('/local/stats/reporting.php', ['id' => $reportingid]);
        $PAGE->set_pagelayout('print');
        $PAGE->set_heading($reporting->name);
        $PAGE->set_title($reporting->name);

        $fs = get_file_storage();
        $subdir = "/report_" . date('Ymd-His') . "/";

        foreach ($reports as $xreport) {
            $report = $xreport->report;
            $description = nl2br($report->description);
            $chart = reportlib::get_chart_filled($report);

            // Get the static image
            $tmpfile = tmpfile();
            $tmppath = stream_get_meta_data($tmpfile)['uri'];

            $pdf->AddPage();
            $pdf->Bookmark($report->name, 0, 0, '', 'B', array(0,64,128));
            $pdf->WriteHTML("<h1>{$report->name}</h1>");
            $pdf->WriteHTML("<p><i>{$description}</i><br />&nbsp;</p>");
            if (in_array($report->id, $reporting->payload->reports_graph)) {
                $pdf->Bookmark(get_string('statisticsgraph', 'moodle'), 1, 0, '', '', array(0, 0, 0));
                $pdf->WriteHTML('<p><strong>' . get_string('statisticsgraph', 'moodle') . '</strong></p>');
                if (static::render_chart_to_file($chart, $tmppath, 1024)) {
                    $imagecontent = file_get_contents($tmppath);
                    $filename = "chart_{$report->id}.png";
                    $fileinfo = [
                        'contextid' => (\context_system::instance())->id,
                        'component' => 'local_stats',
                        'filearea' => "reporting",
                        'itemid' => $reportingid,
                        'filepath' => $subdir,
                        'filename' => $filename,
                    ];
                    $fs->create_file_from_string($fileinfo, $imagecontent);
                    $imagecontent = base64_encode($imagecontent);
                    $pdf->WriteHTML(<<<EOD
                        <p><img src="@{$imagecontent}" style="max-height: 100%; max-width: 100%;" /></p>
                    EOD
                    );
                } else {
                    $pdf->WriteHTML("<p><i>Generation of graph failed</i></p>");
                }
            }
            if (in_array($report->id, $reporting->payload->reports_table)) {
                $xreport->data = reportlib::get_data_for_purpose($report, 'data', $xreport->data);
                if (in_array($report->id, $reporting->payload->reports_graph)) {
                    $pdf->AddPage();
                }
                $pdf->Bookmark(get_string('data', 'local_stats'), 1, 0, '', '', array(0, 0, 0));
                $pdf->WriteHTML('<p><strong>' . get_string('data', 'local_stats') . '</strong><br /></p>');
                if (count($xreport->data->subids) > 0 && $report->payload->max_number_for_col_captions < count($xreport->data->subids)) {
                    $colcaptions = [];
                    for ($i = 0; $i < count($xreport->data->subids); $i++) {
                        $colcaptions[] = "<b>(" . ($i+1) . ")</b> " . $xreport->data->subids[$i];
                        $xreport->data->subids[$i] = ($i+1);
                    }
                    $pdf->WriteHTML('<p><small>' . implode(', ', $colcaptions) . '</small></p>');
                }
                $header = array_merge([''], $xreport->data->subids);
                $lastserie = array_keys($xreport->data->series)[count($xreport->data->series) - 1];
                $sortedperiods = $xreport->data->series[$lastserie];
                switch($xreport->report->payload->sort_by) {
                    case 'name':
                        if ($xreport->report->payload->sort_type == 'ASC') {
                            ksort($sortedperiods);
                        } else {
                            krsort($sortedperiods);
                        }
                        break;
                    case 'value':
                        if ($xreport->report->payload->sort_type == 'ASC') {
                            asort($sortedperiods);
                        } else {
                            arsort($sortedperiods);
                        }
                        break;
                }
                $periods = array_keys($sortedperiods);

                $data = [];
                foreach ($periods as $periodid) {
                    $row = [$periodid];
                    foreach ($xreport->data->series as $serie) {
                        $row[] = $serie[$periodid];
                    }
                    $data[] = $row;
                }

                $pdf->ColoredTable($header, $data, $reporting->payload->orientation == 'p' ? 190 : 277);
            }
        }

        if ($directprint) {
            $pdf->Output($filename, 'I');
            die();
        }

        $filename = "report.pdf";
        $fileinfo = [
            'contextid' => (\context_system::instance())->id,
            'component' => 'local_stats',
            'filearea' => "reporting",
            'itemid' => $reportingid,
            'filepath' => $subdir,
            'filename' => $filename,
        ];
        $attachment = $fs->create_file_from_string($fileinfo, $pdf->Output($filename, 'S'));

        // 5. Send notifications
        if (CLI_SCRIPT) mtrace("Send notifications to " . count($reporting->payload->sendto_notification) . " users");
        if (count($reporting->payload->sendto_notification) > 0) {
            $nextrun = cronlib::get_next_run_time($reporting->payload->cronexpression);
            $DB->set_field('local_stats_reporting', 'nextrun', $nextrun, ['id' => $reportingid]);
            // Send E-Mail to recipients.
            foreach ($reporting->payload->sendto_notification as $sendto) {
                $user = \core_user::get_user($sendto);
                $message = new \core\message\message();
                $message->component = 'local_stats';
                $message->name = 'reporting';
                $message->userfrom = \core_user::get_noreply_user();
                $message->userto = $user;
                $message->subject = $reporting->name;
                $message->fullmessagehtml = "<h1>{$reporting->name}</h1><p>{$reporting->description}</p>";
                $message->fullmessage = \html_to_text($message->fullmessagehtml);
                $message->fullmessageformat = FORMAT_HTML;
                $message->smallmessage = mb_substr($message->fullmessage, 0, 2000, 'UTF-8') . (mb_strlen($message->fullmessage, 'UTF-8') > 2000 ? '...' : '');
                $message->notification = 1;
                $message->contexturl = $PAGE->url->out(false);
                $message->contexturlname = get_string('reporting', 'local_stats');
                $footer = implode('', [
                    get_string('reporting:email:footerlink', 'local_stats', $message->contexturl),
                    get_string('reporting:email:footer', 'local_stats'),
                ]);
                $content = [
                    '*' => [
                        'header' => get_string('reporting:email:header', 'local_stats', \fullname($user)),
                        'footer' => $footer,
                    ],
                ];
                $message->set_additional_content('email', $content);
                $message->attachment = $attachment;
                $message->attachname = $filename;
                \message_send($message);
            }
        }

        // 6. Send E-Mails
        if (CLI_SCRIPT) mtrace("Send email to " . count($reporting->payload->sendto_email) . " addresses");
        if (count($reporting->payload->sendto_email) > 0) {
            $fromUser = \core_user::get_support_user();
            $subject = $reporting->name;

            $file = $attachment->get_contenthash();
            $path = "{$CFG->dataroot}/filedir/" . substr($file, 0,2) . "/" . substr($file, 2, 2) . "/{$file}";
            foreach ($reporting->payload->sendto_email as $email) {
                $toUser = (object)[
                    'alternatename' => '', 'email' => $email, 'firstname' => '',
                    'firstnamephonetic' => '', 'id' => -99, 'lastname' => '',
                    'lastnamephonetic' => '', 'maildisplay' => true, 'mailformat' => 1,
                    'middlename' => '',
                ];
                $messageHtml = get_string('reporting:email:header', 'local_stats', $email) .
                    "<h1>{$reporting->name}</h1><p>{$reporting->description}</p>";
                $messageText = html_to_text($messageHtml);
                email_to_user($toUser, $fromUser, $subject, $messageText, $messageHtml, $path, $filename, false);
            }
        }

        // 7. Unarm subids
        if (empty($reporting->payload->trigger_modeendless)) {
            if (CLI_SCRIPT) mtrace("Unarm " . count($triggers) . " subids");
            foreach ($triggers as $triggerparams) {
                $reporting->payload->trigger_unarmed[] = $triggerparams->subid == get_string('subid:empty', 'local_stats') ? '{empty}' : $triggerparams->realsubid;
            }
            $form = new \local_stats\reportings_form();
            $form->store_row($reporting);
        }

        return $reporting;
    }

    public static function render_chart_to_file(chart_base $chart, string $output_file, int $width = 600, int $height = 400): bool {
        $params = [
            'chart' => $chart,
            'output_file' => $output_file,
            'width' => $width,
            'height' => $height,
        ];

        $args = __DIR__ . '/../chart-app/dist/index.js';
        $cmd = "node " . $args . " 2>&1";

        // passthru('node -v'); exit;

        // $args = __DIR__ . '/../chart-app/src/index.ts';
        // $cmd = 'ts-node ' . $args . " 2>&1";

        $descriptors = array(
            0 => array('pipe', 'r'),  // stdin
            1 => array('pipe', 'w'),  // stdout
            2 => array('pipe', 'w'),  // stderr
        );
        $process = proc_open($cmd, $descriptors, $pipes);

        if (!is_resource($process)) {
            throw new \moodle_exception('node execution error');
        }

        fwrite($pipes[0], json_encode($params));
        fclose($pipes[0]);

        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        $result_code = proc_close($process);
        $output = $stderr . $stdout;

        if ($result_code !== 0) {
            throw new \moodle_exception('node error: ret: ' . $result_code . ', output: ' . $output);
        }

        if (!file_exists($output_file)) {
            throw new \moodle_exception('node error: output file not found, ret: ' . $result_code . ', output: ' . $output);
        }

        return true;
    }

    private static function upgrade(&$reporting): void {
        global $DB;
        if (!empty($reporting->payload)) {
            $reporting->payload = json_decode($reporting->payload);
        } else {
            $reporting->payload = (object)[
                'version' => static::REPORTING_VERSION,
                'cron_minute' => '0',
                'cron_hour' => '12',
                'cron_day' => '*',
                'cron_month' => '*',
                'cron_dayofweek' => '*',
                'description' => '',
                'orientation' => 'p',
                'sendto_email' => [],
                'sendto_notification' => [],
                'trigger_modeendless' => 1,
                'trigger_unarmed' => [],
            ];
        }
        $changed = false;
        if ($reporting->payload->version < 2024041101) {
            $changed = true;
            $reporting->payload->reports = [];
            $reporting->payload->cron_minute = '*';
            $reporting->payload->cron_hour = '12';
            $reporting->payload->cron_day = '*';
            $reporting->payload->cron_month = '*';
            $reporting->payload->cron_dayofweek = '*';
        }
        if ($reporting->payload->version < 2024041800) {
            $reporting->payload->trigger_modeendless = 1;
            $reporting->payload->trigger_unarmed = [];
            $changed = true;
        }
        if ($reporting->payload->version < 2024041900) {
            $reporting->payload->orientation = 'p';
            $changed = true;
        }
        if ($reporting->payload->version < 2024041901) {
            $reporting->payload->description = '';
            $changed = true;
        }
        if ($reporting->payload->version < 2024051301) {
            $reporting->payload->reports_graph = $reporting->payload->reports;
            $reporting->payload->reports_table = $reporting->payload->reports;
            unset($reporting->payload->reports);
            $changed = true;
        }
        if ($changed) {
            $reporting->payload->version = static::REPORTING_VERSION;
            $DB->set_field('local_stats_reporting', 'payload', json_encode($reporting->payload, JSON_PRETTY_PRINT), ['id' => $reporting->id]);
        }
    }
}
