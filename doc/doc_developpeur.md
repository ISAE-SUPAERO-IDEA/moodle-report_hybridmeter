# Documentation développeur

[Retour README](../README.md)

Configuration de l'environnement de travail
=====================================

Installation de Moodle
-------------

Nous vous recommandons vivement de travailler dans un environnement Linux, et d'installer Moodle sur la même machine que celle sur laquelle vous développez : pour ce faire, il suffit de suivre les instructions fournies dans la documentation de Moodle : https://docs.moodle.org/4x/fr/Installation_de_Moodle

Si vous travaillez depuis un poste de travail Windows, nous vous conseillons d'installer le Windows Subsystem Linux (WSL), et de cloner le git du projet dans un sous-système Linux avant d'y installer Moodle.

Visual Studio Code permet d'accéder à vos fichiers sur la WSL depuis une instance Windows de l'IDE grâce au plugin ```Remote - WSL``` https://marketplace.visualstudio.com/items?itemName=ms-vscode-remote.remote-wsl

Ainsi, vous profitez d'un environnement de travail optimal avec tout le confort de votre IDE habituel.

De plus désactivez le cache javascript afin de ne pas avoir à le purger pendant le développement: Site administration ► Appearance ► AJAX and Javascript

Configuration du gestionnaire de paquet npm
------------

L'interface du plugin est développée en javascript dans un environnement d'éxecution Node.js, et ces dépendances sont gérées avec le gestionnaire de paquets npm, qu'il faut installer : https://docs.npmjs.com/downloading-and-installing-node-js-and-npm

Une fois le gestionnaire de paquet installé, il est nécessaire d'installer les dépendances du plugin, rendez-vous dans le répertoire ```hybridmeter/vue``` depuis la racine du git et executez la commande suivante ```npm i```

Outil d'intégration continue
====================================

Utilisation
----------

Nous avons développé un outil d'intégration continue pour faciliter le travail des développeurs, cet outil a deux modes principaux :

- Le mode stage ou autostage, qui permet de rapidement générer un bundle et pousser le code du git vers le serveur moodle local pour en voir les effets directement dans son navigateurs. Le mode autostage répète cette action à chaque fois qu'un fichier est modifié, supprimé, ou ajouté dans le code source.

- Le mode build, qui permet de générer un bundle de production et de construire un fichier zip. C'est ce mode qui est utilisé par jenkins pour générer le zip de chaque nouvelle version avant de le publier.

Configuration
----------

Avant d'utiliser le plugin il est nécessaire de le configurer.

Rendez-vous dans le répertoire ``devtools`` depuis la racine du git, munissez-vous du chemin vers la racine de votre moodle sur votre serveur web (répertoire dans lequel il y a le fichier ``config.php`` par exemple), et exécutez ``./hybridmeter_dev autoconfig <RACINE_DE_MOODLE>``

Un fichier hybrid_dev.env sera généré dans le répertoire et contiendra le lien vers le code source du plugin et celui vers la racine de moodle.

Il est ensuite nécessaire d'installer les paquets ``inotify-tools``, ``rsync``, et ``npm`` si ce n'est pas déjà fait

```bash
# Avec APT
sudo apt-get update && sudo apt-get install inotify-tools rsync npm

# Avec DNF
sudo dnf update && sudo dnf install inotify-tools rsync npm
```

Nous vous recommandons enfin d'ajouter le chemin vers le répertoire ``devtools`` dans votre ``.bashrc`` afin de pouvoir executer la commande ``hybridmeter_dev`` depuis n'importe quel répertoire, pour cela rendez-vous dans le répertoire ``devtools`` et éxecutez :
```bash
echo -e "\n#Hybridmeter\nexport OLDPATH=\"${PATH}\"\nexport PATH=\"${PATH}:${PWD}\"" >> ~/.bashrc
export PATH=\"${PATH}:${PWD}\"
```

Utilisation du bundler javascript
====================================

L'interface du plugin utilise Vue.js, et prend la forme d'un module AMD que l'on passe au moteur de moodle.

Moodle attend un bundle de ce module dans le répertoire ``report/hybridmeter/amd/build`` depuis la racine de moodle, et le code source de l'interface est dans le repertoire ``hybridmeter/vue`` depuis la racine du git et est prévu pour être exécuté dans un environnement Node.js.

Il est donc nécessaire de traduire le code en bundle à chaque modification de celui-ci avant de tester le plugin ou d'en publier une nouvelle version.

Pour ce faire, nous utilisons le module bundler webpack, qui à chaque type de fichier rencontré va associer un "loader" qui sera capable de le traduire en code javascript compréhensible par le navigateur et de l'insérer dans le reste du bundle.

Nous avons choisi webpack car il propose la possibilité de traduire des fichiers .vue très facilement et en permettant le débugguage des composants directement dans le navigateur.

Pour générer le bundle, il faut se rendre dans le répertoire ``hybridmeter/vue`` depuis la racine du git, et éxecuter la commande ``npm run dev`` pour une version de développement ou ``npm run build`` pour une version de production.

Le mode ``dev`` produira un bundle plus lourd et moins performant que celui du mode ``build``, mais la génération se fera plus rapidement, et le bundle sera débugguable depuis le navigateur. 

Configuration du navigateur 
==================================

Nous vous recommandons d'installer le plugin Vue.js devtools, qui propose tout un tas d'outils pour visualiser l'état des composants, le déclenchement des évènements... afin de débugguer plus facilement l'interface : https://addons.mozilla.org/fr/firefox/addon/vue-js-devtools/

Règles de codage moodle
==================================

La communauté Moodle impose des règles de codage pour assurer le bon fonctionnement et l'homogénéité du coeur du programme et des plugins.

La tabulation doit par exemple être constituée de quatre espaces, et il faut laisser un saut de ligne en fin de fichier sans ne jamais fermer la balise PHP...

la liste exhaustive de cette règle est documentée sur [ce site](https://moodledev.io/general/development/policies/codingstyle)

Il existe également un plugin qui permet de vérifier la bonne syntaxe de son code. Le plugin s'appelle code checker et est téléchargeable [sur le store moodle](https://moodle.org/plugins/local_codechecker)

Pour l'utiliser, il suffit de se rendre dans l'Administration du site > Development > Code Checker et de renseigner la cible des fichiers à analyser, dans notre cas ``report/hybridmeter``
