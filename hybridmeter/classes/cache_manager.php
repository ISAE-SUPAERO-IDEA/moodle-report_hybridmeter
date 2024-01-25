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

namespace report_hybridmeter\classes;

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__)."/../../../config.php");
require_once(__DIR__."/../constants.php");
require_once(__DIR__."/utils.php");

defined('MOODLE_INTERNAL') || die();

use Exception;

class cache_manager {
    protected $cache;

    protected static $instance = null;

    public function __construct() {
        $this->cache = array();
    }

    public static function get_instance() {
        if(self::$instance == null){
            self::$instance = new cache_manager();
        }

        return self::$instance;
    }

    public function append_stack_key(string $key, $data) {
        if(isset($this->cache[$key]) && !is_array($this->cache[$key]))
            throw new Exception("The key is already associated with a variable that is not an array");
        
        if(!isset($this->cache[$key])){
            $this->cache[$key] = array();
        }

        array_push($this->cache[$key], $data);
    }

    public function update_associative_array_key(string $key, string $subkey, $data) {
        if(isset($this->cache[$key]) && !is_array($this->cache[$key]))
            throw new Exception("The key is already associated with a variable that is not an array");
        
        if(!isset($this->cache[$key])){
            $this->cache[$key] = array();
        }

        $this->cache[$key][$subkey] = $data;
    }

    public function update_key(string $key, $data) {
        $this->cache[$key] = $data;
    }

    public function unset_key(string $key) {
        unset($this->cache[$key]);
    }

    public function get_key(string $key) {
        return $this->cache[$key];
    }

    public function is_category_path_calculated(int $category_id) {
        return isset($this->cache["categories"][$category_id]);
    }

    public function update_category_path(int $category_id, $value) {
        $this->cache["categories"][$category_id] = $value;
    }

    public function get_category_path(int $category_id) {
        return $this->cache["categories"][$category_id];
    }
}
