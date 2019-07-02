"""
CE MODULE VA PERMETTRE DE RÉCUPÉRER LES NOMS RÉELS À PARTIR DES UUID DES STATEMENTS
NON OPTIMISE
"""
from requests import *
from requests.adapters import HTTPAdapter
from requests.exceptions import ConnectionError
import json
import time
from cachetools import cached, TTLCache
import hashlib
from urllib import parse


# CREATION D'UN CACHE
cacheParentDefinition = TTLCache(maxsize=5000, ttl=1200)
cacheNameLogin = TTLCache(maxsize=5000, ttl=1200)
cacheHash = TTLCache(maxsize=5000, ttl=1200)


# ENRICHIT LES TRACES PASSÉES EN PARAMÈTRES
def enrichStatements(action, lrs, store):
    # Création du log

    """Fonction permettant l'enrichissement
    des traces.

    Ajoute aux traces :
        - le nom et le login
        - Le cours où se situe la ressource
          si la trace a un parent.

    Arguments:
        action {string} -- option de la commande
        configLRS -- objet de la base LRS
        configStore -- objet de la base Store
    """

    if action == 'all':
        print("Recreating index 'enrich'")
        store.deleteIndex_dbName('enrich')
        store.createIndex_dbName('enrich')

    # ON RÉCUPÈRE LA LISTE DES STATEMENTS
    statements = store.retrieveFlatStatements(action)
    bulk_list = []
    exec_time = time.time()
    nb_statements = 0
    for statement in statements:

        trax = statement['_source']
        # On ajoute les noms et les logins aux traces
        __addNameUUID(trax)

        # On ajoute la définition du cours si l'activité de la trace
        # est une activité au sein d'un cours 
        __addCourseDefinition(trax, lrs)

        # On ajoute la définition du système
        __addSystem(trax, lrs)

        #On ajoute l'activité correspondante à la trace
        __addActivity(trax)

        # On ajoute les acl à la trace
        __addACL(trax)

        bulk_list.append(trax)
        nb_statements += 1

        # Au bout de 1000 traces enrichies
        # Je les insère dans l'index pour éviter d'insérer
        # 4 millions de traces d'un coup
        if len(bulk_list) == 1000:
            print('Insertion of 1000 enrich statements')
            print('ENRICHMENT 1000 TRAX TIME : ' + str(time.time() - exec_time))
            print(str(bulk_list[0]['acl']))
            print(str(bulk_list[999]['acl']))
            exec_time = time.time()
            store.saveStatements(bulk_list, 'enrich')
            bulk_list = []

    print('Insertion of ' + str(len(bulk_list)) + ' enrich statements' + '\n')
    store.saveStatements(bulk_list, 'enrich')

    print('\n' + str(nb_statements) + ' STATEMENTS ENRICHED')


# ENRICHIT LES TRACES AVEC DONNÉES AVANCÉES
def advancedEnrichStatements(data, store):
    if data == 'time':
        __addPassedTime(store)


# AJOUTE UN NOM À PARTIR D'UN UUID
def __addNameUUID(statement):
    """Fonction permettant de renvoyer les statements en ajoutant
    pour chaque statement le nom réel du user

    Arguments:
        statement {dict} -- statement
    """
    exec_time = time.time()
    # Renommage du paramètre "name" en "uuid" dans l'objet actor
    __renameNameToUUID(statement)

    # Insertion du nom et du login dans le statement
    namelogin = __getNameAndLogin(statement['actor']['account']['uuid'])
    statement['actor']['account']['name'] = namelogin['name']
    statement['actor']['account']['login'] = namelogin['login']

    # print('REAL NAME INSERTION TIME: ' + str(time.time() - exec_time))


# RENOMMAGE DU PARAMÈTRE "NAME" EN "UUID"
def __renameNameToUUID(statement):
    statement['actor']['account']['uuid'] = statement['actor']['account']['name']


# RÉCUPÉRATION DU NOM ET DU LOGIN POUR UN STATEMENT
@cached(cacheNameLogin)
def __getNameAndLogin(uuid):

    # Création d'un adapter qui va contenir le nombre d'essais d'une requête HTTP
    lms_adapter = HTTPAdapter(max_retries=10)

    # Création d'une session
    s = Session()
    

    # Création de l'url avec le endpoint
    url = "https://lms.isae.fr/webservice/rest/server.php?moodlewsrestformat=json&wsfunction=logstore_trax_get_actors&wstoken=2b8a458c09615e816f571dfc025c3cb9"
    
    s.mount(url, lms_adapter)
    
    # Création du headers
    # headers = {'Content-Type': 'application/x-www-form-urlencoded'}

    # Création du body
    body = {}

    body['items[0][uuid]'] = uuid

    body['full'] = 1

    # Exécution de la requête POST
    res = s.post(url=url, data=body, timeout=None)
    res.encoding = 'utf-8'
    result = json.loads(res.text)
    result = json.loads(result[0]['xapi'])
    namelogin = {
                    'name': result['name'],
                    'login': result['account']['name']
                }
    return namelogin


# AJOUT DU LIBELLE DU PARENT
def __addCourseDefinition(statement, configLRS):
    exec_time = time.time()
    courseId = None
    # On vérifie si l'activité de la trace est un cours
    contextActivities = statement['context']['contextActivities']
    if statement['object']['definition']['type'] == "http://vocab.xapi.fr/activities/course":
        courseId = statement['object']['id']

    # On récupère les traces qui contient une activité au sein d'un cours
    # On vérifie si la trace contient un parent
    elif(('parent' in contextActivities) and 
        (contextActivities['parent'][0]['definition']['type'] == "http://vocab.xapi.fr/activities/course")):
        # On regarde si la trace a plus d'un parent
        if len(contextActivities['parent']) > 1:
            print("The statement " + statement['id'] + "have more than one parent")  # Créer un LOG

        courseId = contextActivities['parent'][0]['id']

    # On regarde dans le grouping si il y a une activité de type cours
    else:
        if 'grouping' in contextActivities:
            for activity in contextActivities['grouping']:
                # On regarde si l'activité est un cours
                if activity['definition']['type'] == "http://vocab.xapi.fr/activities/course":
                    courseId = activity['id']
                    break

    # On récupère la définition et on l'insère à la trace si elle existe
    if courseId is not None:
        statement['course'] = __getCourse(courseId, configLRS)
        statement['course']['hash'] = __getHash(courseId)

    # print('PARENT DEFINITION INSERTION TIME : ' + str(time.time() - exec_time))


@cached(cacheParentDefinition)
# RÉCUPÉRATION DES LIBELLÉS DU PARENT
def __getCourse(courseId, configLRS):

    # Création de l'url avec le endpoint
    url = configLRS.endpoint + '/activities'

    # Parémètres de la requête
    params = {'activityId': courseId}

    # Envoi de la requête
    res = get(url, headers=configLRS.headers, params=params, auth=configLRS.basic_auth)
    res.encoding = 'utf-8'
    result = json.loads(res.text)
    return result


# AJOUT DES ACL À LA TRACE
def __addACL(statement):
    # On insère le créateur de la trace
    statement['acl'] = {
        'user': {
                    'login': statement['actor']['account']['login'],
                    'uuid': statement['actor']['account']['uuid']
                },
        'system': statement['system']['hash']
    }

    # On regarde si la trace contient un cours
    if 'course' in statement:
        statement['acl']['group'] = statement['course']['hash']


@cached(cacheHash)
# Récupération ou hashage d'un id
def __getHash(id):
    m = hashlib.sha256()
    m.update(bytes(id, 'utf-8'))
    return m.hexdigest()


# Ajout de la definition du systeme
def __addSystem(statement, lrs):
    system = "http://vocab.xapi.fr/activities/system"
    # On vérifie si la trace a comme pour objet une activité système
    id = None
    if statement['object']['definition']['type'] == system:
        id = statement['object']['id']
    else:
        for activity in statement['context']['contextActivities']['grouping']:
            if activity['definition']['type'] == system:
                id = activity['id']
                break
    statement['system'] = __getCourse(id, lrs)
    statement['system']['hash'] = __getHash(id)


# Ajout du temps passé aux traces
def __addPassedTime(store):

    # On récupère d'abord les traces n'ayant pas la durée
    statements = store.retrieveStatementsWithoutTimePassed()
    nb_statements = 0

    bulk_list = []

    for statement in statements:

        statement = statement['_source']
        # On récupère le temps passé de la trace
        # Si il y a une trace stockée après celle en paramètre
        passedTime = store.getPassedTime(statement)

        # Si NONE, on ne peut pas savoir le temps passé
        if passedTime is not None:
            statement['timespent'] = passedTime
            nb_statements += 1
            bulk_list.append(statement)

            # Au bout de 1000 traces enrichies
            # Je les insère dans l'index pour éviter d'insérer
            # 4 millions de traces d'un coup
            if len(bulk_list) == 1000:
                print('Insertion of 1000 enrich statements')
                print(str(bulk_list[0]['passedTime']))
                store.saveStatements(bulk_list, 'enrich')
                bulk_list = []

    print(str(nb_statements) + 'STATEMENTS ENRICHED')


# Ajout de l'activité de la trace
def __addActivity(statement):

    # On récupère la définition et le type de la trace
    objectType = statement['object']['definition']['type']

    # On vérifie si l'objet de la trace est une activité
    if "activities" in objectType:

        # On ajoute le paramètre "activity" dans la trace
        statement['activity'] = statement['object']
        definition = objectType.split("/")
        statement['activity']['type'] = definition[len(definition) - 1]
