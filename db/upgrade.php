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

defined('MOODLE_INTERNAL') || die;

function xmldb_local_stats_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();
    if ($oldversion < 2024010400) {
        $table = new xmldb_table('local_stats_data');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('reportid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('periodid', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('subid', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null);
        $table->add_field('periodvalue', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_index('idx_reportid', XMLDB_INDEX_NOTUNIQUE, ['reportid']);
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        $table = new xmldb_table('local_stats_report');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '80', null, XMLDB_NOTNULL, null, null);
        $table->add_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('enabled', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('query', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('lasttimecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        upgrade_plugin_savepoint(true, 2024010400, 'local', 'stats');
    }
    if ($oldversion < 2024010500) {
        $table = new xmldb_table('local_stats_report');
        $field = new xmldb_field('payload', XMLDB_TYPE_TEXT, null, null, null, null, null, 'enabled');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $reports = $DB->get_records('local_stats_report');
        foreach ($reports as $report) {
            $payload = (object)[
                'charttype' => 'line',
                'query' => $report->query,
                'version' => 2024010500,
            ];
            $DB->set_field('local_stats_report', 'payload', json_encode($payload, JSON_PRETTY_PRINT), ['id' => $report->id]);
            echo "<p class=\"alert alert-success\">Updated report \"{$report->name}\" to version 2024010500</p>\n";
        }
        $field = new xmldb_field('query');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2024010500, 'local', 'stats');
    }
    if ($oldversion < 2024030701) {
        $table = new xmldb_table('local_stats');
        $field = new xmldb_field('extraparams', XMLDB_TYPE_CHAR, '1333', null, null, null, null, 'contextid');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('extrasession', XMLDB_TYPE_CHAR, '1333', null, null, null, null, 'extraparams');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2024030701, 'local', 'stats');
    }
    if ($oldversion < 2024041100) {
        $table = new xmldb_table('local_stats_reporting');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null);
        $table->add_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('nextrun', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('payload', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        upgrade_plugin_savepoint(true, 2024041100, 'local', 'stats');
    }
    if ($oldversion < 2024041101) {
        $table = new xmldb_table('local_stats_reporting');
        $field = new xmldb_field('enabled', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1', 'id');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2024041101, 'local', 'stats');
    }
    if ($oldversion < 2024041600) {
        $table = new xmldb_table('local_stats_report');
        $field = new xmldb_field('nextrun', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'enabled');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2024041600, 'local', 'stats');
    }
    if ($oldversion < 2024041900) {
        $table = new xmldb_table('local_stats_reporting');
        $field = new xmldb_field('modescheduled', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'description');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('modetriggered', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'modescheduled');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2024041900, 'local', 'stats');
    }
    if ($oldversion < 2024041901) {
        $table = new xmldb_table('local_stats_data');
        $field = new xmldb_field('subid', XMLDB_TYPE_CHAR, '1333', null, XMLDB_NOTNULL, null, null, 'periodid');
        $dbman->change_field_precision($table, $field);
        upgrade_plugin_savepoint(true, 2024041901, 'local', 'stats');
    }
    if ($oldversion < 2024083000) {
        $table = new xmldb_table('local_stats');
        $index = new xmldb_index('idx_lang', XMLDB_INDEX_NOTUNIQUE, ['lang']);
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        $index = new xmldb_index('idx_timecreated', XMLDB_INDEX_NOTUNIQUE, ['timecreated']);
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        upgrade_plugin_savepoint(true, 2024083000, 'local', 'stats');
    }
    if ($oldversion < 2025091900) {
        $table = new xmldb_table('local_stats');
        $field = new xmldb_field('referer', XMLDB_TYPE_CHAR, '1333', null, null, null, null, 'timecreated');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('remoteaddr', XMLDB_TYPE_CHAR, '15', null, null, null, null, 'referer');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('useragent', XMLDB_TYPE_CHAR, '1333', null, null, null, null, 'remoteaddr');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2025091900, 'local', 'stats');
    }
    if ($oldversion < 2026012300) {
        $table = new xmldb_table('local_stats');
        $field = new xmldb_field('postparams', XMLDB_TYPE_CHAR, '1333', null, null, null, null, 'params');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('method', XMLDB_TYPE_CHAR, '10', null, null, null, null, 'lang');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2026012300, 'local', 'stats');
    }

    // \local_stats\lib::npm_install();

    return true;
}
