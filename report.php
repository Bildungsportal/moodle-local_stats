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

use core\output\notification;

require_once('../../config.php');
require_login();
require_admin();

$id = optional_param('id', 0, PARAM_INT);

$context = \context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_url('/local/stats/report.php', ['id' => $id]);
$BACKURL = new \moodle_url('/local/stats/reports.php', []);

$mform = new \local_stats\reports_form();
$originaldata = \local_stats\reportlib::get($id);
$mform->set_data($originaldata);

if ($mform->is_cancelled()) {
    redirect($BACKURL);
} else if ($data = $mform->get_data()) {
    $msgs = [];
    if (!empty($data->id)) {
        if ($data->wipedataonce) {
            // The sql has changed, so reset all prior statistics!
            \local_stats\reportlib::wipe($data->id);
            $data->lasttimecreated = 0;
            $msgs[] = get_string('report:reset_data', 'local_stats', ['name' => $data->name]);
        }
        \local_stats\reportlib::set($data);
    } else {
        \local_stats\reportlib::set($data);
    }
    $msgs[] = get_string('report:saved', 'local_stats', ['name' => $data->name]);
    $msg = implode("<br />\n", $msgs);
    $url = $PAGE->url;
    redirect($url, $msg, 0, notification::NOTIFY_SUCCESS);
}
$heading = $originaldata->name ?? get_string('pluginname', 'local_stats');
$PAGE->set_title($heading);
$PAGE->set_heading($heading);
\local_stats\lib::add_nav(0, $id);
echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();
