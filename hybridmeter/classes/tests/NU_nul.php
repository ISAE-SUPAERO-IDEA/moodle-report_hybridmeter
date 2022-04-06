<?php

class NU_nul extends NU {
    function __construct($id) {
        parent::__contruct($id);
    }

    public function tests() {
        echo "<h2>On va vérifier si la valeur est la même en mémoire que dans le CSV</h2>";
        $data_unserialized = unserialize(file_get_contents($path_serialized_data));
        if(data_unserialized === false) {
            echo "<p>Impossible de déserialiser les résultats du dernier calcul, pouvez-vous lancer un nouveau calcul ?</p>";
        }
        if (!isset($data_unserialized['generaldata']['data'][$this->course_id])){
            echo "<p>Nous ne trouvons pas le cours (id = ".$this->course_id.") dans les données sérialisées, êtes-vous sûr que les résultats déficients sont bien les derniers résultats calculés ?</p>";
            return false;
        }
        else if (!isset($data_unserialized['generaldata']['data'][$this->course_id]['niveau_d_utilisation'])) {
            echo "<p>Nous ne trouvons pas le niveau d'utilisation pour ce cours, pouvez-vous relancer un calcul sur ce cours ?</p>";
            return false;
        }

        echo "<p>Le niveau d'utilisation en mémoire est de ".$data_unserialized['generaldata']['data'][$this->course_id]['niveau_d_utilisation']."</p>";

        echo "<h2>On va vérifier si les ";
    }
}