# xDash
Repository dans le cadre du stage à ISAE-SUPAERO pour le projet xDash

## CLI POUR RECUPERATION DE STATEMENTS xAPI

Cette CLI va permettre la récupération de traces d'apprentissages xApi d'une base LRS afin de les insérer dans une autre base de données afin de permettre par la suite du travail d'analyse et de visualisation

### PREREQUISITES

Afin que le CLI fonctionne, il faut les modules suivants :

* Cliff
* Elasticsearch

Installez les modules si ils ne sont pas installés :

```
sudo pip install cliff elasticsearch
```

### INSTALLING

Téléchargement du dépôt
```
git clone https://github.com/Graaxes/xDash.git
```

Installation du wheel
```
pip install dist/xapi-1.0-py3-none-any.whl
```

## COMMANDES DU CLI
On accède au CLI avec le mot clé : "xapi"
Trois commandes sont disponibles :
*config
*info
*statements

### COMMANDE CONFIG

La commande permet de configurer des fichiers de configuration pour établir des connexions à des bases de données. Dans le cadre du projet xDash, on relève deux types de bases de données :

* LRS : Base de données stockant les traces d'apprentissages. Nous allons juste faire de la récupération de données sur les LRS
* STORE : Bases de données qui va sauvegarder les traces récupérées dans les LRS.
/!\ : Tous les fichiers de configuration sont enregistrés dans un dossier nommé "./config_xapi" créé par le CLI.

```
cd ~/.config_xapi/
```

Deux sous commandes sont donc disponibles

#### LRS
```
xapi config lrs
```
Configuration de fichier pour établir une connexion à un LRS

#### STORE
```
xapi config store -db {elasticsearch}
```
Configuration de paramètres de connexion à une base de données afin de sauvegarder les traces.
Le seul type de bases de données pris en charge actuellement par le CLI est elasticsearch.

## COMMANDE INFO
```
xapi info {filename}
```
Permet de connaître les informations de configuration d'un fichier de config. Si un password est défini, il n'est pas affiché.

## COMMANDE STATEMENTS
```
xapi statements [-A] [-u] lrs store
```
La commande statements permet la récupération de statements dans la base LRS passé en paramètre vers la base "store" passé en paramètre.
Deux choix sont disponibles pour la récupération de statements : 

*-u ou --update : Récupère dans la base LRS, les traces qui n'on pas encore été insérées dans la base store
*-A ou --all : Récupère l'ensemble des statements dans la base LRS, et les insère dans la base store. 
/!\ ATTENTION /!\ : La base ou l'index (elasticsearch) utilisé pour l'enregistrement des traces sera vidé avant l'insertion.
