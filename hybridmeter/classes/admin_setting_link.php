<?php
// This file is part of the customcert module for Moodle - http://moodle.org/
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
 * Creates an upload form on the settings page.
 *
 * @package    mod_naas
 * @copyright  2021 ISAE-SUPAERO
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_naas;



/**
 * Class extends admin setting class
 *
 * @package    mod_nass
 * @copyright  2021 ISAE-SUPAERO
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class admin_setting_link extends \admin_setting_description {

    private $link;

    private $caption;

    public function __construct($name, $link, $caption) {
        parent::__construct($name, "", "");
        $this->link = $link;
        $this->caption = $caption;
    }

    /**
     * Output the link
     *
     * @param mixed $data
     * @param string $query
     * @return string
     */
    public function output_html($data, $query = '') {
        // Create a dummy variable for this field to avoid being redirected back to the upgrade settings page.
        return '<a href="'.$this->link.'">'.$this->caption.'</a>';
    }
}