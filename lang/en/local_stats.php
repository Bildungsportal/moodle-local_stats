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
 * @copyright  2023 Austrian Federal Ministry of Education
 * @author     Robert Schrenk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['cachedef_extraparams_request'] = 'Extra-Params to be added in regard of the request';
$string['cachedef_extraparams_session'] = 'Extra-Params to be added in regard of the session';
$string['data'] = 'Data';
$string['keep_data'] = 'Keep data';
$string['keep_data:description'] = 'Specify how long the log data shall be kept.';
$string['messageprovider:reporting'] = 'Notification of new reportings';
$string['orientation'] = 'Orientation';
$string['orientation:landscape'] = 'Landscape';
$string['orientation:portrait'] = 'Portrait';
$string['periodid'] = 'Period';
$string['pluginname'] = 'Statistics';
$string['pluginname:settings'] = 'Statistic settings';
$string['privacy:export:local_stats'] = 'Export raw data for user statistics';
$string['privacy:metadata:local_stats'] = 'Store user actions on website';
$string['privacy:metadata:local_stats:contextid'] = 'The context id';
$string['privacy:metadata:local_stats:lang'] = 'The language';
$string['privacy:metadata:local_stats:params'] = 'The request parameters';
$string['privacy:metadata:local_stats:path'] = 'The request path';
$string['privacy:metadata:local_stats:timecreated'] = 'The timestam of the action';
$string['privacy:metadata:local_stats:userid'] = 'The user id';
$string['raw_data'] = 'Inspect raw log';
$string['report:amount:keep'] = 'Keep periods';
$string['report:amount:keep_help'] = 'Specifies how many periods are kept, 0 for unlimited amount of periods.';
$string['report:amount:show'] = 'Show periods';
$string['report:amount:show_help'] = 'Specifies how many periods are shown, 0 for unlimited amount of periods.';
$string['report:change_warning'] = 'Attention: changing the SQL query has effect on the report. Especially if you change the sql query and can not assure all data is compatible, you should wipe all data once!';
$string['report:colorcodes'] = 'Color Codes';
$string['report:confirmsqlchange'] = 'Confirm SQL change';
$string['report:confirmsqlchange:required'] = 'You must confirm that you want to change the sql query!';
$string['report:confirmsqlchange:text'] = 'Yes, I hereby confirm that I want to change the sql query!';
$string['report:hide_empty_subids'] = 'Hide empty subids';
$string['report:hide_empty_subids_help'] = 'If a subid does not have any value in any period, it is not shown in graphs or tables.';
$string['report:max_number_for_col_captions'] = 'Max Col Captions';
$string['report:max_number_for_col_captions_help'] = 'Specifies for how many columns the full heading shall be printed in data tables of reportings. In case the actual number of columns exceeds this limit, all columns will be printed numbered above the datatable and columns will be numbered accordingly!';
$string['report:sort_by'] = 'Sort by';
$string['report:sort_by_help'] = 'Sort data within a period by subid name or value (default: name)';
$string['report:sort_type'] = 'Sort type';
$string['report:sort_type_help'] = 'Sort data within a period ascending (ASC) or descending (DESC) (default: ASC)';
$string['report:sumgraph'] = 'Sum graph';
$string['report:sumgraph:text'] = 'Show a graph that sums up all columns as a line.';
$string['report:switch_axes_data'] = 'Switch Axes (Data)';
$string['report:switch_axes_data_help'] = 'Switch rows and colums when generating data tables';
$string['report:switch_axes_graph'] = 'Switch Axes (Graph)';
$string['report:switch_axes_graph_help'] = 'Switch rows and colums when generating the graph';
$string['report:wipedata'] = 'Wipe data always';
$string['report:wipedata:text'] = 'Yes, wipe data always before each run of report';
$string['report:wipedataonce'] = 'Wipe data once';
$string['report:wipedataonce:text'] = 'Yes, please wipe all data once!';
$string['report:lasttimecreated'] = 'Last timestamp';
$string['report:lasttimecreated_help'] = 'The timestamp of the last item that has been analyzed in this report. Normally, you do not need to change this value!';
$string['report:query'] = 'Query';
$string['report:query_help'] = 'Define the sql query here. It must provide the following columns: period, subid,value und lasttimecreated.';
$string['report:query:contains_malicious_sql'] = 'The query contains malicious sql commands! Commands such as UPDATE, INSERT and such must not be used!';
$string['report:query:template'] = 'SQL Template for reports';
$string['report:reset_data'] = 'All prior data to the report "{$a->name}" has been erased and will be recalculated.';
$string['report:run:successfully'] = 'The report "{$a->name}" has been run successfully!';
$string['report:saved'] = 'The report "{$a->name}" was stored successfully.';
$string['reportings'] = 'Reportings';
$string['reporting'] = 'Reporting';
$string['reporting:email:footer'] = '';
$string['reporting:email:footerlink'] = '<br/>Report can be opened <a href="{$a}">here</a>.';
$string['reporting:email:header'] = 'Dear {$a},<br /><br />';
$string['reporting:mode:scheduled'] = 'Scheduled Mode';
$string['reporting:mode:scheduled_help'] = 'In the scheduled mode, the reporting will be sent according to the cron expression.';
$string['reporting:mode:triggered'] = 'Triggered Mode';
$string['reporting:mode:triggered_help'] = 'In the triggered mode, the reporting will be sent once one of the conditions is met.';
$string['reporting:nextrun'] = 'Next run';
$string['reporting:run:successfully'] = 'The reporting "{$a->name}" has been run successfully!';
$string['reporting:sendto_email'] = 'E-Mail recipients';
$string['reporting:sendto_email_help'] = 'Enter E-Mail-addresses line by line that should receive this reporting.';
$string['reporting:sendto_email:invalid'] = 'Invalid E-Mail address detected ({$a}).';
$string['reporting:sendto_notification'] = 'Notifications';
$string['reporting:sendto_notification_help'] = 'Please indicate users who shall receive a notification!';
$string['reports'] = 'Reports';
$string['reports:graph'] = 'Graphs';
$string['reports:table'] = 'Tables';
$string['subid:empty'] = 'unspecified';
$string['task:cleanup'] = 'Clean up stats log data';
$string['task:run_reports'] = 'Run statistical reports';
$string['task:run_reportings'] = 'Run reportings';
$string['trigger'] = 'Trigger';
$string['trigger:fired'] = '{$a->reportname}, trigger for "<strong>{$a->subid}</strong>" fired in !<br /><small>=> Condition {$a->curvalue} {$a->operator} {$a->firevalue} met, regex was {$a->regex}.</small>';
$string['trigger:modeendless'] = 'Endless mode';
$string['trigger:modeendless_help'] = 'In endless mode trigger will always send a notification, otherwise only one notification per subid, and subid must be rearmed.';
$string['trigger:operator'] = 'Operator';
$string['trigger:operator:invalid'] = 'Invalid operator detected: {$a}';
$string['trigger:regex'] = 'Regex';
$string['trigger:regex_help'] = 'Leave this field empty to apply this rule on all subids, or enter a valid regex to filter for certain subids. Please use the keyword {empty} to match empty subids!';
$string['trigger:regex:invalid'] = 'Invalid regex expression detected';
$string['trigger:unarmed_subids'] = 'Unarmed Sub-IDs';
$string['trigger:unarmed_subids_help'] = 'To re-arm a subid, please remove it from the textfield.';
$string['trigger:value'] = 'Value';
