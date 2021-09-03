<?php

namespace report_hybridmetrics\output;


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

    public function enhance_management_interface() {
        $this->page->requires->strings_for_js(
            array(
                'show',
                'showcategory',
                'hide',
                'expand',
                'expandcategory',
                'collapse',
                'collapsecategory',
                'confirmcoursemove',
                'move',
                'cancel',
                'confirm'
            ),
            'moodle'
        );
    }

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
    public function render() {
        global $CFG;
    // TODO: Il y a sûrement beaucoup mieux pour intégrer notre html et notre vuejs (P2)
        $html = file_get_contents($CFG->wwwroot . '/report/hybridmetrics/assets/management.html');;
        $html = str_replace('$$www_root$$', $CFG->wwwroot, $html); 
        return $html;
    }



    public function categories_list_from_root($categories, $root=true){
        $role = $root ? 'tree' : 'group';
        $params = array(
            'class' => "ml-1 list-unstyled",
            'role' => $role,
            'aria-labelledby' => "category-listing-title");
        if(!$root){
            $params['aria-hidden']="true";
        }
        $html = html_writer::start_tag('ul',$params);
        foreach($categories["children_categories"] as $category){
            $html .= $this->category_card($category);
        }
        foreach($categories["children_courses"] as $course){
            $html .= $this->course_card($course);
        }
        $html .= html_writer::end_tag('ul');
        return $html;
    }

    protected function course_card($course){
        $courseicon = $this->output->pix_icon('i/course', get_string('courses'));
        $attributes = array(
            'class' => "listitem  list-group-item list-group-item-action",
            'data-id' => $course["id"],
            'role' => "treeitem");
        $html = html_writer::start_tag('li', $attributes);
        $html .= html_writer::start_span("course-recap");


        $html .= html_writer::start_span('', array("class" => "mr-2"));//('custom-control custom-checkbox mr-1 ');
        $bcourseinput = array(
                'id' => 'courselistitem' . $course["id"],
                'type' => 'checkbox',
                'name' => 'acourses[]',
                'value' => $course['id'],
                'class' => '',//'bulk-action-checkbox custom-control-input',
                'data-action' => 'select',
                'data-type' => 'course'
        );
        $html .= html_writer::empty_tag('input', $bcourseinput);
        /*$html .= html_writer::tag('label', '', array(
            'aria-label' => "test",
            'class' => 'custom-control-label ml-1',
            'for' => 'courselistitem' . $course["id"]
        ));*/
 
        $html .= $courseicon;

        $html .= html_writer::span(
            $course["fullname"], 
            'coursename',
            array('aria-label' => $course["fullname"])
        );

        $html .= html_writer::end_span();



        $html .= html_writer::start_span('right-actions');
        $html .= $this->single_action_item($course);
        $html .= html_writer::end_span();

        $html .= html_writer::end_span();    
        $html .= html_writer::end_tag("li");

        return $html;
    }

    protected function category_card($category){
        $hascourses=($category["nb_children_courses"] > 0);
        $hassubcat=($category["nb_children_categories"] > 0);
        $isexpandable = ($hascourses || $hassubcat);
        $isexpanded = (($hascourses ? ($category["children_courses"] !== null) : false)
            && ($hassubcat ? ($category["children_categories"] !== null) : false));
        $isblacklisted = ($category["blacklisted"] === 1);
        $attributes = array(
            'class' => "listitem listitem-category list-group-item list-group-item-action",
            'data-id' => $category["id"],
            'data-expandable' => $isexpandable ? 1 : 0,
            'data-expanded' => $isexpanded ? 1 : 0,
            'data-visible'=> $isblacklisted ? 0 : 1,
            'role' => "treeitem",
            'aria-expanded'=> $isexpanded);

        $courseicon = $this->output->pix_icon('i/course', get_string('courses'));
        $bcatinput = array(
                'id' => 'categorylistitem' . $category["id"],
                'type' => 'checkbox',
                'name' => 'bcat[]',
                'value' => $category->id,
                'class' => 'mr-2',//'bulk-action-checkbox custom-control-input',
                'data-action' => 'select',
                'data-type' => 'category'
        );
        $managementurl = new moodle_url('/course/management.php');
        $text=$category["name"];

        if ($isexpanded){
            $icon = $this->output->pix_icon('t/switch_minus', get_string('collapse'),
                'moodle', array('class' => 'tree-icon', 'title' => ''));
            $icon = html_writer::link(
                    $managementurl,
                    $icon,
                    array(
                        'class' => 'float-left',
                        'data-action' => 'collapse',
                        'title' => get_string('collapsecategory', 'moodle', $text),
                        'aria-controls' => 'subcategoryof'.$category['id']
                    )
            );
        }
        else if ($isexpandable) {
            $icon = $this->output->pix_icon('t/switch_plus', get_string('expand'),
                    'moodle', array('class' => 'tree-icon', 'title' => ''));
            $icon = html_writer::link(
                    $viewcaturl,
                    $icon,
                    array(
                            'class' => 'float-left',
                            'data-action' => 'expand',
                            'title' => get_string('expandcategory', 'moodle', $text)
                    )
            );
        }
        else {
            $icon = $this->output->pix_icon(
                    'i/navigationitem',//i/empty',
                    '',
                    'moodle',
                    array('class' => 'tree-icon'));
            $icon = html_writer::span($icon, 'float-left');
        }


        $html = html_writer::start_tag('li', $attributes);
        $html .= html_writer::start_div('category-recap');
        $html .= html_writer::start_span('mr-2');//div('custom-control custom-checkbox mr-1 ');
        $html .= html_writer::empty_tag('input', $bcatinput);
        /*$html .= html_writer::tag('label', '', array(
            'aria-label' => "testtesttesttesttesttesttesttesttesttesttestetstetzeyueyereyrereghreherhreghreghreghregh",
            'class' => 'custom-control-label',
            'for' => 'categorylistitem' . $category["id"]
        ));*/
        $html .= $icon;

        $html .= html_writer::span(
            $category["name"], 
            'float-left categoryname',
            array('aria-label' => $category["name"])
        );

        $html .= html_writer::end_span();

        //$html .= html_writer::start_div('');

        $html .= html_writer::start_span('',array(
            "class" => "mr-1 right-actions"
        ));

        $html .= $category["nb_children_courses"];

        $html .= $courseicon;        

        $html .= $this->single_action_item($category);

        $html .= html_writer::end_span();

        $html .= html_writer::end_div();

        $html .= html_writer::end_div();
        $html .= html_writer::end_tag('li');
        return $html;
    }

    protected function single_action_item($item){

        /*$menu = new action_menu();
        $menu->attributes['class'] .= ' category-items-actions item-actions';*/

        $isblacklisted = ($item['blacklisted']===1);

        $accurate_whitelist = $isblacklisted ? 1 : 0;
        $accurate_blacklist= $isblacklisted ? 0 : 1;
        
        $action_target = ($item["type"] === HYBRIDATION_COURS_TYPE_NAME) ?
        "course" : "category";
        //$action_root = ($isblacklisted) ? "whitelist" : "blacklist";
        //$action = $action_root.$action_target;

        $whitelist=get_string('whitelist', 'report_hybridmetrics');
        $icon_whitelist=new pix_icon('t/show',
                $whitelist
            );

        $blacklist=get_string('blacklist', 'report_hybridmetrics');
        $icon=new pix_icon('t/hide',
                $blacklist
            );

        $html = html_writer::start_span("action-menu-3-menubar", array(
            "class" => "menubar d-flex",
            "role" => "menubar"
        ));

        $html .= html_writer::start_span("",array(
            "class" => "whitelist-button"
        ));

        $html .= html_writer::start_tag("a", array(
            "class" => "action-hide menu-action",
            "data-action" => "whitelist".$action_target,
            "role" => "menuitem",
            "title" => $whitelist,
            "data-accurate" => $accurate_whitelist));

        $html .= $this->output->pix_icon('t/show', $whitelist,
                    'moodle', array('class' => 'icon fa', 'title' => 'whitelist'));

        $html .= html_writer::end_tag("a");

        $html .= html_writer::end_span();

        $html .= html_writer::start_span("",array(
            "class" => "blacklist-button"
        ));

        $html .= html_writer::start_tag("a", array(
            "class" => "action-hide menu-action",
            "data-action" => "blacklist".$action_target,
            "role" => "menuitem",
            "title" => $blacklist,
            "data-accurate" => $accurate_blacklist));

        $html .= $this->output->pix_icon('t/hide', $blacklist,
                    'moodle', array('class' => 'icon fa', 'title' => 'blacklist'));

        $html .= html_writer::end_tag("a");

        $html .= html_writer::end_span();

        $html .= html_writer::end_span();

        return $html;

        /*
        $menu->add(new action_menu_link(
            new moodle_url('management.php',
                array("action" => "blacklistcategory",
                        "categoryid" => $item['id'])),
            $icon,
            "bz ta mere",
            $whitelist,
            array('data-action' => "show", 'class' => 'action-show')
        ));
        $blacklist=get_string('blacklist', 'report_hybridmetrics');
        $icon=new pix_icon('t/hide',
                $blacklist
            );
        $menu->add(new action_menu_link(
            new moodle_url('management.php',
                array("action" => "whitelistcategory",
                        "categoryid" => $item->id)),
            $icon,
            "bz ta mere",
            $blacklist,
            array('data-action' => "hide", 'class' => 'action-hide')
        ));
        
        return $this->render($menu);*/
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