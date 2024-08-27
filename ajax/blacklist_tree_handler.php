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
 * AJAX endpoint to manage HybridMeter blacklist configuration
 *
 * @author Nassim Bennouar
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2020  ISAE-SUPAERO (https://www.isae-supaero.fr/)
 * @package report_hybridmeter
 */

require_once("../../../config.php");

use report_hybridmeter\config;
use report_hybridmeter\data_provider as data_provider;
use report_hybridmeter\logger as logger;

header('Content-Type: text/json');

// Checking authorizations (admin role required).
require_login();

$context = context_system::instance();
$PAGE->set_context($context);
require_capability('report/hybridmeter:all', $context);

$dataprovider = data_provider::get_instance();
$config = config::get_instance();

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $task  = required_param('task', PARAM_ALPHAEXT);
    // Manage blacklist of a category or course.
    if ($task == "manage_blacklist") {
        $type = required_param('type', PARAM_ALPHAEXT);
        $value = required_param('value', PARAM_ALPHAEXT) == "true" ? 1 : 0;
        $id = required_param('id', PARAM_INT);

        if ($type == "courses") {
            $config->set_blacklisted_course($id, $value, true);
        } else if ($type == "categories") {
            $config->set_blacklisted_category_subtree($id, $value);
        } else {
            throw new Exception("Unknown type param: $type");
        }

        // Debugging feature, set debug value to 1 in configurations to display.
        logger::log("New manage_blacklist post request");
        logger::log(["value" => $value, "type" => $type, "id" => $id]);

        $output = [ "blacklisted" => $value ];
    } else {
        $output = [
            "error" => true,
            "message" => "Unknown task",
        ];
    }
} else if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $task  = optional_param('task', 'nothing', PARAM_ALPHAEXT);

    if ($task == "category_children") {
        $id = required_param('id', PARAM_INT);
        $categories = $dataprovider->get_children_categories_ordered($id);

        // In the case where the category id is 0, the child course that corresponds to the site is not returned.
        if ($id != 0) {
            $courses = $dataprovider->get_children_courses_ordered($id);
        } else {
            $courses = [];
        }

        $output = [
            "categories" => $categories,
            "courses" => $courses,
        ];
    } else {
        $output = [
            "error" => true,
            "message" => "Unknown task",
        ];
    }
} else {
    $task = "get";
    $output = [
        "error" => true,
        "message" => "GET method not supported, please retry with a POST request",
    ];
}


// Return response as JSON.
echo json_encode($output);
