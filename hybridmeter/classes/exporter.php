<?php

namespace report_hybridmeter\classes;
defined('MOODLE_INTERNAL') || die();
require_once(dirname(__FILE__)."/../../../config.php");
global $CFG;
require_once($CFG->libdir . '/csvlib.class.php');

/**
 * Cette classe permet le transcodage sous forme de CSV des données calculées
 * et donne la possibilité de l'afficher sur une page web, de le transférer sur un
 * fichier local, ou de le mettre à disposition en tant que téléchargement sur navigateur web
 * 
 * 
 * @package    report_hybridmeter
 * @since      Moodle 3.7
 * @copyright  2021 Bruno Ilponse Nassim Bennouar
 */
class exporter {
    //Le caractère de délimitation
    protected $delimiter;

    //Les chaînes de caractères de ce tableau correspondent aux attributs de $data dont les valeurs seront exportés
    protected $fields;

    //Tableau d'alias sur le CSV
    protected $alias;

    //Le tableau de données en entrée, tableau à deux dimensions
    protected $data;

    //L'objet csv_export_writer de moodle core
    protected $csv;

    public function __construct(array $fields=array(), array $alias = array(), array $data=array(), $delimiter = 'comma'){
        $this->set_data($fields);
        if(empty($fields) && !empty($this->data))
            $this->auto_fields();
        else
            $this->set_fields($fields);
        $this->set_alias($alias);
        $this->set_delimiter($delimiter);
    }

    //Récupère les fields de la première entrée et les définit en fields du fichier sortant
    public function auto_fields(){
        $this->fields=array();

        if(!is_array($this->data) || sizeof($this->data)==0)
            throw new Exception("On ne peut pas calculer automatiquement les fields s'il n'y a pas de données");

        foreach ($this->data[array_keys($this->data)[0]] as $key => $value){
            array_push($this->fields, $key);
        }
    }

    public function set_fields (array $fields){
        $precondition_array = array_map($fields, 'is_string');
        if(in_array(false, $precondition_array)){
            throw new Exception("fields doit etre un tableau de chaînes de caractères");
        }

        $this->fields=$fields;
    }

    //ajoute une entrée au tableau
    public function add_data(array $data){
        array_push($this->data, $data);
    }

    public function set_data(array $data){
        $precondition_array = array_map($data, 'is_array');
        if(in_array(false, $precondition_array)){
            throw new Exception("Les données doivent être passées à l'exporter sous forme de tableau de tableaux");
        }

        $this->data = $data;
    }

    public function set_delimiter($delimiter){
        $this->delimiter=$delimiter;
        $this->csv=new \csv_export_writer($this->delimiter);
    }

    public function set_alias(array $alias){
        $this->alias=$alias;
    }

    private function construct_fields_name(){
        $output=array();

        foreach ($this->fields as $field){
            if(array_key_exists($field,$this->alias))
                $value = $this->alias[$field];
            else
                $value = $field;

            array_push($output, $value);
        }

        return $output;
    }

    /*TODO : améliorer le workflow */

    //pour créer le csv
    public function create_csv($filename) {
        $this->csv->set_filename($filename);
        $this->csv->add_data($this->construct_fields_name());

        foreach ($this->data as $key => $record) {
            $row = array();
            foreach ($this->fields as $key => $field) {
                array_push($row, $record[$field]);
            }
            $this->csv->add_data($row);
        }
    }
    
    public function print_csv_data_standard(){
        return $this->csv->print_csv_data(false);
    }

    public function csv_data_to_string(){
        return $this->csv->print_csv_data(true);
    }

    //pour télécharger le csv
    public function download_file(){
        $this->csv->download_file();
    }
}