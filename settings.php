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

if ($hassiteconfig) {
    $ADMIN->add('localplugins', new admin_category('local_stats_settings', get_string('pluginname', 'local_stats')));
    $settings = new admin_settingpage('managelocalstats', get_string('pluginname:settings', 'local_stats'));
    $ADMIN->add('local_stats_settings', $settings);
    $ADMIN->add(
        'local_stats_settings',
        new \admin_externalpage(
            'local_stats/reports',
            get_string('reports', 'local_stats'),
            new moodle_url('/local/stats/reports.php'),
            'moodle/site:config'
        )
    );
    $ADMIN->add(
        'local_stats_settings',
        new \admin_externalpage(
            'local_stats/reportings',
            get_string('reportings', 'local_stats'),
            new moodle_url('/local/stats/reportings.php'),
            'moodle/site:config'
        )
    );
    $ADMIN->add(
        'local_stats_settings',
        new \admin_externalpage(
            'local_stats/raw',
            get_string('raw_data', 'local_stats'),
            new moodle_url('/local/stats/raw.php'),
            'moodle/site:config'
        )
    );

    if ($ADMIN->fulltree) {
        $settings->add(
            new \admin_setting_configduration(
                "local_stats/keep_data",
                get_string("keep_data", 'local_stats'),
                get_string("keep_data:description", 'local_stats'),
                60 * 60 * 24 * 365, // 365 days
                60 * 60 * 24
            )
        );

        switch ($CFG->dbtype) {
            case 'pgsql':
                $templatefile = 'report_template_pgsql.sql';
                break;
            default:
                $templatefile = 'report_template_mysql.sql';
        }
        $template = file_get_contents("$CFG->dirroot/local/stats/db/{$templatefile}");
        $settings->add(
            new \admin_setting_configtextarea(
                "local_stats/template_sql_query",
                get_string("report:query:template", 'local_stats'),
                '',
                $template
            )
        );

        $defaultcolorcodes = file_get_contents("$CFG->dirroot/local/stats/db/report_default_colors.txt");
        $settings->add(
            new \admin_setting_configtextarea(
                'local_stats/template_color_codes',
                get_string('report:colorcodes', 'local_stats'),
                '',
                $defaultcolorcodes
            )
        );
    }
}
