<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/datalib.php');


/*class hybridation_action_form extends moodleform {
	public function get_actions(): array {
		global $CFG;

		$syscontext = context_system::instance();
		$actions = [];
		$actions['download'] = new action_link(new moodle_url('/report/hybridation/download.php'), "télécharger test");
		$actions['add_selected'] = new action_link(new moodle_url('/report/hybridation/add_selected.php'), "add selected hehe");
		$actions['remove_selected'] = new action_link(new moodle_url('/report/hybridation/remove_selected.php'), "remove selected hoho");

		return $actions;
	}

	public function definition() {
		global $CFG;

		$mform =& $this->_form;

        $actions = [0 => get_string('choose') . '...'];
        $hybactions = $this->get_actions();
        foreach ($hybactions as $key => $action) {
            $actions[$key] = $action->text;
        }
        $objs = array();
        $objs[] =& $mform->createElement('select', 'action', null, $actions);
        $objs[] =& $mform->createElement('submit', 'doaction', get_string('go'));
        $mform->addElement('group', 'actionsgrp', get_string('withselectedusers'), $objs, ' ', false);
	}
}*/

/*
 * Classe pour définir la mise en page
 */
class hybridation_form extends moodleform {

    /*
     * fonction attendue par moodle
     */
    public function definition() {

        //référence vers le formulaire, l'objet qui contient les élements à afficher
        $mform =& $this->_form;

        //en input on rentre un tableau avec d'une part les blacklist, et d'autre part les cours : on récupère la référence ici
        $blacklist =& $this->_customdata['blacklist'];
        $courses =& $this->_customdata['courses'];

        //on prend les noms des cours (pour les afficher)
        $courses_names=array();
        foreach ( $courses as $key => $object )
            $courses_names[$key]=$object['fullname'];

        //on ajoute un header
        $mform->addElement('header', 'users', get_string('blacklistheader', 'report_hybridation'));

        //on ajoute les menus déroulants
        $objs = array();
        $objs[0] =& $mform->createElement('select', 'acourses', get_string('available', 'report_hybridation'), $courses_names, 'size="15"');
        $objs[0]->setMultiple(true);
        $objs[1] =& $mform->createElement('select', 'bcourses', "gacoooooo", $blacklist, 'size="15" style="width:100%');
        $objs[1]->setMultiple(true);

        //avec la légende et un petit help
        $grp =& $mform->addElement('group', 'courses', get_string('courses', 'hybridation_form'), $objs, ' ', false);
        $mform->addHelpButton('courses', 'courses', 'report_hybridation');

        $mform->addElement('static', 'comment');

        //On ajoute les boutons de contrôle
        $objs = array();
        $objs[] =& $mform->createElement('submit', 'addsel', get_string('addsel', 'hybridation_form'));
        $objs[] =& $mform->createElement('submit', 'removesel', get_string('removesel', 'hybridation_form'));
        $grp =& $mform->addElement('group', 'buttonsgrp', get_string('selectedlist', 'hybridation_form'), $objs, null, false);

        $objs = array();
        $objs[] =& $mform->createElement('submit', 'download', get_string('download_csv', 'report_hybridation'));
        $grp =& $mform->addElement('group', 'buttonsgrp2', '', $objs, null, false);

        $renderer =& $mform->defaultRenderer();
        //$template = '<label class="qflabel" style="vertical-align:top">{label}</label> {element}';

        $template='<label class="qflabel" style="vertical-align:top">{label}</label> {element}';

        $renderer->setGroupElementTemplate($template, 'courses');
    }
}

