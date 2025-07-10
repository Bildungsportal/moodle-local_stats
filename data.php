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

require_once('../../config.php');
require_login();
require_admin();

$id = required_param('id', PARAM_INT);

$context = \context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_url('/local/stats/data.php', ['id' => $id]);
$BACKURL = new \moodle_url('/local/stats/reports.php', []);

$report = \local_stats\reportlib::get($id);

$PAGE->set_title($report->name);
$PAGE->set_heading($report->name);
\local_stats\lib::add_nav(0, $id);

$table = new \local_stats\reports_data_table($id, true, true);
echo $OUTPUT->header();
echo "<details open><summary>Chart</summary>";

$report->payload->amount_show = optional_param('amount_show', $report->payload->amount_show, PARAM_INT);
$report->payload->charttype = optional_param('forcetype', $report->payload->charttype, PARAM_TEXT);
$report->payload->sumgraph = optional_param('sumgraph', $report->payload->sumgraph, PARAM_INT);
$report->payload->switch_axes_graph = optional_param('switchaxes', $report->payload->switch_axes_graph, PARAM_INT);

$params = (object)[
    'amount_show' => $report->payload->amount_show,
    'charttype' => $report->payload->charttype,
    'id' => $id,
    'sumgraph' => $report->payload->sumgraph,
    'switchaxes' => $report->payload->switch_axes_graph,
    'types' => [],
];
foreach (\local_stats\reports_form::CHART_TYPES as $_charttype => $label) {
    $params->types[] = (object)[
        'label' => $label,
        'selected' => $_charttype == $report->payload->charttype ? ' selected="selected"' : '',
        'type' => $_charttype,
    ];
}

echo $OUTPUT->render_from_template('local_stats/report_charttype', $params);
$chart = \local_stats\reportlib::get_chart_filled($report);

$description = nl2br($report->description);
echo "<p class=\"text-center\">$description</p>\n";
echo $OUTPUT->render_chart($chart, true);
echo "</details>";
echo "<details><summary>Table</summary>";
$table->out();
echo "</details>";
echo $OUTPUT->footer();

