from xapi.lrs.lrs_xApi_data import lrs_data
from xapi.store.datastore_xApi import datastore
import xapi.enrichment.data_enrichment as dataEnrich
import json
"""Ce script permet d'ajouter l'ensemble des statements de la base LRS
   dans la base de données receveuse
   Ce script supprime l'index de la base qui stocke les statements si il existe
   sinon il le crée
"""


def getLRS(lrs):
    config_lrs = json.load(open(lrs, 'r'))

    # Création de de l'objet LRS
    lrs = lrs_data(
        config_lrs['endpoint'],
        config_lrs['xApiVersion'],
        config_lrs['username'],
        config_lrs['password']
    )

    return lrs


def getStore(store):
    config_db = json.load(open(store, 'r'))
    store = datastore(config_db)
    return store


def getStatements(action, lrs, store):
    """
    Ajoute l'ensemble des statements de la base LRS
    dans la base de données receveuse
    Ce script supprime l'index de la base qui stocke les statements si il existe
    """
    print("Loading statements {}".format(action))
    lrs = getLRS(lrs)
    store = getStore(store)
    # Récupération des statements
    lrs.getStatements(action, store)


def enrichStatements(data, lrs, store):
    print("Enriching statements. data = {}".format(data))
    lrs = getLRS(lrs)
    store = getStore(store)
    if (data == "basic_all"):
        dataEnrich.enrichStatements("all", lrs, store)
    elif (data == "basic_update"):
        dataEnrich.enrichStatements("update", lrs, store)
    else:
        dataEnrich.advancedEnrichStatements(data, store)
   
