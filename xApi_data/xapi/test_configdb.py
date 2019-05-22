"""Ce module va permettre de tester les configurations
    de connexion passer par l'utilisateur afin de savoir
    si les paramètres donnés sont fonctionnels
"""

# IMPORTATION DES MODULES
from xapi.lrs.lrs_xApi_data import lrs_data
from xapi.store.datastore_xApi import datastore
import os
import pickle


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


# AFFICHER LES PARAMETRES DE CONNEXION A LA DB
def getInfoConnexionDb(filename):

    if not os.path.exists(filename):
        print(str.upper(filename) + ' doesn\'t exist\n' +
            'To retrieve the statements, please configure the file \nwith this command : xapi config ' +
            "[lrs, store]"
        )
    else:
        file = open(filename, 'rb')
        config = pickle.load(file)
        for key, value in config.items():
            if key != 'password':
                print(key + ': ' + str(value))
