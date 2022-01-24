Objectifs de Hybridmeter
========================

*   La problématique est de calculer une mesure du degrés d'hybridation numérique d'espaces de cours d'une plateforme pédagogique LMS basée sur le logiciel Moodle
*   L'objectif est de proposer un dispositif automatisé compatible avec Moodle (dernières versions) qui permettrait de réaliser une mesure automatique à une fréquence à déterminer.

**Hypothèse importante** : on suppose que Moodle est utilisé comme Hub des enseignements de l'établissement. C'est très souvent le cas dans les établissements d'enseignement supérieur. On suppose que la plateforme centralisera ainsi tous les cours des programmes de formation de l'établissement.

  

Site Web
========

[![](https://t2594656.p.clickup-attachments.com/t2594656/02a2acc8-fd84-4d24-9e1a-299262ff2ce0/HybridmeterWebsite.png)](https://online.isae-supaero.fr/hybridmeter)

Visitez le site Web principal de HybridMeter : [https://online.isae-supaero.fr/hybridmete](https://online.isae-supaero.fr/hybridmeter)


# Installation et paramétrage

  

===

**Installation du plugin HybridMeter**
======================================

Le plugin HybridMeter se présente sous forme d'un fichier zip qui s'installe de façon standard dans la section administration du site. Il s'agit d'un plugin de type "rapport".

  

*   [Téléchargez la dernière version du plugin ici.](https://online.isae-supaero.fr/resources/hybridmeter/download/report_hybridmeter.zip) Il est livré sous la forme d'une archive ZIP
*   Connectez vous comme administrateur à la plateforme Moodle cible, dont la version doit être supérieure à 3.7
*   En tant qu'administrateur, installez le plugin HybridMeter selon la procédure classique de Moodle.
*   Le plugin n'est visible et utilisable que par le rôle d'administrateur de la plateforme

Le plugin de modifie pas la base de données de Moodle. Les informations sont sauvegardées dans le plugin, sous la forme de simples fichiers.

  

Paramétrage du lancement du plugin
----------------------------------

  

Dans l'administration du site / Rapports, vous trouverez l'entrée HybridMeter.

  

Le plugin est pré-configuré pour déclencher le calcul des indicateurs toutes les nuits à 0h00 du matin. L'objectif est de ne pas impacter les performances de la plateforme pendant les moments d'usage par les étudiants.

  

Pour changer les horaires ou la fréquence de déclenchement des calculs, vous pouvez modifier le paramétrage dans **Administration du site/Serveur/Tâches/Tâches programmées**, en recherchant l'entrée "Indicateurs d'hybridation". Changez pour l'heure souhaitée ou une période selon la syntaxe proposée par Moodle.

  

Il est possible de déclencher immédiatement le calcul avec l'URL suivante : `<URL SERVEUR>/report/hybridmeter/traitement.php.`

Attention, vous risquez d'écrouler les performances de votre plateforme pendant le temps du calcul.  
La page risque d'afficher un délai dépassé si le calcul est trop long, cette URL n'a été conçue que pour tester le plugin sur une plateforme de test.

  

Configuration
-------------

Un bouton  **Configuration** permet de régler d'autres aspects du calcul :

*   **Sélection des cours/catégories** : Par défaut l'ensemble des catégories et des cours de la plateforme sont sélectionnés. Sélectionnez/dé-sélectionnez les cours à ne pas prendre en compte dans le calcul, en affichant/barrant l’œil devant leur titre. Si vous désélectionnez une catégorie, l'ensemble des cours de cette catégorie sont dé-sélectionnés. Si vous re-sélectionnez une catégorie, la sélection des cours est restituée.
*   **Période** **de mesure**: indiquez une date de début et de fin afin de filtrer les résultats sur cette période. Ainsi, ne seront pris en compte que les cours qui ont été ouverts durant cette période, et les clics qui ont été effectués durant cette période.
*   **Programmation du lancement d'un traitement :** indiquez une date et une heure puis cliquez sur "Programmer le lancement" afin de programmer le lancement d'un traitement, vous pouvez également déprogrammer le lancement prévu s'il y en a déjà un avec le bouton "Déprogrammer le lancement"

Si les traitements ne se lancent pas comme prévu, vous pouvez aller vérifier leur bon ordonnancement via le lien `<URL SERVEUR>/report/hybridmeter/planned_tasks.php` .  
Si aucun traitement n'est programmé, la page devrait affiché `[]` , si en revanche un traitement est programmé (et non encore exécuté), elle affichera quelque chose comme `[{"id":"584","nextruntime":"1643072400"}]` : ce qui signifie qu'un traitement a été programmé pour le timestamp 1643072400, soit le 25 janvier 2022 à 2h du matin.

N'oubliez pas d'enregistrer les modifications apportées aux paramètres.

  

La page de configuration présente également des informations non modifiables :

*   **Valeur des coefficients** : une information non modifiable concernant les coefficients définis pour le calcul des indicateurs. Ils sont le fruit d'une concertation entre experts pédagogiques du projet THE Campus de l'UFTMIP. Deux ensemble de coefficients sont distingués, l'un pour le calcul du niveau de digitalisation, l'autre pour le calcul du niveau d'utilisation.
*   **Valeur des seuils :** une information non modifiable concernant les seuils d'hybridation sont proposés. Ces seuils sont utilisés pour déterminer si un cours est hybride ou non avec les résultat des indicateurs, et le nombre d'étudiants minimum d'un cours potentiellement actif.


# Documentation fonctionnelle

Indicateurs calculés
====================

  

On cherche à calculer deux indicateurs complémentaires :

*   **Le niveau de digitalisation (ND)** : C'est une mesure calculée à partir d'activités dans les espaces de cours. Cette mesure se base sur leur nombre, leur diversité et leur caractère hybride. Un seuil détermine si le cours est alors compté comme "hybride numérique" ou non.
*   **Le niveau d'utilisation (NU)** : C'est le niveau de digitalisation (indicateur ND) couplé à l'usage des activités par les étudiants du cours. Chaque espace de cours entrant dans la mesure est analysé et l'indicateur intègre désormais non seulement les activités, mais également l'usage des activités par les étudiants de chaque cours. 

  

A partir de ces indicateurs, on calculera :

*   le nombre de cours hybrides selon ND en appliquant un seuil au dessous duquel les cours ne sont pas comptés
*   Le nombre de cours hybrides selon NU en appliquant un seuil au dessous duquel les cours ne sont pas comptés

Vocabulaire
-----------

*   **Période de mesure :** Date de début et de fin de prise en compte des logs
*   **Activité (a) :** Activité ou ressource statique au sens Moodle
*   **Coefficient d’hybridation d’une activité :** Un coefficient réglable par l’administrateur traduisant le niveau d’activité de l’étudiant lors de l’utilisation de l’activité
*   **Cours actifs :** Un cours utilisé dans la période de mesure
*   **Nombre d’apprenants inscrits :** Les inscrits au cours ayant le statut étudiants ayant visité le cours au moins 1 fois depuis toujours
*   **Nombre d’apprenants actifs :** Les inscrits au cours ayant le statut étudiants ayant visité le cours au moins 1 fois pendant la période de mesure
*   **Nombre d’utilisation d’une activité :** Nombre de clics sur une activité
*   **Activité utilisée**: Une activité est considérée comme utilisée si et seulement si plus de 5 étudiants l'ont visitée sur la période donnée

  

Calcul du niveau de Digitalisation (ND)
=======================================

C = nombre de type d'activités différentes dans le cours

N = nombre total d'activités du cours

P : Un poids associé au cours, caractérisant la diversité d'activités du cours

*   Exemple 1 cours avec 10 forum ne devrait pas être hybride
*   1 cours avec 1 forum, 1 page, 1 rendu de devoir pourrait être hybride

Le but est que la fonction tende vers 1 si C augmente. On ajoute Dc qui est une valeur > 1 au nombre de type d'activités différentes du cours

P = C / (Dc +C)

  

Exemple : Valeurs de x / ( 2 + x ), on suppose que Dc=2, et que le poids du type d'activité serait de 1. Selon le nombre d'activité dans l'espace de cours, on aurait l'évolution suivante :  
pour x = 1 => 0.33  
pour x = 2 => 0.5  
pour x = 3 => 0.6  
pour x = 4 => 0.66  
pour x = 5 => 0.74  
pour x = 6 => 0.75

  

M: Un malus lié au faible nombre de cours dans l'espace :

N <= 2 alors M = 0.25  
N >= 3 alors M = 1

  

Pk : le nombre d'activités par type d'activités

Pk = Nk / (Do + Nk)

  

Plus il y a des activités d'un certain type, plus ce type d'activité influe dans le calcul de manière asymtotique vers 1 afin de limiter le poids d'un type d'activité dans un contexte d'hybridation

Exemple : 20 fichers, ne doivent pas prendre le pas sur les autres activités

  

L'indicateur Niveau de Digitalisation ND est donné par la formule :

ND = M \* P \* \[ sigma (Pk \* Vk) / sigma(Pk) \]

  

Calcul du Niveau d'Utilisation (NU)
===================================

  

Le niveau d'utilisation prend en compte activité par activité, l'utilisation des étudiant.

  

Le calcul du niveau d'utilisation est identique à celui du calcul du niveau de digitalisation à ceci près que pour chaque cours, ne sont pris en compte que les activités utilisées.

  

L'indicateur NU peut être plus élevé quelques fois que l’indicateur ND, notamment si certaines activités peu valorisées (avec un coefficient faible) ne sont en fait pas utilisées par les étudiants et donc éliminées. Ainsi la moyenne augmente puisqu’il reste des activités mieux valorisées dans le cours.


===


# Documentation utilisateur

Si le plugin a déjà réalisé au moins une fois les calculs, la date du calcul et sa durée est indiquée en bas de la page.

  

Voir la partie [Installation et paramétrage](https://app.clickup.com/2594656/v/dc/2f5v0-8317/2f5v0-1588) du plugin pour maitriser le lancement du calcul.

  

Un ensemble de résultats agrégé concernant l'analyse des espaces de cours de la plateforme est affiché directement sur la page du plugin : ![](https://t2594656.p.clickup-attachments.com/t2594656/0d25ea82-ff6e-4643-b73f-b43d6cf6bc72/image.png)

  

Un bouton "Télécharger le compte rendu" vous permet de récupérer les derniers résultats du calcul de manière détaillée, cours par cours, au format CSV.



# Documentation technique

  

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

```
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

*   `count_modules_types_id (int $id)` : cette fonction retourne le nombre d'activités par catégorie du cours d'id `$id`.

Si le cours ne possède que 2 quiz et 1 forum par exemple, la fonction retournera le tableau suivant :

```
array(
```

*   `count_hits_course_viewed (int $id, int $begin_date, int $end_date)` : cette fonction retourne le nombre de visite d'apprenants sur le cours d'id `$id` durant la période allant du timestamp `$begin_date` au timestamp `$end_date`.
*   `count_single_users_course_viewed (array $ids, int $begin_date, int $end_date)` : retourne le nombre d'apprenants uniques ayant visité un cours dont l'id est un élément du tableau `$ids` durant la période allant du timestamp `$begin_date` au timestamp `$end_date`.
*   `count_registered_users (int $id)` : retourne le nombre d'apprenants inscrits dans le cours d'id `$id`
*   `count_distinct_users (array $ids)` : retourne le nombre d'apprenants uniques inscrits dans au moins un cours dont l'id est un élément du tableau `$ids`.
*   `count_hits_by_module_type (int $id, int $begin_date, int $end_date)` : retourne un tableau associant le nom de chaque type d'activité au nombre de clics qui a été enregistré sur les activités du type appartenant au cours d'id `$id` durant la période allant du timestamp `$begin_date` au timestamp `$end_date`.
*   `get_subcategories_id (int $id)` : retourne dans un tableau les id des sous-catégories de la catégorie d'id `$id`.
*   `get_whitelisted_courses ()` : retourne dans un tableau d'objets les id des cours non blacklistés.
*   `filter_living_courses_period(array $ids, int $begin_timestamp, int $end_timestamp)` : retourne dans un tableau d'objets les id, idnumber, nom complet et nom de catégorie des cours dont l'id est un élément du tableau `$ids` et qui ont été visités par au moins un apprenant durant la période allant du timestamp `$begin_date` au timestamp `$end_date`.
*   `count_adhoc_tasks ()` : retourne le nombre de tâches adhoc liées à hybridmeter qui sont programmées actuellement.
*   `clear_adhoc_tasks ()` : supprime et annule toutes les tâches adhoc liées à hybridmeter.
*   `get_adhoc_tasks_list ()` : retourne une liste de l'id et du timestamp de prochain lancement des tâches adhoc liées à hybridmeter actuellement programmées.
*   `schedule_adhoc_task ($timestamp)` : programme une nouvelle tâche à une heure de prochain lancement `$timestamp`.

  

C'est un singleton, dont la méthode statique `data_provider::getInstance()` retourne la référence vers l'instance.

classes/exporter.php
--------------------

La classe exporter permet l'export des données sous forme de CSV.

  

La classe contient cinq attributs :

*   **$data** : c'est le tableau de données des cours à exporter, qui doit impérativement être un tableau de tableaux associatifs.

Les données de chaque cours doivent être passés dans un tableau associatif où les clés sont les noms des indicateurs et des propriétés.  
Pour ce faire nous utiliserons la classe `formatter`, décrite un peu plus bas.

```
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

```
//Pour reprendre l'exemple ci-dessus :

$example_fields = array("fullname", "niveau_digitalisation");

//Avec ce tableau fields, le fichier CSV n'afficherait que le nom complet du cours et son niveau de digitalisation
```

*   **$alias** : ce tableau est un tableau associatif qui à chaque nom de champ associe son énoncé humain à afficher en haut du CSV. Sans alias, le nom brut du champs est utilisé.

"fullname" est ainsi associé à "Nom du cours" dans le tableau d'alias codé en dur dans constants.php

  

*   **$csv** : c'est une référence vers une instance de l'objet `csv_export_writer` de moodle utilisé pour la création du CSV.
*   **$delimiter** : c'est un chaîne de caractères décrivant le caractère de délimitation des champs à afficher dans le CSV. Par défaut il vaut "comma", c'est une virgule. Les valeurs supportées sont les suivantes : [`supported types(comma, tab, semicolon, colon, cfg)`](https://wimski.org/api/3.8/d5/d99/classcsv__export__writer.html#a26132b4a8a7bd633f393a26d30fce97d)

  

La signature du constructeur est la suivante :

```
public function __construct(array $fields = array(), array $aliases = array(), array $raw_data = array(), $delimiter = 'comma')
```

  

On peut indiquer en paramètres du constructeur le tableau de champs, le tableau d'alias, le tableau de données et un délimiteur personnalisé, ou on peut modifier les attributs plus tard via les fonctions suivantes :

  

*   `set_data (array $data)` : cette méthode permet de modifier le tableau de données. Si `$data` n'est pas un tableau de tableaux, la méthode déclenchera une exception.
*   `add_data (array $data)` : cette méthode permet d'ajouter une entrée au tableau de données déjà existant.
*   `set_fields (array $fields)` : cette méthode permet de définir le tableau de champs. Si `$fields` n'est pas un tableau de chaînes de caractères, la méthode déclenchera une exception.
*   `auto_fields ()` : cette méthode permet de définir automatiquement le tableau de champs. Elle récupère les clés du de la première occurrence du tableau `$data` dans un tableau et l'affecte à l'attribut `$fields`

```
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

  

Si ce tableau est un tableau d'objets, alors il va être converti en tableau de tableaux associatifs. Cette fonctionnalité a été développée car la fonction de requêtage SQL de moodle retourne un tableau d'objets, et qu'on a besoin d'un tableau de tableaux associatifs pour traiter les données correctement.

  

La méthode `calculate_new_indicator (?callable $lambda, string $indicator_name, array $parameters = array() )` sert à calculer un nouvel indicateur pour chacun des cours du tableau.

  

C'est-à-dire que pour chaque cours analysé, elle va affecter à un nouvel élément de son tableau associatif les résultats d'une fonction lambda de paramètre les données du cours.

  

Cette méthode prend deux paramètres obligatoires et un paramètre optionnel :

*   **$lambda** (obligatoire), qui est la fonction lambda qui sera utilisée pour le calcul et qui doit prendre en paramètre les données d'un cours ainsi qu'un tableau de paramètres optionnels. Nous allons voir cela en détails un peu plus bas.

En PHP, le nom de la fonction lambda à appeler doit être indiquée entre guillemets.

*   **$indicator\_name** (obligatoire), qui est une chaîne de caractère représentant le nom de l'indicateur, c'est la clé qui sera associée à la valeur retournée par la fonction lambda pour chaque cours. C'est également le nom qui sera utilisé par la classe exporter (décrite plus haut) pour sélectionner les champs à afficher les résultats sur le CSV.

Le nom des indicateurs doivent être indiqués en minuscule et sans caractères spéciaux si ce n'est le tiret du bas.

*   **$parameters** (optionnel), c'est le tableau de paramètres qui sera passé à la fonction lambda à chaque appel.

  

Il est possible d'obtenir la référence vers le tableau via la méthode `get_data ()` ou sa longueur via la méthode `get_nb_records ()`

indicators.php
--------------

C'est dans ce fichier que l'on définit les fonctions lambda de calcul d'indicateurs `indicateur_lambda ($object, $parameters)`.

  

Une telle fonction doit obligatoirement prendre deux paramètres :

*   **$object**, qui est un tableau associatif correspondant aux données du cours dont la méthode `calculate_new_indicator` de `formatter` va calculer l'indicateur.
*   **$parameters**, qui est un tableau de paramètres indépendants du cours.

  

```
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
2.  Le constructeur encore on initialise les objets `DateTime` pour les dates de début et de fin de traitement.
3.  À l'appel de `launch()` maintenant, le programme commence par indiquer à config.json qu'un traitement est en cours via la méthode `set_as_running` du configurator.
4.  Il va ensuite se charger de calculer les uns après les autres les différents indicateurs qui figureront dans le CSV grâce à la méthode `calculate_new_indicator` de formatter
5.  Il va ensuite calculer les indicateurs généraux qui figureront sur la page d'accueil du plugin et enregistrer les résultats dans une variable `$generaldata`
6.  Il va ensuite calculer le temps qu'a pris le traitement en faisant une différence entre le timestamp au début et à la fin de celui-ci
7.  Enfin, il sérialise dans une même variable la variable `$generaldata`, le tableau avec les indicateurs cours par cours via la méthode `get_array` du formatter, et le temps de calcul, qu'il reporte dans un fichier à l'intérieur du moodledata, au chemin suivant : `"{$CFG->dataroot}/hybridmeter/records/serialized_data"`
8.  Avant de terminer le traitement, le programme déclare qu'il a terminé son traitement avec la méthode `unset_as_running` de configurator

classes/task/traitement.php
---------------------------

Cette classe est une réalisation de la classe abstraite `\core\task\adhoc_task` de moodle, c'est-à-dire qu'elle sert à instancier des tâches ad hoc.

Une tâche ad hoc est une classe qui ne s'exécute que ponctuellement à un moment donné

  

Cette classe contient deux méthodes : `get_name()` qui se contente de retourner le nom du plugin, et `execute()` qui sera executée au moment du traitement de la tâche ad hoc : elle se contente de signaler au configurator que la tâche adhoc a été exécutée, de créer une instance de la classe traitement et de lancer le traitement.

classes/task/traitement\_regulier.php
-------------------------------------

Cette classe est une réalisation de la classe abstraite `\core\task\scheduled_task` de moodle, c'est-à-dire qu'elle sert à instancier des tâches à exécution périodique.

  

Cette classe contient deux méthodes : `get_name()` qui se contente de retourner le nom du plugin, et `execute()` qui sera exécutée au moment du traitement de la tâche : elle se contente de créer une instance de la classe traitement et de lancer le traitement.

# Foire aux questions

**Les indicateurs mesurent-ils vraiment tous les cours hybrides de l'établissement ?** 
---------------------------------------------------------------------------------------

Nos établissements se sont pour la plupart structurés autour de la plateforme Moodle, comme portail unique des enseignements. Ainsi, les enseignants sont assez poussés à utiliser les activités venant directement dans la plateforme. Le plugin ne fait à ce stade que compter les activités et leurs usages par les étudiants dans les cours. Il est tout à possible que certains cours n'aient pas utilisé d'activités Moodle et soient très hybride. 

**Les indicateurs sont-ils diffusés en dehors de la plateforme ?**
------------------------------------------------------------------

Seul, le profil administrateur de votre plateforme aura accès aux résultats des calculs, que ce soit les résultats globaux ou le détail par cours. Aucune information n'est stockée dans la base de donnée locale, et aucune information ne sort de la plateforme.

**J'ai détecté un bug, j'ai une suggestion, comment puis-je le signaler ?**
---------------------------------------------------------------------------

*   Utiliser le bouton "Support" (en jaune, en haut à droite des pages du site), permettant la remontée de suggestions à l'équipe de développement. Vous pouvez aussi cliquer sur le bouton suivant.

[Support](https://forms.clickup.com/f/2f5v0-8508/5SDCGICT8X4L037TAF)

**Nous utilisons un plugin spécifique, qu'il faudrait prendre en compte dans les calculs**
------------------------------------------------------------------------------------------

Vous pouvez proposer ce plugin en utilisant le bouton de Support (en haut à droite des pages de ce site), permettant la remontée de suggestions à l'équipe de développement.

**Comment puis-je être averti de nouvelles versions ?**
-------------------------------------------------------

Inscrivez votre email professionnel avec le bouton "Inscription" en haut à droite.

Quel établissement peut installer le plugin et l'utiliser ?
-----------------------------------------------------------

Tout établissement d'enseignement supérieur peut installer le plugin.

**Y a t il une garantie sur ce logiciel ? sur le service ?**
------------------------------------------------------------

Le logiciel est livré gratuitement, avec le code source et cette documentation selon une approche de meilleur effort de la part de l'équipe de développement. Il vous appartient de vérifier que le code informatique livré correspond bien à vos attentes. Le logiciel est utilisable en l'état, modifiable, sans aucune garantie de fonctionnement sur votre plateforme.

L'équipe de développement n'est pas responsable des problèmes qui pourraient être causés par l'installation et l'exécution du code sur votre plateforme.

  

  

  

  

[SITE WEB HYBRIDMETER](https://online.isae-supaero.fr/hybridmeter)