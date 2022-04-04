# Documentation technique


[Retour README](../README.md)
===


Voici une description fichier par fichier du programme :

index.php
---------

L'index est le point d'entrée du plugin

management.php
--------------

L'interface de configuration est accessible depuis management.php

constants.php
-------------

Toutes les constantes utilisées sont définies dans le fichier constants.php

classes/configurator.php
------------------------

La classe configurator a pour fonction de gérer les paramètres du plugin.

  

On accède à l'instance de configurator via la méthode statique `configurator::getInstance()`

  

Les données de paramètre sont organisées sous forme de tableau clé-valeur et enregistrées au format JSON dans le dossier moodle data de la plateforme, au chemin suivant : `"{$CFG->dataroot}/hybridmeter/config.json"`

  

Dans le constructeur on initialise les paramètres avec leur valeur par défaut grâce à la fonction `set_default_value(string $key, mixed $value)` qui prend en paramètre une chaîne de caractères `$key` la clé, et sa valeur associée `$value` qui peut être de n'importe quel type supporté par [`json_encode`](https://www.php.net/manual/fr/function.json-encode.php).

  

Pour mettre à jour un paramètre, on utilise la fonction `update_key (string $key, mixed $value)` qui prend en paramètre une chaîne de caractères `$key` pour la clé, et sa valeur associée `$value`.

  

On peut mettre plusieurs paramètres à jour à la fois avec la fonction `update (array $data)` : qui prend en paramètre un tableau associatif comme dans l'exemple suivant.

```plain
array(
  "paramètre_déjà_défini_à_modifier" => 7.2,
  "nouveau_paramètre" => "ENSEEIHT"
);
```

  

C'est un singleton, dont la méthode statique `configurator::getInstance()` retourne la référence vers l'instance.

{$CFG->dataroot}/hybridmeter/config.json
----------------------------------------

C'est le fichier de paramètres, il contient les informations de blacklist, la période de mesure, la date de lancement programmée, les seuils...

  

Il est créé dans un sous-dossier hybridmeter du dossier moodledata de la plateforme (dont le chemin est contenu dans la variable $CFG->dataroot et en clair dans le fichier config.php de la plateforme).

classes/data\_provider.php
--------------------------

La classe `data_provider` est le point unique d'accès à la base de données dans le plugin.

  

On y accède via la fonction statique `data_provider::getInstance()`.

  

Cette fonction contient les méthodes suivantes :

*   `count_activities_per_type_of_course(int $id_course)` : cette fonction retourne le nombre d'activités par catégorie du cours d'id `$id_course`.

Si le cours ne possède que deux quiz et un forum par exemple, la fonction retournera le tableau suivant :

```plain
array(
```

*   `count_student_visits_on_course(int $id_course, int $begin_timestamp, int $end_timestamp)` : cette fonction retourne le nombre de visites d'apprenants sur le cours d'id `$id_course` durant la période allant du timestamp `$begin_timestamp` au timestamp `$end_timestamp`.
*   `count_student_single_visitors_on_courses($ids_courses, int $begin_timestamp, int $end_timestamp)` : retourne le nombre d'apprenants uniques ayant visité un cours dont l'id est un élément du tableau `$ids_courses` durant la période allant du timestamp `$begin_timestamp` au timestamp `$end_timestamp`.
*   `count_registered_students_of_course(int $id_course)` : retourne le nombre d'apprenants inscrits dans le cours d'id `$id_course`
*   `count_distinct_registered_students_of_courses($ids_courses)` : retourne le nombre d'apprenants uniques inscrits dans au moins un cours dont l'id est un élément du tableau `$ids_courses`.
*   `count_hits_on_activities_per_type(int $id_course, int $begin_timestamp, int $end_timestamp)` : retourne un tableau associant le nom de chaque type d'activité au nombre de clics qui a été enregistré sur les activités du type appartenant au cours d'id `$id_course` durant la période allant du timestamp `$begin_timestamp` au timestamp `$end_timestamp`.
*   `get_children_courses_ordered(int $id_category)` : retourne les cours dans la catégorie d'id `$id_category` dans l'ordre défini dans les paramètres moodle.
*   `get_children_categories_ordered(int $id_category)` : retourne les sous-catégories de catégorie d'id `$id_category` dans l'ordre défini dans les paramètres moodle.
*   `get_whitelisted_courses_ids ()` : retourne dans un tableau d'objets les id des cours non blacklistés.
*   `filter_living_courses_period_on_period(array $ids_courses, int $begin_timestamp, int $end_timestamp)` : retourne dans un tableau d'objets les id, idnumber, nom complet et nom de catégorie des cours dont l'id est un élément du tableau `$ids_courses` et qui ont été visités par au moins un apprenant durant la période allant du timestamp `$begin_timestamp` au timestamp `$end_timestamp`.
*   `count_adhoc_tasks ()` : retourne le nombre de tâches adhoc liées à hybridmeter qui sont programmées actuellement.
*   `clear_adhoc_tasks ()` : supprime et annule toutes les tâches adhoc liées à hybridmeter.
*   `get_adhoc_tasks_list ()` : retourne une liste de l'id et du timestamp de prochain lancement des tâches adhoc liées à hybridmeter actuellement programmées.
*   `schedule_adhoc_task ($timestamp)` : programme une nouvelle tâche à une heure de prochain lancement `$timestamp`.

  

C'est un singleton, dont la méthode statique `data_provider::getInstance()` retourne la référence vers l'instance.

classes/cache\_manager.php
--------------------------

La classe cache\_manager permet de mettre en mémoire les résultats des traitements récurrents afin de ne pas les recalculer inutilement.

  

On y accède grâce à la méthode statique `cache_manager::getInstance()`.

  

Cette classe n'a qu'un attribut qui est un tableau. Pour chaque donnée à mettre en cache, il convient d'initialiser un nouvel élément du tableau.

  

Pour ce faire, de méthodes existent :

*   `update_key($key, $data)` : attribue la valeur `$data` à l'élément `$key` du tableau de cache.
*   `unset_key($key)` : libère la mémoire de l'élément `$key` du tableau de cache.
*   `get_key($key)` : retourne la valeur de l'élément `$key` du tableau de cache.
*   `append_stack_key($array_key, $data)` : empile la valeur `$data` dans le tableau que pointe l'élément $key du tableau de cache. Si l'élément `$key` du tableau de cache n'est pas initialisé, un nouveau tableau est initialisé, s'il contient autre chose qu'un tableau, une exception est émise.
*   `update_associative_array_key($array_key, $key, $data)` : attribue la valeur `$data` à l'élément `$key` du tableau associatif pointé par l'élément `$array_key` du tableau de cache.

  

C'est un singleton, dont la méthode statique `cache_manager::getInstance()` retourne la référence vers l'instance.

classes/exporter.php
--------------------

La classe exporter permet l'export des données sous forme de CSV.

  

La classe contient cinq attributs :

*   **$data** : c'est le tableau de données des cours à exporter, qui doit impérativement être un tableau de tableaux associatifs.

Les données de chaque cours doivent être passés dans un tableau associatif où les clés sont les noms des indicateurs et des propriétés.  
Pour ce faire nous utiliserons la classe `formatter`, décrite un peu plus bas.

```php
//Voici un exemple de tableau bien formaté

$example_data = array(
  array(
    "id_moodle" => 1,
    "fullname" => "Physique des fluides",
    "niveau_digitalisation" => 0.3,
    ...
  ),
  array(
    "id_moodle" => 2,
    "fullname" => "Théorie des jeux",
    "niveau_digitalisation" => 0.5,
    ...
  ),
  ...
);
```

*   **$fields** : ce tableau contient le nom des champs à représenter sur le CSV. Concrètement, ce sont les clés associées aux données que l'on veut retrouver dans le CSV.

Le tableau des champs actuellement utilisés pour les traitements est codé en dur dans constants.php

```php
//Pour reprendre l'exemple ci-dessus :

$example_fields = array("fullname", "niveau_digitalisation");

//Avec ce tableau fields, le fichier CSV n'afficherait que le nom complet du cours et son niveau de digitalisation
```

*   **$alias** : ce tableau est un tableau associatif qui à chaque nom de champ associe son énoncé humain à afficher en haut du CSV. Sans alias, le nom brut du champs est utilisé.

"fullname" est ainsi associé à "Nom du cours" dans le tableau d'alias codé en dur dans constants.php

*   **$csv** : c'est une référence vers une instance de l'objet `csv_export_writer` de moodle utilisé pour la création du CSV.
*   **$delimiter** : c'est un chaîne de caractères décrivant le caractère de délimitation des champs à afficher dans le CSV. Par défaut il vaut "comma", c'est une virgule. Les valeurs supportées sont les suivantes : [`supported types(comma, tab, semicolon, colon, cfg)`](https://wimski.org/api/3.8/d5/d99/classcsv__export__writer.html#a26132b4a8a7bd633f393a26d30fce97d)

  

La signature du constructeur est la suivante :

```php
public function __construct(array $fields = array(), array $aliases = array(), array $raw_data = array(), $delimiter = 'comma')
```

  

On peut indiquer en paramètres du constructeur le tableau de champs, le tableau d'alias, le tableau de données et un délimiteur personnalisé, ou on peut modifier les attributs plus tard via les fonctions suivantes :

  

*   `set_data (array $data)` : cette méthode permet de modifier le tableau de données. Si `$data` n'est pas un tableau de tableaux, la méthode déclenchera une exception.
*   `add_data (array $data)` : cette méthode permet d'ajouter une entrée au tableau de données déjà existant.
*   `set_fields (array $fields)` : cette méthode permet de définir le tableau de champs. Si `$fields` n'est pas un tableau de chaînes de caractères, la méthode déclenchera une exception.
*   `auto_fields ()` : cette méthode permet de définir automatiquement le tableau de champs. Elle récupère les clés de la première occurrence du tableau `$data` dans un tableau et l'affecte à l'attribut `$fields`

```php
//Reprenons le tableau $example_data défini plus haut

$example_exporter = new report_hybridmetrics\classes\exporter();

$example_exporter->set_data($example_data);
$example_exporter->auto_fields();

//Le champ $fields de notre exporter sera ainsi array("id_moodle", "fullname", "niveau_digitalisation", ...)
```

*   `set_alias (array $alias)` : cette méthode permet de modifier le tableau d'alias à utiliser.
*   `set_delimiter (string $delimiter)` : cette méthode permet de modifier le délimiteur à utiliser par moodle, elle peut prendre en paramètre l'une des valeurs suivantes : [`supported types(comma, tab, semicolon, colon, cfg)`](https://wimski.org/api/3.8/d5/d99/classcsv__export__writer.html#a26132b4a8a7bd633f393a26d30fce97d)

  

Une fois que tout est configuré, il faut créer le fichier CSV en indiquant un nom de fichier via la méthode `create_csv (string $filename)`

  

On peut ensuite proposer le CSV au téléchargement à l'utilisateur sur une page web avec la méthode `download_file ()`

  

On peut aussi récupérer le CSV sous forme de chaîne de caractères (pour potentiellement l'enregistrer en local) avec la méthode `csv_data_to_string ()`, ou l'afficher sur la sortie standard avec `print_csv_data_standard ()`

classes/formatter.php
---------------------

La classe formatter a pour fonction de structurer les données.

  

Son constructeur `__construct (array $data)` prend en paramètre un tableau d'objets ou un tableau de tableaux associatifs contenant les informations des cours à analyser : id, nom, catégorie...

  

Si ce tableau est un tableau d'objets, alors il va être converti en tableau de tableaux associatifs. Nous avons développé cette fonctionnalité car la fonction de requêtage SQL de moodle retourne un tableau d'objets, et qu'on a besoin d'un tableau de tableaux associatifs pour traiter les données correctement.

  

La méthode `calculate_new_indicator (?callable $lambda, string $indicator_name, array $parameters = array() )` sert à calculer un nouvel indicateur pour chacun des cours du tableau.

  

C'est-à-dire que pour chaque cours analysé, elle va affecter à un nouvel élément de son tableau associatif les résultats d'une fonction lambda de paramètre le tableau de données du cours.

  

Cette méthode prend deux paramètres obligatoires et un paramètre optionnel :

*   **$lambda** (obligatoire), qui est la fonction lambda qui sera utilisée pour le calcul et qui doit prendre en paramètre les données d'un cours ainsi qu'un tableau de paramètres optionnels. Nous allons voir cela en détails un peu plus bas.

En PHP, le nom de la fonction lambda à appeler doit être indiquée entre guillemets.

*   **$indicator\_name** (obligatoire), qui est une chaîne de caractères représentant le nom de l'indicateur, c'est la clé qui sera associée à la valeur retournée par la fonction lambda pour chaque cours. C'est également le nom qui sera utilisé par la classe exporter (décrite plus haut) pour sélectionner les champs à afficher les résultats sur le CSV.

Le nom des indicateurs doivent être indiqués en minuscule et sans caractères spéciaux si ce n'est le tiret du bas.

*   **$parameters** (optionnel), c'est le tableau de paramètres qui sera passé à la fonction lambda à chaque appel.

  

Il est possible d'obtenir la référence vers le tableau ainsi obtenu avec la méthode `get_data ()` ou sa longueur avec la méthode `get_nb_records ()`

indicators.php
--------------

C'est dans ce fichier que l'on définit les fonctions lambda de calcul d'indicateurs `indicateur_lambda ($object, $parameters)`.

  

Une telle fonction doit obligatoirement prendre deux paramètres :

*   **$object**, qui est un tableau associatif correspondant aux données du cours dont la méthode `calculate_new_indicator` de `formatter` va calculer l'indicateur.
*   **$parameters**, qui est un tableau de paramètres indépendants du cours.

  

```php
/* Supposons que nous voulions mesurer le nombre de clics pour chaque cours, nous pouvons procéder comme suit (cet exemple n'a pas vocation à montrer la manière la plus efficace de le faire)*/

//Dans indicators.php :

function nb_clicks_lambda($object, $parameters){
  //On récupère une instance de data_provider pour faire des appels à la BDD
  $data_provider = data_provider::getInstance();
  //On appelle la méthode (fictive) de data_provider qui retourne le nombre de clics du cours d'ID $id
  $clicks = $data_provider->get_course_clicks($object['id']);
  ...
  return $clicks;
}

//Dans classes/traitement.php

//On veut que pour chaque cours, son nombre de clicks soit affecté à la clé "nb_clicks" de son tableau associatif, pour ce faire :

//Sans paramètres optionnels
$formatter->calculate_new_indicator("nb_clicks_lambda", "nb_clicks");

//Avec des paramètres optionnels
$formatter->calculate_new_indicator("nb_clicks_lambda", "nb_clicks", array("parametre_optionnel" => $parametre_optionnel));


```

classes/traitement.php
----------------------

Cette classe est en charge de fournir la trame du traitement.

  

Cette cette classe qu'on va instancier à chaque programmation de traitement, et dont on va lancer la méthode `launch()` au moment du lancement du traitement.

  

Le traitement est séquentiel et se déroule dans l'ordre suivant :

  

1.  Le constructeur initialise une instance de formatter avec les cours whitelistés et filtrés sur la période de capture.
2.  Le constructeur encore initialise les objets `DateTime` pour les dates de début et de fin de traitement.
3.  À l'appel de `launch()` maintenant, le programme commence par indiquer à config.json qu'un traitement est en cours via la méthode `set_as_running` du configurator.
4.  Il va ensuite se charger de calculer les uns après les autres les différents indicateurs qui figureront dans le CSV grâce à la méthode `calculate_new_indicator` de formatter
5.  Il va ensuite calculer les indicateurs généraux qui figureront sur la page d'accueil du plugin et enregistrer les résultats dans une variable `$generaldata`
6.  Il va ensuite calculer le temps qu'a pris le traitement en faisant une différence entre le timestamp au début et à la fin de celui-ci
7.  Enfin, il sérialise dans une même variable la variable `$generaldata`, le tableau avec les indicateurs cours par cours via la méthode `get_array` du formatter, et le temps de calcul, qu'il reporte dans un fichier à l'intérieur du moodledata, au chemin suivant : `"{$CFG->dataroot}/hybridmeter/records/serialized_data"`
8.  Avant de terminer le traitement, le programme déclare qu'il a terminé son traitement avec la méthode `unset_as_running` de configurator

classes/task/traitement.php
---------------------------

Cette classe est une réalisation de la classe abstraite `\core\task\adhoc_task` de Moodle, c'est-à-dire qu'elle sert à instancier des tâches ad hoc.

Une tâche ad hoc est une classe qui ne s'exécute que ponctuellement à un moment donné

  

Cette classe contient deux méthodes : `get_name()` qui se contente de retourner le nom du plugin, et `execute()` qui sera executée au moment du traitement de la tâche ad hoc : elle se contente de signaler au configurator que la tâche adhoc a été exécutée, de créer une instance de la classe traitement et de lancer le traitement.

classes/task/traitement\_regulier.php
-------------------------------------

Cette classe est une réalisation de la classe abstraite `\core\task\scheduled_task` de moodle, c'est-à-dire qu'elle sert à instancier des tâches à exécution périodique.

  

Cette classe contient deux méthodes :

*   `get_name()` qui retourne le nom du plugin
*   `execute()` qui sera exécutée au moment du traitement de la tâche : elle se contente de créer une instance de la classe traitement et de lancer le traitement.

  

[SITE WEB HYBRIDMETER](https://online.isae-supaero.fr/hybridmeter)