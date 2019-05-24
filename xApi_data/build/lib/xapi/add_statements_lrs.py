from xapi.lrs.lrs_xApi_data import lrs_data
from xapi.store.datastore_xApi import datastore
import json
"""Ce script permet d'ajouter l'ensemble des statements de la base LRS
   dans la base de données receveuse
   Ce script supprime l'index de la base qui stocke les statements si il existe
   sinon il le crée
"""


def addStatementsLRS(action, lrs, store):
    """
    Ajoute l'ensemble des statements de la base LRS
    dans la base de données receveuse
    Ce script supprime l'index de la base qui stocke les statements si il existe
    """

    config_lrs = json.load(open(lrs, 'r'))

    # Création de de l'objet LRS
    lrs = lrs_data(
        config_lrs['endpoint'],
        config_lrs['xApiVersion'],
        config_lrs['username'],
        config_lrs['password']
    )

    # Création de de l'objet STORE
    config_db = json.load(open(store, 'r'))
    store = datastore(config_db)

    # Récupération des statements
    lrs.getStatements(action, store)
