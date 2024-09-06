<?php

require(dirname(__FILE__) . '/../../config.php');

use report_hybridmeter\course_indicators;
use report_hybridmeter\indicators;
use report_hybridmeter\data_provider as data_provider;

require_login();
$context = context_system::instance();
$PAGE->set_context($context);
require_capability('report/hybridmeter:all', $context);

$courseid = required_param('id', PARAM_INT);

global $DB;

$courseinfo = $DB->get_record('course', array("id" => $courseid));
$data_provider = data_provider::get_instance();

$config = \report_hybridmeter\config::get_instance();
$begin = $config->get_begin_date();
$end = $config->get_end_date();


$output = $PAGE->get_renderer('report_hybridmeter');
$url = new moodle_url("$CFG->wwwroot/report/hybridmeter/course?id=".$courseid.".php");
$PAGE->set_url($url);
echo $output->header();
echo $output->course_indicators(
    new course_indicators(
        $courseinfo->fullname,
        $begin,
        $end,
        indicators::digitalisation_level($courseid),
        indicators::usage_level($courseid),
        indicators::nb_registered_students($courseid),
        indicators::active_students($courseid),
        indicators::is_course_active_on_period($courseid),
        $data_provider->count_activities_per_type_of_course($courseid),
        $data_provider->count_hits_on_activities_per_type(
            $courseid,
            $begin,
            $end
        )
    )
);



echo "<p><a href=\"management.php\">Back to config</a></p>";
echo $output->footer();
