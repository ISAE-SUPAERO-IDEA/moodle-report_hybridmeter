from elasticsearch import Elasticsearch
from elasticsearch import helpers
import datetime
import logging
import time


VERB_LOGGED_IN = "https://w3id.org/xapi/adl/verbs/logged-in"
VERB_LOGGED_OUT = "https://w3id.org/xapi/adl/verbs/logged-out"
VERB_NAVIGATED_IN = "http://vocab.xapi.fr/verbs/navigated-in"

class elastic_search:
    """Cette classe établit le lien de connexion
    à une base de données elasticsearch
    Elle permet donc l'échange de données
    """

    # CONSTRUCTEUR
    def __init__(self, config):
        logging.getLogger('elasticsearch').setLevel(logging.WARNING)
        self.index_flat = config['index_flat']
        self.index_enrichment = config['index_enrichment']
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

        helpers.bulk(self.es, bulk_action, request_timeout=60)

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
    def retrieveStatementsWithoutField(self, field):
        body = {
            "query": {
                "bool": {
                    "must_not": {
                        "exists": {
                            "field": field
                        }
                    }
                }
            },
            "sort": {"stored": "asc"},
            "docvalue_fields": ["timestamp"]
        }

        # On requête l'index qui contient les traces brutes
        return helpers.scan(self.es, query=body, index=self.index_enrichment, preserve_order=True)

    def getNextStatement(self, statement):
        return self.getAdjacentStatement(statement, filter_operator="gt", sort_order="asc",
            rupture_verbs=[VERB_LOGGED_IN])

    def getPreviousStatement(self, statement):
        return self.getAdjacentStatement(statement, filter_operator="lt", sort_order="desc",
            rupture_verbs=[VERB_LOGGED_OUT])

    def getAdjacentStatement(self, statement, filter_operator, sort_order, rupture_verbs=[]):

        # On récupère le timestamp de la trace
        # Requête
        if not statement["_source"]["verb"]["id"] == VERB_NAVIGATED_IN:
            return None

        body = {
            "query": {
                "bool": {
                    "filter": [
                        {
                            "range": {
                                "timestamp": {
                                    filter_operator: statement["_source"]['timestamp']
                                }
                            },
                        },
                        {
                            "term": {
                                "actor.account.uuid.keyword": statement["_source"]['actor']['account']['uuid'],
                            }
                        },
                        {
                            "term": {
                                "verb.id.keyword": VERB_NAVIGATED_IN
                            }
                        }
                    ]

                }
            },
            "sort": {
                "timestamp": sort_order
            },
            "size": 5,
            "docvalue_fields": ["timestamp"]
        }

        res = self.es.search(
            index=self.index_enrichment,
            body=body
        )
        
        # On cherche la trace
        i = 0
        adjacent_statement_db = None
        while len(res['hits']['hits']) > i:
            candidate = res['hits']['hits'][i]
            i = i + 1
            is_sameplatform = candidate["_source"]["system"]["id"] == statement["_source"]["system"]["id"]
            # si on a un logged in sur la même plateforme que la trace en cours on considére que la trace en cours est la dernière de la série 
            if candidate["_source"]["verb"] in rupture_verbs and is_sameplatform:
                print ("ruptured:" + statement["id"])
                break

            # On ignore logegd in et logged out
            if candidate["_source"]["verb"] in [VERB_LOGGED_IN, VERB_LOGGED_OUT]:
                continue
            
            # Finalement, si on a une trace avec un objet différent et un timestamp différent on la garde
            if candidate["_source"]["object"]["id"] != statement["_source"]["object"]["id"] and candidate["fields"]['timestamp'][0] != statement["fields"]['timestamp'][0]:
                adjacent_statement_db = candidate
                break
            

        if not adjacent_statement_db:
            return None

        
        

        adjacent_timestamp = adjacent_statement_db['fields']['timestamp'][0]
        timestamp = statement['fields']['timestamp'][0]

        # Conversion des timestamps ISO en datetime
        next = datetime.datetime.strptime(adjacent_timestamp, '%Y-%m-%dT%H:%M:%S.%fZ').timestamp()
        now = datetime.datetime.strptime(timestamp, '%Y-%m-%dT%H:%M:%S.%fZ').timestamp()

        distance = next - now

        # On regarde si le temps passé est trop long
        if distance < 0:
            distance = - distance
        if distance > 3600:
            distance = 3600

        obj_id = adjacent_statement_db["_source"]["object"]["id"]
        try:
            obj_name = adjacent_statement_db["_source"]["object"]["definition"]["name"]["any"]
        except:
            obj_name = obj_id

        try:
            obj_type = adjacent_statement_db["_source"]["object"]["definition"]["type"]
        except:
            obj_type = None

        adjacent_statement = {
            "object": { 
                "id": obj_id,
                "definition": {
                    "name": { "any": obj_name },
                    "type": obj_type,
                }
            },
            "system_id": adjacent_statement_db["_source"]["system"]["id"],
            "id": adjacent_statement_db["_source"]["id"],
            "distance": distance
        }
        return adjacent_statement
