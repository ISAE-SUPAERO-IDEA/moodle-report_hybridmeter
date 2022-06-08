# Installation et paramétrage

[Retour README](../README.md)
===

**Installation du plugin HybridMeter**
======================================

Le plugin HybridMeter se présente sous forme d'un fichier zip qui s'installe de façon standard dans la section administration du site. Il s'agit d'un plugin de type "rapport".

  

*   [Téléchargez la dernière version du plugin ici.](https://online.isae-supaero.fr/resources/hybridmeter/download/report_hybridmeter.zip) Il est livré sous la forme d'une archive ZIP
*   Connectez vous comme administrateur à la plateforme moodle cible, dont la version doit être supérieure à 3.7
*   En tant qu'administrateur, installez le plugin HybridMeter selon la procédure classique de Moodle
*   Le plugin n'est visible et utilisable que par le rôle d'administrateur de la plateforme

Le plugin de modifie pas la base de données de moodle. Les informations sont sauvegardées dans le plugin, sous la forme de simples fichiers.

  

Paramétrage du lancement du plugin
----------------------------------

  

Dans l'administration du site / Rapports, vous trouverez l'entrée HybridMeter.

  

Le plugin est pré-configuré pour déclencher le calcul des indicateurs toutes les nuits à 0h00 du matin. L'objectif est de ne pas impacter les performances de la plateforme pendant les moments d'usage par les étudiants.

  

Pour changer les horaires ou la fréquence de déclenchement des calculs, vous pouvez modifier le paramétrage dans **Administration du site/Serveur/Tâches/Tâches programmées**, en recherchant l'entrée "Indicateurs d'hybridation". Changez pour l'heure souhaitée ou une période selon la syntaxe proposée par Moodle.

  

Il est possible de déclencher immédiatement le calcul avec l'URL suivante : `<URL SERVEUR>/report/hybridmeter/processing.php.`

Attention, vous risquez d'écrouler les performances de votre plateforme pendant le temps du calcul.  
La page risque d'afficher un délai dépassé si le calcul est trop long, cette URL n'a été conçue que pour tester le plugin sur une plateforme de test.

  

Configuration
-------------

Un bouton  **Configuration** permet de régler d'autres aspects du calcul :

*   **Sélection des cours/catégories** : Par défaut l'ensemble des catégories et des cours de la plateforme sont sélectionnés. Sélectionnez/dé-sélectionnez les cours à ne pas prendre en compte dans le calcul, en affichant/barrant l’œil devant leur titre. Si vous dé-sélectionnez une catégorie, l'ensemble des cours de cette catégorie sont dé-sélectionnés. Si vous re-sélectionnez une catégorie, la sélection des cours est restituée.
*   **Période** **de mesure**: indiquez une date de début et de fin afin de filtrer les résultats sur cette période. Ainsi, ne seront pris en compte que les cours qui ont été ouverts durant cette période, et les clics qui ont été effectués durant cette période.
*   **Programmation du lancement d'un traitement :** indiquez une date et une heure puis cliquez sur "Programmer le lancement" afin de programmer le lancement d'un traitement, vous pouvez également déprogrammer le lancement prévu s'il y en a déjà un avec le bouton "Déprogrammer le lancement"

Si les traitements ne se lancent pas comme prévu, vous pouvez aller vérifier leur bon ordonnancement via le lien `<URL SERVEUR>/report/hybridmeter/planned_tasks.php` .  
Si aucun traitement n'est programmé, la page devrait affiché `[]` , si en revanche un traitement est programmé (et non encore exécuté), elle affichera quelque chose comme `[{"id":"584","nextruntime":"1643072400"}]` : ce qui signifie qu'un traitement a été programmé pour le timestamp 1643072400, soit le 25 janvier 2022 à 2h du matin.

  

*   **Configuration additionnelle:** Cette section permet de configurer notamment le rôle utilisé sur la plateforme pour identifier les étudiants

N'oubliez pas d'enregistrer les modifications apportées aux paramètres.

  

  

La page de configuration présente également des informations non modifiables :

*   **Valeur des coefficients** : une information non modifiable concernant les coefficients définis pour le calcul des indicateurs. Ils sont le fruit d'une concertation entre experts pédagogiques du projet THE Campus de l'UFTMIP. Deux ensembles de coefficients sont distingués, l'un pour le calcul du niveau de digitalisation, l'autre pour le calcul du niveau d'utilisation.
*   **Valeur des seuils :** une information non modifiable concernant les seuils d'hybridation sont proposées. Ces seuils sont utilisés pour déterminer si un cours est hybride ou non avec les résultat des indicateurs, et le nombre d'étudiants minimum d'un cours potentiellement actif.

  

[SITE WEB HYBRIDMETER](https://online.isae-supaero.fr/hybridmeter)