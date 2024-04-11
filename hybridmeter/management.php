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
 * @author Nassim Bennouar
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2020  ISAE-SUPAERO (https://www.isae-supaero.fr/)
 */

require('../../config.php');
require_once($CFG->libdir.'/adminlib.php');

require_login();
$context = context_system::instance();
$PAGE->set_context($context);
has_capability('report/hybridmeter:all', $context) || die();

admin_externalpage_setup('report_hybridmeter');


$title = get_string('pluginname', 'report_hybridmeter');
$pagetitle = get_string('config', 'report_hybridmeter');
$url = new moodle_url("$CFG->wwwroot/report/hybridmeter/index.php");
$PAGE->set_url($url);
$PAGE->set_title($title);
$PAGE->set_heading($title);

$PAGE->requires->js_call_amd('report_hybridmeter/management', 'init', [
    'www_root' => $CFG->wwwroot,
    'ajax_url' => "{$CFG->wwwroot}/report/hybridmeter/ajax",
    'plugin_frankenstyle' => "report_hybridmeter",
]);

echo $OUTPUT->header();

echo <<<'EOT'
<div id="hybridmeter-app">
</div>
EOT;

echo html_writer::link(
    $url,
    get_string('back_to_plugin', 'report_hybridmeter'),
    [
        'class' => 'row btn btn-primary',
        'style' => 'margin-left: 5px; margin-top: 20px;',
    ]
);

echo $OUTPUT->footer();
