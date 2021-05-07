<?php

namespace report_hybridation\output;                                                                                                         
 
defined('MOODLE_INTERNAL') || die;                                                                                                  
 
use plugin_renderer_base;  
 
class renderer extends plugin_renderer_base {
    public function render_index_page($page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('report_hybridation/index_page', $data);                                                      
    }           
}