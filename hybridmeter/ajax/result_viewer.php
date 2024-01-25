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

//TODO : Standardize APIs response
/*
AJAX endpoint to manage HybridMeter configuration data

*/ 
require_once("../../../config.php");
header('Content-Type: text/json');

//Checking authorizations (admin role required)

require_login();
$context = context_system::instance();
$PAGE->set_context($context);
has_capability('report/hybridmeter:all', $context) || die();
$id  = optional_param('id' , null, PARAM_INT);

$path_serialized_data = $CFG->dataroot."/hybridmeter/records/serialized_data";
$data_unserialized = unserialize(file_get_contents($path_serialized_data));


echo (json_encode($data_unserialized["data"][$id], JSON_PRETTY_PRINT));
