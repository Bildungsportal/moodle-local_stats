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

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . "/formslib.php");

class reportings_form extends \local_table_sql\table_sql_subform {
    private object $reporting;
    public static array $files_options = [
        'subdirs' => 1,
        'accepted_types' => ['document', 'image'],
    ];

    function definition() {
        // is done in definition_after_data()
        // setDefault must not be used in definition_after_data. Therefore we set the default values here.
        $fields = ['minute' => '*', 'hour' => '12', 'day' => '*', 'month' => '*', 'dayofweek' => '*'];
        foreach ($fields as $field => $default) {
            $this->_form->setDefault("__payload_cron_$field", $default);
        }
    }

    function definition_after_data() {
        global $CFG, $DB;
        $mform = $this->_form;

        $mform->addElement('header', 'general', get_string('general'));
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('advcheckbox', 'enabled', get_string('active'), '&nbsp;', [], [0, 1]);
        $mform->setType('enabled', PARAM_INT);

        $mform->addElement('text', 'name', get_string('name'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', get_string('required'), 'required');
        $mform->addRule('name', get_string('maximumchars', '', 50), 'maxlength', 50);

        $mform->addElement('textarea', '__payload_description', get_string('description'));
        $mform->setType('__payload_description', PARAM_TEXT);

        $reports = array_map(function($v) {
            return $v->name;
        }, $DB->get_records('local_stats_report', [], 'name ASC', 'id,name'));
        $select = $mform->addElement('select', '__payload_reports_graph', get_string('reports:graph', 'local_stats'), $reports);
        $select->setMultiple(true);

        $select = $mform->addElement('select', '__payload_reports_table', get_string('reports:table', 'local_stats'), $reports);
        $select->setMultiple(true);

        $options = ['p' => get_string('orientation:portrait', 'local_stats'), 'l' => get_string('orientation:landscape', 'local_stats')];
        $mform->addElement('select', "__payload_orientation", get_string('orientation', 'local_stats'), $options);

        $fullname = $DB->sql_fullname();
        [$insql, $inparams] = $DB->get_in_or_equal(array_map('trim', explode(',', $CFG->siteadmins)), onemptyitems: false);
        $sql = "SELECT id,$fullname AS fullname FROM {user} WHERE id $insql ORDER BY firstname ASC,lastname ASC";
        $users = array_map(function($v) {
            return $v->fullname;
        }, $DB->get_records_sql($sql, $inparams));
        $options = array(
            'multiple' => true,
            'noselectionstring' => get_string('users'),
        );
        $mform->addElement('autocomplete', '__payload_sendto_notification', get_string('reporting:sendto_notification', 'local_stats'), $users, $options);
        $mform->addHelpButton('__payload_sendto_notification', 'reporting:sendto_notification', 'local_stats');

        $mform->addElement('textarea', '__payload_sendto_email', get_string('reporting:sendto_email', 'local_stats'));
        $mform->addHelpButton('__payload_sendto_email', 'reporting:sendto_email', 'local_stats');
        $mform->setType('__payload_sendto_email', PARAM_TEXT);

        $mform->addElement('header', 'scheduled_header', get_string('reporting:mode:scheduled', 'local_stats'));
        $mform->addElement('advcheckbox', 'modescheduled', get_string('reporting:mode:scheduled', 'local_stats'), '&nbsp;', [], [0, 1]);
        $mform->addHelpButton('modescheduled', 'reporting:mode:scheduled', 'local_stats');
        $mform->setType('modescheduled', PARAM_INT);

        $mform->addElement('static', 'nextrun_readable', get_string('reporting:nextrun', 'local_stats'));
        $fields = ['minute' => '*', 'hour' => '12', 'day' => '*', 'month' => '*', 'dayofweek' => '*'];
        foreach ($fields as $field => $default) {
            $mform->addElement('text', "__payload_cron_$field", get_string("taskschedule$field", 'tool_task'));
            $mform->addHelpButton("__payload_cron_$field", "taskschedule$field", 'tool_task');
            $mform->setType("__payload_cron_$field", PARAM_TEXT);
        }

        if (!empty($this->reporting->id)) {
            $mform->addElement('header', 'trigger_header', get_string('reporting:mode:triggered', 'local_stats'));
            $mform->addElement('advcheckbox', 'modetriggered', get_string('reporting:mode:triggered', 'local_stats'), '&nbsp;', [], [0, 1]);
            $mform->addHelpButton('modetriggered', 'reporting:mode:triggered', 'local_stats');
            $mform->setType('modetriggered', PARAM_INT);

            $mform->addElement('advcheckbox', "__payload_trigger_modeendless", get_string('trigger:modeendless', 'local_stats'), '&nbsp;', [], [0, 1]);
            $mform->addHelpButton("__payload_trigger_modeendless", 'trigger:modeendless', 'local_stats');
            $mform->setType("__payload_trigger_modeendless", PARAM_INT);

            $html = '<h5 class="d-flex">
                    <div class="col-md-4 text-center">' . get_string('trigger:regex', 'local_stats') . '</div>
                    <div class="col-md-4 text-center">' . get_string('trigger:operator', 'local_stats') . '</div>
                    <div class="col-md-4 text-center">' . get_string('trigger:value', 'local_stats') . '</div>
                </h5>';
            $mform->addElement('html', $html);

            $comparator_options = ['' => '', '<' => '<', '<=' => '<=', '==' => '==', '>=' => '>=', '>' => '>'];
            for ($i = 0; $i < 10; $i++) {
                $mform->addElement('html', '<div class="row">');
                $mform->addElement('html', '<div class="col-md-4">');
                $mform->addElement('text', "__payload_trigger_{$i}_regex", null);
                $mform->addHelpButton("__payload_trigger_{$i}_regex", 'trigger:regex', 'local_stats');
                $mform->addElement('html', '</div><div class="col-md-4">');
                $mform->addElement('select', "__payload_trigger_{$i}_operator", null, $comparator_options, ['style' => 'width: 100%;']);
                $mform->addElement('html', '</div><div class="col-md-4">');
                $mform->addElement('text', "__payload_trigger_{$i}_value", null);
                $mform->addElement('html', '</div></div>');

                $mform->setType("__payload_trigger_{$i}_regex", PARAM_TEXT);
                $mform->setType("__payload_trigger_{$i}_operator", PARAM_TEXT);
                $mform->setType("__payload_trigger_{$i}_value", PARAM_TEXT);
                if ($i > 0) {
                    $before = $i - 1;
                    $mform->hideIf("__payload_trigger_{$i}_regex", "__payload_trigger_{$before}_operator", 'eq', '');
                    $mform->hideIf("__payload_trigger_{$i}_operator", "__payload_trigger_{$before}_operator", 'eq', '');
                    $mform->hideIf("__payload_trigger_{$i}_value", "__payload_trigger_{$before}_operator", 'eq', '');
                }
            }

            $mform->addElement('textarea', "__payload_trigger_unarmed", get_string('trigger:unarmed_subids', 'local_stats'));
            $mform->addHelpButton("__payload_trigger_unarmed", 'trigger:unarmed_subids', 'local_stats');
            $mform->setType(PARAM_TEXT, "__payload_trigger_unarmed");

            $mform->addElement('header', 'files_header', get_string('files'));
            $mform->addElement(
                'filemanager',
                'files',
                get_string('files'),
                null,
                static::$files_options
            );
        }

        $this->add_action_buttons();
    }

    function get_data() {
        $data = parent::get_data();
        if ($data) {
            if ($data->id) {
                // Ensure data of payload that is not part of the form is kept!
                $originaldata = reportinglib::get($data->id);
                $data->payload = $originaldata->payload;
            } else {
                $data->payload = (object)[
                    'version' => reportinglib::REPORTING_VERSION,
                ];
            }
            foreach ($data as $field => $value) {
                if (str_starts_with($field, '__payload_')) {
                    $data->payload->{str_replace('__payload_', '', $field)} = $value;
                }
            }

            if (!is_array($data->payload->sendto_email)) {
                $data->payload->sendto_email = trim($data->payload->sendto_email);
                $data->payload->sendto_email = $data->payload->sendto_email ? array_map('trim', explode("\n", $data->payload->sendto_email)) : [];
            }
            if (isset($data->payload->trigger_unarmed) && !is_array($data->payload->trigger_unarmed)) {
                $data->payload->trigger_unarmed = trim($data->payload->trigger_unarmed);
                $data->payload->trigger_unarmed = $data->payload->trigger_unarmed ? array_map('trim', explode("\n", $data->payload->trigger_unarmed)) : [];
            }
        }
        return $data;
    }

    function validation($data, $files) {
        $errors = parent::validation($data, $files);
        $i = 0;

        while (!empty($data["__payload_trigger_{$i}_operator"])) {
            if (!empty($data["__payload_trigger_{$i}_operator"]) && !in_array($data["__payload_trigger_{$i}_operator"], ['<', '<=', '==', '>=', '>'])) {
                $errors["__payload_trigger_{$i}_operator"] = get_string('trigger:operator:invalid', 'local_stats', $data["__payload_trigger_{$i}_operator"]);
            }
            if (!empty($data["__payload_trigger_{$i}_regex"]) && !preg_match("/^\/.+\/[a-z]*$/i", $data["__payload_trigger_{$i}_regex"])) {
                $errors["__payload_trigger_{$i}_regex"] = get_string('trigger:regex:invalid', 'local_stats');
            }
            $i++;
        }
        if (!empty($data["__payload_sendto_email"])) {
            $testemails = array_map('trim', explode("\n", $data["__payload_sendto_email"]));
            foreach ($testemails as $email) {
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errors["__payload_sendto_email"] = get_string('reporting:sendto_email:invalid', 'local_stats', $email);
                }
            }
        }
        return $errors;
    }

    public function set_data($data): void {
        if (!empty($data->payload)) {
            if (is_array($data->payload->sendto_email ?? null)) {
                $data->payload->sendto_email = implode("\n", $data->payload->sendto_email);
            }
            if (is_array($data->payload->trigger_unarmed)) {
                $data->payload->trigger_unarmed = implode("\n", $data->payload->trigger_unarmed);
            }
            foreach ($data->payload as $field => $value) {
                $data->{"__payload_{$field}"} = $value;
            }
        }
        if (empty($data->enabled)) {
            $data->nextrun_readable = get_string('never');
        } else {
            $data->nextrun_readable = !empty($data->nextrun) ? date('Y-m-d H:i', $data->nextrun) : get_string('now');
        }
        parent::set_data($data);
    }

    public function get_row(int $id): ?object {
        $this->reporting = reportinglib::get($id);

        $draftitemid = \file_get_submitted_draft_itemid('files');
        \file_prepare_draft_area(
            $draftitemid,
            \context_system::instance()->id,
            'local_stats',
            'reporting',
            $this->reporting->id,
            reportings_form::$files_options
        );
        $this->reporting->files = $draftitemid;

        return $this->reporting;
    }

    public function store_row(object $data): ?object {
        global $DB;
        $data->payload->cronexpression = implode(' ', [
            $data->payload->cron_minute, $data->payload->cron_hour, $data->payload->cron_day,
            $data->payload->cron_month, $data->payload->cron_dayofweek,
        ]);
        $data->payload->trigger_unarmed = array_unique($data->payload->trigger_unarmed ?? []);
        array_filter($data->payload->trigger_unarmed);
        sort($data->payload->trigger_unarmed);
        $data->nextrun = cronlib::get_next_run_time($data->payload->cronexpression);
        $data->payload = json_encode($data->payload, JSON_PRETTY_PRINT);
        if (!empty($data->id)) {
            $DB->update_record('local_stats_reporting', $data);
        } else {
            $data->id = $DB->insert_record('local_stats_reporting', $data);
        }
        if (!empty($data->files)) {
            file_save_draft_area_files(
                $data->files,
                \context_system::instance()->id,
                'local_stats',
                'reporting',
                $data->id,
                static::$files_options
            );
        }
        return $data;
    }
}
