"""
CE MODULE VA PERMETTRE DE RÉCUPÉRER LES NOMS RÉELS À PARTIR DES UUID DES STATEMENTS
NON OPTIMISE
"""
from requests import *
import json
import time
from cachetools import cached, TTLCache


# CREATION D'UN CACHE
cacheParentDefinition = TTLCache(maxsize=5000, ttl=1200)
cacheNameLogin = TTLCache(maxsize=5000, ttl=1200)

# ENRICHIT LES TRACES PASSÉES EN PARAMÈTRES
def enrichStatements(statements, configLRS):
    """Fonction permettant l'enrihissement
    des traces.

    Ajoute aux traces :
        - le nom et le login
        - Le cours où se situe la ressource
          si la trace a un parent.

    Arguments:
        statements {[list]} -- liste de traces
    """

    # On ajoute les noms et les logins aux traces
    __addNameUUID(statements)

    # On ajoute la définition des parents pour les traces qui en ont
    __addParentCourseDefinition(statements, configLRS)


# AJOUTE UN NOM À PARTIR D'UN UUID
def __addNameUUID(statements):
    """Fonction permettant de renvoyer les statements en ajoutant
    pour chaque statement le nom réel du user

    Arguments:
        statements {list} -- Liste des statements
    """
    exec_time = time.time()
    # Renommage du paramètre "name" en "uuid" dans l'objet actor
    __renameNameToUUID(statements)

    # Insertion des noms et des logins dans les statements
    for statement in statements:
        namelogin = __getNameAndLogin(statement[i]['actor']['account']['uuid'])
        statement['actor']['account']['name'] = namelogin['name']
        statement['actor']['account']['login'] = namelogin['login']

    print('REAL NAME INSERTION TIME: ' + str(time.time() - exec_time))


# RENOMMAGE DU PARAMÈTRE "NAME" EN "UUID"
def __renameNameToUUID(statements):
    for statement in statements:
        statement['actor']['account']['uuid'] = statement['actor']['account']['name']


# RÉCUPÉRATION DU NOM ET DU LOGIN POUR UN STATEMENT
@cached(cacheNameLogin)
def __getNameAndLogin(uuid):
    # Création de l'url avec le endpoint
    url = "https://lms.isae.fr/webservice/rest/server.php?moodlewsrestformat=json&wsfunction=logstore_trax_get_actors&wstoken=2b8a458c09615e816f571dfc025c3cb9"

    # Création du headers
    # headers = {'Content-Type': 'application/x-www-form-urlencoded'}

    # Création du body
    body = {}

    body['items[0][uuid]'] = uuid

    body['full'] = 1

    # Exécution de la requête POST
    res = post(url=url, data=body)
    res.encoding = 'utf-8'
    result = json.loads(res.text)
    namelogin = {
                    'name': result[0]['xapi']['name'],
                    'login': result[0]['xapi']['account']['name']
                }
    return namelogin


# AJOUT DU LIBELLE DU PARENT
def __addParentCourseDefinition(statements, configLRS):
    exec_time = time.time()
    # On récupère uniquement les traces ayant au moins un parent
    statements_with_parents = []
    for statement in statements:
        if 'parent' in statement['context']['contextActivities']:
            statements_with_parents.append(statement)
        # list(filter(lambda trace: trace['context']['contextActivities']['parent'] in trace, statements))

    # On récupère la définition de chaque parent et on l'insère
    for statement in statements_with_parents:
        # On regarde si la trace a plus d'un parent
        if len(statement['context']['contextActivities']['parent']) > 1:
            print("The statement " + statement['id'] + "have more than one parent")

        parentId = statement['context']['contextActivities']['parent'][0]['id']
        # On récupère la définition et on l'insère à la trace si elle existe
        definition = __getParentDefinition(parentId, configLRS)
        if definition is not None:
            statement['context']['contextActivities']['parent'][0]['definition']['name'] = definition

    print('PARENT DEFINITION INSERTION TIME : ' + str(time.time() - exec_time))


@cached(cacheParentDefinition)
# RÉCUPÉRATION DES LIBELLÉS DES PARENTS
def __getParentDefinition(parentId, configLRS):

    # Création de l'url avec le endpoint
    url = configLRS.endpoint + '/activities'

    # Parémètres de la requête
    params = {'activityId': parentId}

    # Envoi de la requête
    res = get(url, headers=configLRS.headers, params=params, auth=configLRS.basic_auth)
    res.encoding = 'utf-8'
    result = json.loads(res.text)
    if not('definition' in result):
        print('THE COURSE ' + parentId + 'has no definition')
        return None

    return result['definition']['name']
