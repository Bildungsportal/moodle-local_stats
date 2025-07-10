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

$id = required_param('id', PARAM_INT);
$direct = optional_param('direct', 0, PARAM_INT);
$url = optional_param('backto', new \moodle_url('/local/stats/reportings.php', []), PARAM_TEXT);

$context = \context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_url('/local/stats/run_reporting.php', ['id' => $id]);

$reporting = \local_stats\reportinglib::run($id, true, $direct);

redirect($url, get_string('reporting:run:successfully', 'local_stats', $reporting), 0, notification::NOTIFY_SUCCESS);
