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
 * @package
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
        $this->cache = [];
    }

    public static function get_instance() {
        if(self::$instance == null){
            self::$instance = new cache_manager();
        }

        return self::$instance;
    }

    public function append_stack_key(string $key, $data) {
        if(isset($this->cache[$key]) && !is_array($this->cache[$key])) {
            throw new Exception("The key is already associated with a variable that is not an array");
        }

        if(!isset($this->cache[$key])){
            $this->cache[$key] = [];
        }

        array_push($this->cache[$key], $data);
    }

    public function update_associative_array_key(string $key, string $subkey, $data) {
        if(isset($this->cache[$key]) && !is_array($this->cache[$key])) {
            throw new Exception("The key is already associated with a variable that is not an array");
        }

        if(!isset($this->cache[$key])){
            $this->cache[$key] = [];
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

    public function is_category_path_calculated(int $categoryid) {
        return isset($this->cache["categories"][$categoryid]);
    }

    public function update_category_path(int $categoryid, $value) {
        $this->cache["categories"][$categoryid] = $value;
    }

    public function get_category_path(int $categoryid) {
        return $this->cache["categories"][$categoryid];
    }
}
