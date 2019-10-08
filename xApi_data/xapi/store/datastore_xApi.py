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
    def saveStatements(self, statements, index):
        self.db.saveStatements(statements, index)

    # DELETE INDEX OR DB TABLES
    def deleteIndex_dbName(self, index_type):
        self.db.deleteIndex_dbName(index_type)

    # CREATE INDEX OR DB NAME
    def createIndex_dbName(self, index_type):
        self.db.createIndex_dbName(index_type)

    # RETRIEVE LAST TIMESTAMP
    def retrieveLastTimestamp(self, index_type):
        return self.db.retrieveLastTimestamp(index_type)

    # RÉCUPÈRE LES STATEMENTS BRUTES DE L'INDEX NON ENRICHI
    def retrieveFlatStatements(self, action):
        return self.db.retrieveFlatStatements(action)

    # TEST PING
    def testPing(self):
        return self.db.testPing()

    # RÉCUPÈRE LES STATEMENTS BRUTES DE L'INDEX NON ENRICHI
    def retrieveStatementsWithoutField(self, field):
        return self.db.retrieveStatementsWithoutField(field)

    # RÉCUPÈRE LE TIMESTAMP DE LA TRACE SUIVANTE POUR UN USER DONNÉ
    def getNextStatement(self, statement):
        return self.db.getNextStatement(statement)

    # RÉCUPÈRE LE TIMESTAMP DE LA TRACE SUIVANTE POUR UN USER DONNÉ
    def getPreviousStatement(self, statement):
        return self.db.getPreviousStatement(statement)
