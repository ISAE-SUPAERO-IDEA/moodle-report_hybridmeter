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

  

Pour changer les horaires ou la fréquence de déclenchement des calculs, vous pouvez modifier le paramétrage dans **Admnistration du site/Serveur/Tâches/Tâches programmées**, en recherchant l'entrée "Indicateurs d'hybridation". Changez pour l'heure souhaitée ou une période selon la syntaxe proposée par Moodle.

  

Il est possible de déclencher immédiatement le calcul avec l'URL suivante :  
`<URL SERVEUR>/report/hybridmeter/traitement.php.`  
  
Attention, vous risquez d'écrouler les performances de votre plateforme pendant le temps du calcul.

  

Configuration
-------------

Un bouton  **Configuration** permet de régler d'autres aspects du calcul :

*   **Sélection des cours/catégories** : Par défaut l'ensemble des catégories et des cours de la plateforme est sélectionné. Sélectionnez/dé-sélectionnez les cours à ne pas prendre en compte dans le calcul, en affichant/barrant l'oeil devant leur titre.  Si vous désélectionnez une catégorie, l'ensemble des cours sont dé-sélectionnés. Si vous re-sélectionnez une catégorie, la sélection des cours est restituée
*   **Période** **de mesure**: indiquez une date de début et de fin afin de filtrer les résultats sur cette période. Par exemple, les mesures d'utilisation seront filtrées seront limitées à cette période de temps.

  

N'oubliez pas d'enregistrer les modifications apportées aux paramètres.

  

La page de configuration présente également des informations non modifiables :

*   **Valeur des coefficients** : une information non modifiable concernant les coefficients définis pour le calcul des indicateurs. Ils sont isssus d'une concertation entre experts pédagogiques du projet THE Campus de l'UFTMIP. Deux ensemble de coefficients sont distingués, l'un pour le calcul du niveau de digitalisation, l'autre pour le calcul du niveau d'utilisation.
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

  

Description des champs de l'export CSV
======================================

*   TBD



# Documentation technique

  

===

Architecture générale du plugin
-------------------------------

  

Voici une description fichier par fichier du programme :

_index.php_
-----------

L'index a pour fonction d'initialiser la mise en page Moodle et les infos d'authentification, de récupérer les données de la requête HTTP et de lancer les traitements en conséquence.

_configurator.php_
------------------

La classe configurator a pour fonction de mettre à disposition les variables de paramètres et de fournir les fonctions de gestion des paramètres.

C'est grace aux méthodes de cette classe que l'on peut ajouter ou supprimer des cours de la blacklist

_config.json_
-------------

Le fichier de mémorisation des paramètres se présente sous forme d'un json.

La blacklist est simplement représentée comme une liste des id des cours blacklistés.

_data.php_
----------

La classe data est le point unique d'accès à la base de données moodle dans le plugin.

Une instance de cette classe doit être passée en attribut de toutes les classes qui ont besoin d'accéder à la base de données.

_formatter.php_
---------------

La classe formatter structure les données.

Son constructeur requiert une instance de classe data, une variable blacklist, et une fonction lambda prenant les prenant en paramètre pour s'initialiser.

Un attribut de données brutes prendra la valeur de sortie de la fonction lambda. Un autre attribut de classe array accueillera les données restructurées sous forme d'un tableau à deux dimensions.

La méthode calculate\_new\_indicator() permet d'ajouter un indicateur à chaque cours en fonction d'un lambda passée en paramètre.

_indicators.php_

  

C'est dans ce fichier que l'on définit les fonctions lambda correspondant aux indicateurs voulus ainsi que les constantes qui y sont liées. Une fonction lambda d'indicateur doit obligatoirement prendre ces trois variables en paramètres

*   **$object**, qui est un tableau associatif correspondant aux données du cours dont formatter veut calculer l'indicateur.
*   **$data**, qui est une instance de la classe data que l'on peut manipuler pour aller chercher des données.
*   **$parameters**, qui est un tableau de paramètres que l'on peut passer à la fonction de calcul d'indicateur si nécessaire. Par défaut, il s'agit d'un tableau vide.

Pour utiliser la fonction calculate\_new\_indicator() avec ces fonctions lambda, il faut bien penser à passer le nom du lambda en paramètre avec des guillemets, et si besoin d'ajouter des paramètres définir un tableau en lieu et place de $parameters

```
//Dans indicators.php :


function indicateur($object, $data, $parameters){
  $foo=$data->foo($object['id']);
  ...
  return ...
}


//Dans index.php


//Sans paramètre extérieur
$formatter->calculate_new_indicator("indicateur");


//Avec paramètre extérieur
$formatter->calculate_new_indicator("indicateur", array("parametre" => $parametre
```

_exporter.php_
--------------

La classe exporter permet l'export des données sous forme de CSV.

On peut définir manuellement les champs que l'on souhaite voir dans le CSV, ou afficher tous les champs (comportement par défaut).

_utility.php_
-------------

C'est dans ce fichier que l'on définit les fonctions utilitaires, par exemple :

*   ?.??
*   ???

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