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
$PAGE->set_url('/local/stats/reporting.php', ['id' => $id]);

$reporting = \local_stats\reportinglib::get($id);

$PAGE->set_title($reporting->name);
$PAGE->set_heading($reporting->name);
\local_stats\lib::add_nav($id);

echo $OUTPUT->header();
$backurl = new \moodle_url('/local/stats/reportings.php', []);
$back = get_string('back');
echo "<a href=\"{$backurl}\" class=\"btn btn-primary\"><i class='fa fa-arrow-l'></i> {$back}</a>";
$params = [
    'filerecords' => \local_stats\reportinglib::get_files($id),
];

echo $OUTPUT->render_from_template('local_stats/files', $params);
echo $OUTPUT->footer();
