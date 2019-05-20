"""
CE MODULE VA PERMETTRE DE RÉCUPÉRER LES NOMS RÉELS À PARTIR DES UUID DES STATEMENTS
NON OPTIMISE
"""
from requests import post
import json
import time

# AJOUTE UN NOM À PARTIR D'UN UUID
def addNameUUID(statements):
    """Fonction permettant de renvoyer les statements en ajoutant
    pour chaque statement le nom réel du user

    Arguments:
        statements {list} -- Liste des statements
    """
    exec_time = time.time()
    # Renommage du paramètre "name" en "uuid" dans l'objet actor
    __renameNameToUUID(statements)

    # Récupération des noms
    names = __getNames(statements)

    # Insertion des noms dans les statements
    for i in range(len(statements)):
        name = json.loads(names[i]['xapi'])
        statements[i]['actor']['account']['name'] = name['name']

    print('REAL NAME INSERTION TIME: ' + str(time.time() - exec_time))

# RENOMMAGE DU PARAMÈTRE "NAME" EN "UUID"
def __renameNameToUUID(statements):
    for statement in statements:
        statement['actor']['account']['uuid'] = statement['actor']['account']['name']


# RÉCUPÉRATION DES NOMS POUR CHAQUE STATEMENT
def __getNames(statements):
    # Création de l'url avec le endpoint
    url = "https://lms.isae.fr/webservice/rest/server.php?moodlewsrestformat=json&wsfunction=logstore_trax_get_actors&wstoken=2b8a458c09615e816f571dfc025c3cb9"

    # Création du headers
    # headers = {'Content-Type': 'application/x-www-form-urlencoded'}

    # Création du body
    body = {}
    for i in range(len(statements)):
        body['items[' + str(i) + '][uuid]'] = statements[i]['actor']['account']['uuid']

    body['full'] = 1

    # Exécution de la requête POST
    res = post(url=url, data=body)
    res.encoding = 'utf-8'
    result = json.loads(res.text)
    return result
