<?php
// This file is part of Moodle - http://moodle.org
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
 * @author Bruno Ilponse, Nassim Bennouar
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2020  ISAE-SUPAERO (https://www.isae-supaero.fr/)
 */
require_once(dirname(__FILE__)."/../../config.php");

defined('MOODLE_INTERNAL') || die;

// Checking authorizations (admin role required).
require_login();
$context = context_system::instance();
$PAGE->set_context($context);
has_capability('report/hybridmeter:all', $context) || die();

$ADMIN->add(
    'reports',
    new admin_externalpage('report_hybridmeter',
        get_string('pluginname', 'report_hybridmeter'),
        "$CFG->wwwroot/report/hybridmeter/index.php")
);

if ($hassiteconfig) {
    $settings->add(new admin_setting_heading(
        'hybridmeter',
        get_string('hybridmeter_settings', 'report_hybridmeter'),
        get_string('hybridmeter_settings_help', 'report_hybridmeter')
    ));
    $url = new moodle_url("/report/hybridmeter/index.php");
}
