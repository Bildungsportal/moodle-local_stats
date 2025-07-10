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

use moodleform;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . "/formslib.php");

class reports_form extends moodleform {
    public const CHART_TYPES = [
        'bar' => 'bar',
        'bar_stacked' => 'bar stacked',
        'bar_horiz' => 'bar horizontal',
        'bar_stacked_horiz' => 'bar stacked horizontal',
        'line' => 'line',
        'line_smooth' => 'line smooth',
        'pie' => 'pie',
        'pie_doughnut' => 'pie (doughnut)',
    ];

    function definition() {
        $mform = $this->_form;

        $mform->addElement('header', 'general', get_string('general'));
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('advcheckbox', 'enabled', get_string('active'), '&nbsp;', [], [0, 1]);
        $mform->setType('enabled', PARAM_INT);

        $mform->addElement('text', 'name', get_string('name'));
        $mform->setType('name', PARAM_TEXT);

        $mform->addElement('select', '__payload_charttype', get_string('type', 'editor'), self::CHART_TYPES);
        $mform->setType('__payload_charttype', PARAM_TEXT);

        $mform->addElement('text', '__payload_amount_show', get_string('report:amount:show', 'local_stats'), '');
        $mform->addHelpButton('__payload_amount_show', 'report:amount:show', 'local_stats');
        $mform->setDefault('__payload_amount_show', 0);
        $mform->setType('__payload_amount_show', PARAM_INT);

        $mform->addElement('text', '__payload_amount_keep', get_string('report:amount:keep', 'local_stats'), '');
        $mform->addHelpButton('__payload_amount_keep', 'report:amount:keep', 'local_stats');
        $mform->setDefault('__payload_amount_keep', 0);
        $mform->setType('__payload_amount_keep', PARAM_INT);

        $mform->addElement('text', '__payload_max_number_for_col_captions', get_string('report:max_number_for_col_captions', 'local_stats'), '');
        $mform->addHelpButton('__payload_max_number_for_col_captions', 'report:max_number_for_col_captions', 'local_stats');
        $mform->setDefault('__payload_max_number_for_col_captions', 10);
        $mform->setType('__payload_max_number_for_col_captions', PARAM_INT);

        $mform->addElement('advcheckbox', '__payload_switch_axes_data', get_string('report:switch_axes_data', 'local_stats'), [], [ 0,1 ]);
        $mform->addHelpButton('__payload_switch_axes_data', 'report:switch_axes_data', 'local_stats');
        $mform->setType('__payload_switch_axes_data', PARAM_INT);

        $mform->addElement('advcheckbox', '__payload_switch_axes_graph', get_string('report:switch_axes_graph', 'local_stats'), [], [ 0,1 ]);
        $mform->addHelpButton('__payload_switch_axes_graph', 'report:switch_axes_graph', 'local_stats');
        $mform->setType('__payload_switch_axes_graph', PARAM_INT);

        $mform->addElement('advcheckbox', '__payload_hide_empty_subids', get_string('report:hide_empty_subids', 'local_stats'), [], [ 0,1 ]);
        $mform->addHelpButton('__payload_hide_empty_subids', 'report:hide_empty_subids', 'local_stats');
        $mform->setType('__payload_hide_empty_subids', PARAM_INT);

        $mform->addElement('select', '__payload_sort_by', get_string('report:sort_by', 'local_stats'), [ 'name' => 'name', 'value' => 'value' ]);
        $mform->addHelpButton('__payload_sort_by', 'report:sort_by', 'local_stats');
        $mform->setType('__payload_sort_by', PARAM_TEXT );

        $mform->addElement('select', '__payload_sort_type', get_string('report:sort_type', 'local_stats'), [ 'ASC' => 'ASC', 'DESC' => 'DESC' ]);
        $mform->addHelpButton('__payload_sort_type', 'report:sort_type', 'local_stats');
        $mform->setType('__payload_sort_type', PARAM_TEXT );

        $mform->addElement(
            'advcheckbox', '__payload_sumgraph',
            get_string('report:sumgraph', 'local_stats'),
            get_string('report:sumgraph:text', 'local_stats'),
            [], [0, 1]
        );
        $mform->setType('__payload_sumgraph', PARAM_INT);

        $mform->addElement('textarea', 'description', get_string('description'));
        $mform->setType('description', PARAM_TEXT);

        $mform->addElement('header', 'cron', get_string('cron', 'admin'));
        $mform->addElement(
            'advcheckbox', '__payload_cron_enabled',
            get_string('active'),
            '', [], [0, 1]
        );
        $mform->setType('__payload_cron_enabled', PARAM_INT);
        $mform->addElement('static', 'nextrun_readable', get_string('reporting:nextrun', 'local_stats'));
        $fields = ['minute' => '*', 'hour' => '12', 'day' => '*', 'month' => '*', 'dayofweek' => '*'];
        foreach ($fields as $field => $default) {
            $mform->addElement('text', "__payload_cron_$field", get_string("taskschedule$field", 'tool_task'));
            $mform->addHelpButton("__payload_cron_$field", "taskschedule$field", 'tool_task');
            $mform->setType("__payload_cron_$field", PARAM_TEXT);
            $mform->setDefault("__payload_cron_$field", $default);
            $mform->disabledIf("__payload_cron_$field", '__payload_cron_enabled', 'eq', 0);
        }

        $mform->addElement('header', 'settings', get_string('settings'));
        $warning = get_string('report:change_warning', 'local_stats');
        $mform->addElement('html', "<p class=\"alert alert-danger\">{$warning}</p>");

        $mform->addElement('text', 'lasttimecreated', get_string('report:lasttimecreated', 'local_stats'));
        $mform->addHelpButton('lasttimecreated', 'report:lasttimecreated', 'local_stats');
        $mform->setType('lasttimecreated', PARAM_INT);

        $mform->addElement(
            'advcheckbox', 'confirmsqlchange',
            get_string('report:confirmsqlchange', 'local_stats'),
            get_string('report:confirmsqlchange:text', 'local_stats'),
            [], [0, 1]
        );
        $mform->hideIf('confirmsqlchange', 'id', 'eq', 0);
        $mform->setType('confirmsqlchange', PARAM_TEXT);

        $mform->addElement(
            'advcheckbox', '__payload_wipedata',
            get_string('report:wipedata', 'local_stats'),
            get_string('report:wipedata:text', 'local_stats'),
            [], [0, 1]
        );
        $mform->setType('__payload_wipedata', PARAM_TEXT);

        $mform->addElement(
            'advcheckbox', 'wipedataonce',
            get_string('report:wipedataonce', 'local_stats'),
            get_string('report:wipedataonce:text', 'local_stats'),
            [], [0, 1]
        );
        $mform->hideIf('wipedataonce', 'id', 'eq', 0);
        $mform->setType('wipedataonce', PARAM_TEXT);

        $mform->addElement('textarea', '__payload_query', get_string('report:query', 'local_stats'), 'wrap="virtual" rows="20" cols="50"');
        $mform->setType('__payload_query', PARAM_RAW);
        $mform->addHelpButton('__payload_query', 'report:query', 'local_stats');

        $this->add_action_buttons();
    }

    function validation($data, $files): array {
        $errors = [];
        if (!empty($data['id'])) {
            $originaldata = reportlib::get($data['id']);
            if ($data['__payload_query'] != $originaldata->payload->query && empty($data['confirmsqlchange'])) {
                $errors['__payload_query'] = get_string('report:confirmsqlchange:required', 'local_stats');
            }
        }
        if (!reportlib::validate($data['__payload_query'])) {
            $errors['__payload_query'] = get_string('report:query:contains_malicious_sql', 'local_stats');
        }
        return $errors;
    }

    function get_data() {
        $data = parent::get_data();
        if ($data) {
            if ($data->id) {
                // Ensure data of payload that is not part of the form is kept!
                $originaldata = reportlib::get($data->id);
                $data->payload = $originaldata->payload;
            } else {
                $data->payload = (object)[
                    'version' => reportlib::REPORT_VERSION,
                ];
            }

            foreach ($data as $field => $value) {
                if (str_starts_with($field, '__payload_')) {
                    $data->payload->{str_replace('__payload_', '', $field)} = $value;
                }
            }
        }
        return $data;
    }

    public function set_data($data): void {
        if (!empty($data->payload)) {
            foreach ($data->payload as $field => $value) {
                $data->{"__payload_{$field}"} = $value;
            }
            if (empty($data->enabled) || empty($data->payload->cron_enabled)) {
                $data->nextrun_readable = get_string('never');
            } else {
                $data->nextrun_readable = !empty($data->nextrun) ? date('Y-m-d H:i', $data->nextrun) : get_string('now');
            }
        }
        parent::set_data($data);
    }
}
