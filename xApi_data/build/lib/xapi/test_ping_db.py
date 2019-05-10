"""Ce module va permettre de tester les configurations
    de connexion passer par l'utilisateur afin de savoir
    si les paramètres donnés sont fonctionnels
"""

# IMPORTATION DES MODULES
from xapi.lrs_xApi_data import lrs_data
from xapi.datastore_xApi import datastore

# TEST PING
def testPingDb(typeDb, config):
    if typeDb == 'lrs':
        # Création de l'objet LRS
        lrs = lrs_data(
            config['endpoint'],
            config['xApiVersion'],
            config['username'],
            config['password']
        )
        return lrs.pingLRS()
    elif typeDb == 'store':
        # Crétion de l'objet STORE
        store = datastore(config)
        return store.testPing()
