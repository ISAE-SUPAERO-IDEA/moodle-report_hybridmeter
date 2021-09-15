<?php

namespace report_hybridmeter\output;


defined('MOODLE_INTERNAL') || die;
    
 
use plugin_renderer_base;
use html_writer;
use moodle_url;
use action_menu;
use action_menu_link;
use pix_icon;

// TODO: Retirer les fonctions inutiles
// TODO: Mettre tous les renderer au même niveau
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
        $form = array('action' => $this->page->url->out(), 'method' => 'POST', 'id' => 'blacklist-management');

        $html = html_writer::start_tag('form', $form);
        $html .= html_writer::start_div("", array("id" => "category-listing"));
        $html .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()));
        $html .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'action', 'value' => 'bulkaction'));

        return $html;
    }
    public function include_vue() {
        global $CFG;
        global $OUTPUT;
    // TODO: Il y a sûrement beaucoup mieux pour intégrer notre html et notre vuejs (P2)

        $params = array(
            "vue" => "libraries/vue@2.6.0.js",
            "axios" => "libraries/axios.min.js",
            "vuesuggest" => "libraries/vue-simple-suggest.js",
            "fontawesome" => "libraries/font-awesome-4.7.0/css/font-awesome.min.css",
            "labelblacklist" => "Sélection des cours/catégories",
            "labelperiod" => "Sélection des cours/catégories",
            "www_root" => $CFG->wwwroot
        );

        $html = $OUTPUT->render_from_template("report_hybridmeter/management", $params);
        //$html = file_get_contents($CFG->wwwroot . '/report/hybridmeter/assets/management.html');;
        //$html = str_replace('$$www_root$$', $CFG->wwwroot, $html); 
        $url = new moodle_url('/report/hybridmeter/index.php');
        $html .= html_writer::link($url, get_string('pluginname', 'report_hybridmeter'), array('class' => 'row m-1 mb-1'));
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