"""CE MODULE VA PERMETTRE D'ÉTABLIR UNE CONNEXION À LA BASE DE DONNÉES
   DE VOTRE CHOIX ET DE POUVOIR INTERAGIR.
   CETTE BASE DE DONNÉES GÉRERA VOS STATEMENTS xAPI
"""
from xapi.store.elasticsearch_store import elastic_search


class datastore:
    """Cette classe centralise les constructeurs
        des connecteurs à une base de données
        afin d'insérer les données LRS
    """

    # CONSTRUCTEUR
    def __init__(self, config_db):
        # On séléctione le constucteur selon le type de DB
        if config_db['db'] == 'elasticsearch':
            self.db = elastic_search(config_db)

    # AJOUT STATEMENTS
    def saveStatements(self, statements):
        self.db.saveStatements(statements)

    # DELETE INDEX OR DB TABLES
    def deleteIndex_dbName(self):
        self.db.deleteIndex_dbName()

    # CREATE INDEX OR DB NAME
    def createIndex_dbName(self):
        self.db.createIndex_dbName()

    # RETRIEVE LAST TIMESTAMP
    def retrieveLastTimestamp(self):
        return self.db.retrieveLastTimestamp()

    # TEST PING
    def testPing(self):
        return self.db.testPing()
