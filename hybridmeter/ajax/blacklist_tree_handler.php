<?php
/*
 * Hybrid Meter
 * Copyright (C) 2020 - 2024  ISAE-SUPAERO
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

//TODO : Standardize APIs response
/*
AJAX endpoint to manage HybridMeter blacklist configuration

*/
require_once("../../../config.php");
require_once(__DIR__."/../classes/configurator.php");
require_once(__DIR__."/../classes/data_provider.php");
require_once(__DIR__."/../classes/logger.php");

use \report_hybridmeter\classes\configurator as configurator;
use \report_hybridmeter\classes\data_provider as data_provider;
use \report_hybridmeter\classes\logger as logger;

header('Content-Type: text/json');

//Checking authorizations (admin role required)

require_login();
$context = context_system::instance();
$PAGE->set_context($context);
has_capability('report/hybridmeter:all', $context) || die();

$configurator = configurator::get_instance();
$data_provider = data_provider::get_instance();

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $task  = required_param('task', PARAM_ALPHAEXT);
    // manage blacklist of a category or course
    if ($task == "manage_blacklist") {
        $type = required_param('type', PARAM_ALPHAEXT);
        $value = required_param('value', PARAM_ALPHAEXT) == "true" ? 1 : 0;
        $id = required_param('id', PARAM_INT);
        $configurator->set_blacklisted($type, $id, $value);

        //Debugging feature, set debug value to 1 in configurations to display
        logger::log("New manage_blacklist post request");
        logger::log(array("value" => $value, "type" => $type, "id" => $id));

        $output = [ "blacklisted" => $value ];
    }
    else{
        $output = array(
            "error" => true,
            "message" => "Unknown task",
        );
    }
}
else if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $task  = optional_param('task', 'nothing', PARAM_ALPHAEXT);

    if($task == "category_children") {
        $id = required_param('id', PARAM_INT);
        $categories = $data_provider->get_children_categories_ordered($id);

        //In the case where the category id is 0, the child course that corresponds to the site is not returned

        if($id != 0){
            $courses = $data_provider->get_children_courses_ordered($id);
        }
        else{
            $courses = array();
        }
        
        $output = [ 
            "categories" => $categories,
            "courses" => $courses,
        ];
    }
    else {
        $output = array(
            "error" => true,
            "message" => "Unknown task",
        );
    }
}
else {
    $task = "get";
    $output = array(
        "error" => true,
        "message" => "GET method not supported, please retry with a POST request",
    );
}


//Return response as JSON

echo json_encode($output);
