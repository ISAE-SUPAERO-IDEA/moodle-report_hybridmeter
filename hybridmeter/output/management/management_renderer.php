<?php

namespace report_hybridmeter\output;


defined('MOODLE_INTERNAL') || die;
    
 
use plugin_renderer_base;
use html_writer;
use moodle_url;
use action_menu;
use action_menu_link;
use pix_icon;

require_once(dirname(__FILE__)."/../../../../config.php");
require_once(dirname(__FILE__).'/../../classes/configurator.php');

// TODO: Remove unnecessary functions
// TODO: Put all renderers on the same level
class management_renderer extends plugin_renderer_base {
    /**
     * Displays a heading for the management pages.
     *
     * @param string $heading The heading to display
     * @param string|null $viewmode The current view mode if there are options.
     * @param int|null $categoryid The currently selected category if there is one.
     * @return string
     */
    public function management_heading($heading, $viewmode = null, $categoryid = null) {
        $html = html_writer::start_div('blacklist-management-header clearfix');
        if (!empty($heading)) {
            $html .= $this->heading($heading);
        }
        $html .= html_writer::end_div();
        return $html;
    }

    /**
     * Prepares the form element for the course category listing bulk actions.
     *
     * @return string
     */
    public function management_form_start() {
        $form = array('action' => $this->page->url->out(), 'method' => 'POST', 'id' => 'blacklist-management',);

        $html = html_writer::start_tag('form', $form);
        $html .= html_writer::start_div("", array("id" => "category-listing",));
        $html .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey(),));
        $html .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'action', 'value' => 'bulkaction',));

        return $html;
    }
    public function include_vue() {
        global $CFG;
        global $OUTPUT;
    // TODO: There are surely better ways to integrate our html and vuejs (P2)

        $params = array(
            "vue" => "libraries/vue@2.6.0.js",
            "axios" => "libraries/axios.min.js",
            "vuesuggest" => "libraries/vue-simple-suggest.js",
            "fontawesome" => "libraries/font-awesome-4.7.0/css/font-awesome.min.css",
            "labelblacklist" => get_string('labelblacklist', 'report_hybridmeter'),
            "labelperiod" => get_string('labelperiod', 'report_hybridmeter'),
            "boxok" => $OUTPUT->box(get_string('boxokstring', 'report_hybridmeter'), 'notice'),
            "scheduled" => \report_hybridmeter\classes\configurator::get_instance()->has_scheduled_calculation(),
            "boxnotok" => $OUTPUT->box(get_string('boxnotokstring', 'report_hybridmeter'), 'errorbox'),
            "www_root" => $CFG->wwwroot,
            "coeff_value_title" => get_string('coeff_value_title', 'report_hybridmeter'),
            "coeff_digitalisation_title" => get_string('coeff_digitalisation_title', 'report_hybridmeter'),
            "coeff_using_title" => get_string('coeff_using_title', 'report_hybridmeter'),
            "treshold_value_title" => get_string('treshold_value_title', 'report_hybridmeter'),
        );

        $html = $OUTPUT->render_from_template("report_hybridmeter/management", $params);

        // Back to plugin button

        $url = new moodle_url('/report/hybridmeter/index.php');
        $html .= html_writer::tag("hr","");
        $html .= html_writer::link(
            $url,
            get_string('back_to_plugin', 'report_hybridmeter'),
            array(
                'class' => 'row btn btn-primary',
                'style' => 'margin-left: 5px; margin-top: 20px;',
            )
        );
        return $html;
    }

    /**
     * Closes the course category bulk management form.
     *
     * @return string
     */
    public function management_form_end() {
        $html = html_writer::end_div();
        $html .= html_writer::end_tag('form');
        return $html;
    }

}
