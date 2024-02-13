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

namespace report_hybridmeter\output;

use renderable;
use renderer_base;
use templatable;
use stdClass;

 // TODO: see usefulness of this class
class index_page implements renderable, templatable {

    var $buttontext = null;
    var $link = null;
 
    public function __construct($buttontext, $link) {
        $this->buttontext = $buttontext;
        $this->link = $link;
    }
                                                                                                             
    public function export_for_template(renderer_base $output) {
        $data = new stdClass();
        $data->buttontext = $this->buttontext;
        $data->link=$this->link;
        return $data;
    }
}
