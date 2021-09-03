<?php

namespace report_hybridmetrics\classes;
defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/csvlib.class.php');

// TODO: Expliquer ce que cette classe exporte et vers quoi
class exporter {
    //Le caractère de délimitation
    protected $delimiter;

    //Le tableau contenant les champs que l'on souhaite voir dans le CSV
    protected $fields;

    //Le tableau de données
    protected $raw_data;

    //L'objet csv_export_writer
    protected $csv;

    public function __construct(array $fields=array(), array $raw_data=array(), $delimiter = 'comma'){
        $this->fields=$fields;
        $this->delimiter=$delimiter;
        $this->csv=new \csv_export_writer($this->delimiter);
    }

    //Récupère les champs de la première entrée et les définit en champs du fichier sortant
    public function auto_fields(){
        //print_r($this->raw_data);
        $this->fields=array();

        if(!is_array($this->raw_data) || sizeof($this->raw_data)==0)
            throw new Exception("On ne peut pas calculer automatiquement les champs s'il n'y a pas de données");

        foreach ($this->raw_data[array_keys($this->raw_data)[0]] as $key => $value){
            array_push($this->fields, $key);
        }
    }

    public function set_fields (array $fields){
        $this->fields=$fields;
    }

    public function add_data(array $data){
        array_push($this->raw_data, $data);
    }

    public function set_data(array $data){
        //print_r($data);
        $this->raw_data=$data;
    }

    public function set_delimiter($delimiter){
        $this->delimiter=$delimiter;
    }

    //pour créer le csv
    public function create_csv($filename) {
        //print_r($this->fields);
        $this->csv->set_filename($filename);
        $fields = $this->fields;
        $this->csv->add_data($fields);

        foreach ($this->raw_data as $key => $record) {
            $row = array();
            foreach ($this->fields as $key => $field) {
                array_push($row, $record[$field]);
            }
            $this->csv->add_data($row);
        }
    }
    
    
    //affiche le CSV sur la page
    public function print_csv_data($return=false){
        return $this->csv->print_csv_data($return);
    }

    //pour télécharger le csv
    public function download_file(){
        $this->csv->download_file();
    }
}