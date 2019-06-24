# EXPORTATION DES LIBRAIRIES NECESSAIRES
"""
    MODULE PERMETTANT LA CONNEXION A UNE BASE LRS xAPI
    ET RECUPERATION DES STATEMENTS ET INSERTION DANS LA BASE ELASTICSEARCH
"""
from requests import *
from requests import auth
import json
import time


class lrs_data:
    """Le constructeur permet de récupérer les informations de connexion
        Parameters:
            endpoint (string): url du endpoint de votre base LRS
            xApiVersion (string): version de la communication xApi dans votre base LRS
            username (string): login pour la Basic HTTP Authentication
            password (string): password pour la Basic HTTP Authentication
    """

    # CONSTRUCTEUR
    def __init__(self, endpoint, xApiVersion, username, password):
        # Verification des types des parametres
        if not(isinstance(endpoint, str) and isinstance(xApiVersion, str)
        and isinstance(username, str) and isinstance(password, str)):
            raise NameError("Un des parametres n'est pas de type string")

        # Sauvegarde des informations de connexion à la base LRS
        self.endpoint = endpoint
        self.xApiVersion = xApiVersion
        self.username = username
        self.password = password

        # Création de la basic authentication
        self.basic_auth = auth.HTTPBasicAuth(
            self.username,
            self.password
        )

        # Paramétrages du header de la requête http
        self.headers = {'X-EXPERIENCE-API-VERSION': self.xApiVersion}

    # RECUPERATION DE STATEMENTS LRS
    def getStatements(self, action, db):
        """
            Cette fonction permet de récupérer tous les statements de la base LRS
            Ces statements seront automatiquement insérés dans la base elasticsearch

            /!\\ ATTENTION /!\\
            L'index de la base elasticsearch qui sera utilisé pour la sauvegarde
            des statements sera ecrasé
        """
        exec_time = time.time()
        # Récupération de tous les statements
        if action == 'all':
            # Suppresion et création de l'index
            db.deleteIndex_dbName('flat')
            db.createIndex_dbName('flat')

            # Parémètres de la requête
            params = {'ascending': 'true'}

        # Réupération des statements qui ne sont pas insérés dans le STORE
        elif action == 'update':
            # Récupération du timestamp du dernier statement inséré dans le STORE
            timestamp = db.retrieveLastTimestamp('flat')
            # Paramètres de la requête
            params = {
                'since': timestamp
            }

        # Création de l'url avec le endpoint
        url = self.endpoint + "/statements"

        # RÉCUPÉRATION ET ENREGISTREMENT DU PREMIER BRACKET DE STATEMENTS
        result = self.__insertStatementsBracket(db, url, params)

        nb_statements = result['nb_statements']

        # On regarde si il reste des statements à récupérer
        while 'more' in result:
            # Récupération des autres brackets
            result = self.__insertStatementsBracket(db, result['more'])
            nb_statements += result['nb_statements']

        print('TOTAL EXEC TIME : ' + str(time.time() - exec_time) + '\n')
        print(str(nb_statements) + ' STATEMENTS ADDED')

    # RECUPÉRATION DES STATEMENTS
    def __getStatementsRequest(self, url, params=None):
        # Exécution de la requête
        exec_time = time.time()
        res = get(url, headers=self.headers, params=params, auth=self.basic_auth)
        print('\nRequest LRS time: ' + str(time.time() - exec_time))
        res.encoding = 'utf-8'
        result = json.loads(res.text)
        return result

    # RÉCUPÉRATION D'UN PAQUET DE STATEMENTS
    # AJOUT DES TRACES BRUTES ET DES TRACES ENRICHIES DANS LE STORE
    def __insertStatementsBracket(self, db, url, params=None):
        # Récupération des statements
        result = self.__getStatementsRequest(url, params)

        # Enregistrement des traces brutes dans le store
        db.saveStatements(result['statements'], 'flat')

        if 'more' in result:
            return {'more': result['more'], 'nb_statements': len(result['statements'])}
        else:
            return {'nb_statements': len(result['statements'])}

    # TEST PING
    def pingLRS(self):
        # Création de l'url avec le endpoint
        url = self.endpoint + "/statements"

        res = get(url, headers=self.headers, auth=self.basic_auth)

        # Vérification de l'état de connexion
        if res.status_code == 200:
            print('Connexion establish ! Connexion parameters are valid')
            return True
        elif res.status_code == 401:
            print('Unauthorized ! Basic Authentication credentials are wrong\n' +
                  'Please reconfigure your connexion configuration'
            )
            return False
        elif res.status_code == 500:
            print('Error servor. We cannot test your connexion configuration\n' +
                  'Please try later'
            )
            return False
