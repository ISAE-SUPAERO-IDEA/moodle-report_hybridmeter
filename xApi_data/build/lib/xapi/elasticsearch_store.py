from elasticsearch import Elasticsearch
from elasticsearch import helpers
import datetime
import logging


class elastic_search:
    """Cette classe établit le lien de connexion
    à une base de données elasticsearch
    Elle permet donc l'échange de données
    """

    # CONSTRUCTEUR
    def __init__(self, config):
        logging.getLogger('elasticsearch').setLevel(logging.WARNING)
        self.index = config['index']
        connexion = {
            'host': config['host'],
            'port': config['port'],
            'use_ssl': config['use_ssl']
        }
        if config['username'] and config['password'] is not None:
            connexion['http_auth'] = (config['username'], config['password'])

        self.es = Elasticsearch(hosts=[connexion])

    # AJOUT DES STATEMENTS LRS DANS LA BD STORE
    def saveStatements(self, statements):
        """Cette fonction ajouter les statements recupérés dans la base de données
        ELASTICSEARCH

        En utilisant la fonction bulk() de la librairie elasticsearch, la fonction va ourvrir
        une connexion avec la base pour envoyer plusieurs documents à la base elasticsearch

        Arguments:
            statements {list} -- [Liste contenant plusieurs statements]
        """

        # Creation d'une liste contenant les actions à faire
        bulk_action = []

        # On ajoute dans la liste les actions
        for statement in statements:
            bulk = {
                "_index": self.index,
                "_id": statement['id'],
                "_type": "statements",
                "_source": statement
            }
            bulk_action.append(bulk)

        helpers.bulk(self.es, bulk_action)

    # SUPPRESSION D'UN INDEX OU REINITIALISATION D'UNE BD
    def deleteIndex_dbName(self):
        self.es.indices.delete(index=self.index, ignore=[400, 404])

    # SUPPRESSION D'UN INDEX OU CREATION D'UNE BD
    def createIndex_dbName(self):
        self.es.indices.create(index=self.index)

    # CETTE FONCTION RECUPERE DANS LA BASE DE DONNEES ELASTIC SEARCH
    # LE TIMESTAMP DU STATEMENT LE PLUS RECENT
    def retrieveLastTimestamp(self):
        """
            Retourne le timestamp du statement LRS le plus récent
            dans le store
        """

        # On recupère le dernier statement ajouté à la base Elasticsearch
        last = self.es.search(
            index=self.index,
            body={
                "query": {"match_all": {}},
                "sort": {"stored": "desc"},
                "size": 1,
                "_source": ["stored"]
            }
        )

        # On recupere le timestamp du statetement le plus récent inséré dans la BD ElasticSearch
        timestamp = last['hits']['hits'][0]['_source']['stored']

        # Conversion de l'ISO 8601 du statement en datetime
        date_time = datetime.datetime.strptime(timestamp, '%Y-%m-%dT%H:%M:%S.%fZ')
        new = date_time.replace(microsecond=date_time.microsecond - 1)

        # Creation du nouveau timestamp
        new_timestamp = new.isoformat() + 'Z'
        return new_timestamp

    # TEST PING
    def testPing(self):
        return self.es.ping()
