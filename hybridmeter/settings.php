<?php
/*
 * Hybryd Meter
 * Copyright (C) 2020 - 2024  ISAE-Supaéro
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once(dirname(__FILE__)."/../../config.php");

defined('MOODLE_INTERNAL') || die;

$ADMIN->add('reports', new admin_externalpage('report_hybridmeter', get_string('pluginname', 'report_hybridmeter'), "$CFG->wwwroot/report/hybridmeter/index.php"));

if ($hassiteconfig) {
    $settings->add(new admin_setting_heading(
        'hybridmeter',
        get_string('hybridmeter_settings', 'report_hybridmeter'),
        get_string('hybridmeter_settings_help', 'report_hybridmeter')
    ));
    $url = new moodle_url("/report/hybridmeter/index.php");
}
