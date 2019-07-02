from elasticsearch import Elasticsearch
from elasticsearch import helpers
import datetime
import logging
import time


class elastic_search:
    """Cette classe établit le lien de connexion
    à une base de données elasticsearch
    Elle permet donc l'échange de données
    """

    # CONSTRUCTEUR
    def __init__(self, config):
        logging.getLogger('elasticsearch').setLevel(logging.WARNING)
        self.index_flat = config['index_flat']
        self.index_enrichment = config['index_enriched']
        connexion = {
            'host': config['host'],
            'port': config['port'],
            'use_ssl': config['use_ssl']
        }
        if config['username'] and config['password'] is not None:
            connexion['http_auth'] = (config['username'], config['password'])

        self.es = Elasticsearch(hosts=[connexion])

    # AJOUT DES STATEMENTS LRS DANS LA BD STORE
    def saveStatements(self, statements, index):
        """Cette fonction ajouter les statements recupérés dans la base de données
        ELASTICSEARCH

        En utilisant la fonction bulk() de la librairie elasticsearch, la fonction va ourvrir
        une connexion avec la base pour envoyer plusieurs documents à la base elasticsearch

        Arguments:
            statements {list} -- [Liste contenant plusieurs statements]
            index {string} -- [index qui va stocker les documents]
        """

        # Creation d'une liste contenant les actions à faire
        bulk_action = []

        # Choix de la base ou de l'index en fonction du type des traces
        if index == 'flat':
            index = self.index_flat
        else:
            index = self.index_enrichment

        # On ajoute dans la liste les actions
        for statement in statements:
            bulk = {
                "_index": index,
                "_id": statement['id'],
                "_type": "statements",
                "_source": statement
            }
            bulk_action.append(bulk)

        helpers.bulk(self.es, bulk_action)

    # SUPPRESSION D'UN INDEX OU REINITIALISATION D'UNE BD
    def deleteIndex_dbName(self, index_type):
        if index_type == 'enrich':
            index = self.index_enrichment
        else:
            index = self.index_flat
        self.es.indices.delete(index=index, ignore=[400, 404])

    # SUPPRESSION D'UN INDEX OU CREATION D'UNE BD
    def createIndex_dbName(self, index_type):
        if index_type == 'enrich':
            index = self.index_enrichment
        else:
            index = self.index_flat
        self.es.indices.create(index=index)

    # CETTE FONCTION RECUPERE DANS LA BASE DE DONNEES ELASTIC SEARCH
    # LE TIMESTAMP DU STATEMENT LE PLUS RECENT
    def retrieveLastTimestamp(self, index_type):
        """
            Retourne le timestamp du statement LRS le plus récent
            dans le store
        """
        index = None
        if index_type == 'enrich':
            index = self.index_enrichment
        else:
            index = self.index_flat

        # On recupère le dernier statement ajouté à la base Elasticsearch
        last = self.es.search(
            index=index,
            body={
                "query": {"match_all": {}},
                "sort": {"stored": "desc"},
                "size": 1,
                "_source": ["stored"]
            }
        )

        # On recupere le timestamp du statetement le plus récent inséré dans la BD ElasticSearch
        timestamp = last['hits']['hits'][0]['_source']['stored']

        print(str(timestamp))

        # Conversion du timestap ISO du statement en datetime
        date_time = datetime.datetime.strptime(timestamp, '%Y-%m-%dT%H:%M:%S.%fZ')
        new = date_time.replace(microsecond=date_time.microsecond - 1)

        # Creation du nouveau timestamp
        new_timestamp = new.isoformat() + 'Z'
        return new_timestamp

    # TEST PING
    def testPing(self):
        return self.es.ping()

    # CETTE FONCTION RECUPERE DANS L'INDEX NON ENRICHI
    # L'ACTION EN PARAMÈTRE PRÉCISE SI ON RÉCUPÈRE
    # L'ENSEMBLE DES TRACES OU NON
    def retrieveFlatStatements(self, action):
        body = {}
        # Si l'utilisateur veut enrichir les traces brutes qu'il n'a pas encore enrichi
        # Et qu'elles se trouvent dans l'index brut
        if action == 'update':
            # On recherche le timestamp de la dernière trace enrichie
            timestamp = self.retrieveLastTimestamp('enrich')
            # On créé la requête afin de récupérer uniquement les traces brutes
            # Avec un timestamp supérieur ou égal au timestamp dernière de la trace enrichie
            body = {
                "query": {"range": {
                    "stored": {
                        "gte": timestamp,
                        "format": "strict_date_time"
                    }
                }
                },
                "sort": {"stored": "asc"}
            }
        else:
            body = {
                "query": {"match_all": {}},
                "sort": {"stored": "asc"}
            }

        # On requête l'index qui contient les traces brutes
        return helpers.scan(self.es, query=body, index=self.index_flat, preserve_order=True)

    # CETTE FONCTION RENVOIE UNIQUEMENT LES STATEMENTS
    # N'AYANT PAS LE TEMPS PASSÉ COMME DONNÉE
    def retrieveStatementsWithoutTimePassed(self):
        body = {
            "query": {
                "bool": {
                    "must_not": {
                        "exists": {
                            "field": "passedTime"
                        }
                    }
                }
            },
            "sort": {"stored": "asc"}
        }

        # On requête l'index qui contient les traces brutes
        return helpers.scan(self.es, query=body, index=self.index_enrichment, preserve_order=True)

    def getPassedTime(self, statement):

        # On récupère le timestamp de la trace
        # Requête
        body = {
            "query": {
                "bool": {
                    "filter": [
                        {
                            "range": {
                                "timestamp": {
                                    "gte": statement['timestamp']
                                }
                            }
                        },
                        {
                            "term": {
                                "actor.account.uuid.keyword": statement['actor']['account']['uuid']
                            }
                        }
                    ]

                }
            },
            "sort": {
                "timestamp": "asc"
            },
            "size": 2,
            "_source": "false",
            "docvalue_fields": ["timestamp"]
        }

        res = self.es.search(
            index=self.index_enrichment,
            body=body
        )

        # On vérifie si l'utilisateur a laissé une trace après celle passé en paramètre
        if len(res['hits']['hits']) == 1:
            return None

        next_timestamp = res['hits']['hits'][1]['fields']['timestamp'][0]
        timestamp = res['hits']['hits'][0]['fields']['timestamp'][0]

        # Conversion des timestamps ISO en datetime
        next = datetime.datetime.strptime(next_timestamp, '%Y-%m-%dT%H:%M:%S.%fZ').timestamp()
        now = datetime.datetime.strptime(timestamp, '%Y-%m-%dT%H:%M:%S.%fZ').timestamp()

        passedTime = next - now

        # On regarde si le temps passé est trop long
        if passedTime > 3600 * 4:
            return 3600
        else:
            return passedTime
