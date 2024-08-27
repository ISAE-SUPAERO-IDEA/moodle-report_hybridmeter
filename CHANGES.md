# ChangeLog

_Ce document décrit les modifications du plugin HybridMeter pour chaque version_

  

[Site HybridMeter](https://online.isae-supaero.fr/hybridmeter)

## Hybridmeter version 1.3.1 (2024-08-27)
### Chore
- Improve code source compliance with Moodle standards

## Hybridmeter version 1.3.0 (2024-06-10)
### Feature
- Allow to configure multiple roles instead of one role archetype

### Fix
- Count only registered students having one of the configured role

## Hybridmeter version 1.2.1 (2024-04-23)
### Chore
- Adding inline documentation
- fix PHP Code Sniffer warnings & errors
- Rework classes structures to conform to Moodle standards
- Various source code improvements

## Hybridmeter version 1.2.0 (2024-04-17)
### Chore
- Add inline documentation
- Rewrite indicators computation (introducing explicit types)
- Remove unnecessary code

## Hybridmeter version 1.1.14 (2024-04-16)
### Chore
- Publish source code on GitHub and update the README accordingly 
- Improve code style fixing some PHPCS errors and warnings


## Hybridmeter version 1.1.13 (2024-04-15)
### Chore
- Update plugin structure to meet Moodle standards
- Improve code style fixing some PHPCS errors and warnings


## Hybridmeter version 1.1.12 (2024-04-11)
### Chore
- Fix classes to ensure autoloading
- Fix typo in messages
- Fix settings.php : check $hassiteconfig first
- Do not import config.php in tasks
- Fix comments format


## Hybridmeter version 1.1.11 (2024-04-04)
### Fix
- Remove security check in settings.php that breaks plugin setup


## Hybridmeter version 1.1.10 (2024-04-04)
### Chore
- Improve code style fixing PHPCS errors and warnings 


## Hybridmeter version 1.1.9 (2024-03-04)
### Chore
- Fix regressions introduced by code cleaning


## Hybridmeter version 1.1.8 (2024-03-04)
### Chore
- Fix errors raised by PHPCS
- Update copyrights within source files

## Hybridmeter version 1.1.7 (2024-02-29)
- Update copyrights within source files


## Hybridmeter version 1.1.6 (2024-02-26)


### Fixes
* Fix computations of summarized indicators that has been broken during a refactor


## Hybridmeter version 1.1.5 (18 avril 2023)
### Améliorations techniques
* AjoutMise en conformité de Hybridmeter avec les normes de développement Moodle:
* Transformation de la librairie javascript en module AMD
* Normalisation du nommage des variables, des fonctions et des fichiers
* Mise à jour du framework de développement front (vuejs 3.0)
* Création de scripts de packaging
* Mise en Compatibilité avec Moodle 4.0


## Hybridmeter version 1.1.4 (18 avril 2023)

### Fonctionnalités
* Ajout d'une fonctionnalité permettant de programmer des lancements automatiquement
* Ajout d'un mode debug permettant d'enregistrer les opérations faites par le plugin dans les logs du serveur web.

### Correction de bugs
* Correction d'un bug qui empêchait le bon rafraîchi

## Hybridmeter version 1.1.3 (21 avril 2022)

# Fonctionnalités
*   Outil de diagnostic des cours

# Améliorations techniques
*   Meilleure utilisation de la classe utils


## Hybridmeter version 1.1.2 (22 mars 2022)

### Fonctionnalités
*   Possibilité de choisir le rôle permettant d'identifiant les étudiants sur la plateforme


## Hybridmeter version 1.1.1 (11 février 2022)

### Fonctionnalités
*   Affichage du chemin complet des catégories dans le CSV
*   Affichage du nom fonctionnel des activités dans les tableaux de coefficients et non le nom technique
*   Possibilité de "whitelister" des cours appartenant à une catégorie blacklistée

### Améliorations techniques
*   Refactorisation des classes exporter, data\_provider, configurator et logger
*   Création d'une classe de gestion de cache pour éviter le calcul redondant.

### Correction de bugs
*   Correction des problèmes de type quand les indicateurs décimaux coïncidaient avec des nombres entiers dans le CSV
*   Cours affichés dans l'ordre sur l'interface de gestion de la blacklist


## HybridMeter version 1.1.0 (28 Janvier 2022)

###  Fonctionnalités
*   Programmation de l'heure et de la date de calcul depuis la page de configuration
*   Suppression du lancement automatique tous les matins à 4h
*   Amélioration de la lisibilité du CSV

### Améliorations techniques
*   Refactorisation de la classe exporter

###  Correction de Bugs
*   Correction des notices qui apparaissaient lors du calcul des indicateurs

## HybridMeter version 1.0.3 (15 Décembre 2021)

### Fonctionnalités
*   Ajouts de liens vers la documentation et le changelog

###  Correction de Bugs
*   Rétablissement de l'export de la metadonnée idnumber (Identification du cours)


## HybridMeter version 1.0.2 (10 Décembre 2021)

### Fonctionnalités
*   Modification des valeurs d'hybridation des activités selon la concertation des membres THE\_Campus
*   Mise en œuvre d'un nouveau modèle de calcul des indicateurs d'hybridation
*   Evolution de la documentation gérée sur un Wiki


## HybridMeter version 1.0.1 (23 septembre 2021)

###  Fonctionnalités
*   Option permettant aux tâches d'être plus ou moins verbeuses dans le log

### Correction de Bugs
*   Amélioration des performances du plugin

## HybridMeter version 1.0.0 (22 septembre 2021)

### Fonctionnalités
*   Version initiale du plugin HybridMeter
*   Choix des cours et des catégories à analyser
*   Affichage et choix de la période de mesure
*   Affichage de la valeur des coefficients de digitalisation et d'utilisation
*   Affichage de la valeur des seuils déterminant si un cours est hybridé ou non, nombre d'étudiants actifs minimum pour un cours actif
*   Nombre de cours analysés
*   Calcul des cours hybrides selon différentes approches (niveau de digitalisation, niveau d'utilisation)
*   Calcul du nombre d'étudiants actifs
*   Etudiants actuellement inscrits au cours
*   Calcul et affichage du temps de traitement
*   Programmation du lancement asynchrone du plugin utilisant le CRON