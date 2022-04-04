# Documentation fonctionnelle

[Retour README](../README.md)

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
*   **Nombre d’apprenants inscrits :** Les inscrits au cours ayant le statut étudiant ayant visité le cours au moins 1 fois depuis toujours
*   **Nombre d’apprenants actifs :** Les inscrits au cours ayant le statut étudiant ayant visité le cours au moins 1 fois pendant la période de mesure
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

  

[SITE WEB HYBRIDMETER](https://online.isae-supaero.fr/hybridmeter)

  

===