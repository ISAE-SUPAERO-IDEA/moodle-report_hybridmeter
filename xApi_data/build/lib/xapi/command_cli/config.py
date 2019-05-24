
# IMPORTATION DES MODULES NECESSAIRES
import logging
import os
import pickle
from getpass import getpass
import json

from cliff.command import Command
import xapi.test_configdb as testDb


class Config(Command):
    """Classe contenant le code pour la commande config du CLI --> xApi\n\n

    La commande config permet de configurer les informations de connexions
    afin de se connecter aux bases de données\n\n

    SUB-COMMANDS\n\n

        \tlrs --> Récupère les informations permettant la connexion à la base LRS\n
        \tstore --> Récupère les informations permettant la connexion à la base de données\n
        \tqui va être utilisée pour la sauvegarde des traces xApi récupérées dans la base LRS\n
    """

    log = logging.getLogger(__name__)

    # PARAMÉTRAGE DE LA COMMANDE
    def get_parser(self, prog_name):
        parser = super(Config, self).get_parser(prog_name)
        subparsers = parser.add_subparsers(help='Choose database connexion configuration')

        # Sous commande LRS
        lrs = subparsers.add_parser('lrs', help='LRS connexion configuration')
        lrs.set_defaults(which='lrs')

        # Sous commande DATA BASE
        store = subparsers.add_parser('store', help='Data base connexion configuration to store LRS data')
        store.set_defaults(which='store')

        # Softwares de bases de données compatibles avec le CLI
        self.db_softwares = ['elasticsearch']
        store.add_argument(
            '-db',
            '--database',
            type=str,
            choices=self.db_softwares,
            help='Data Base Softwares'
        )

        return parser

    # APPEL DE FONCTION SELON LES ARGUMENTS DE LA COMMANDE
    def take_action(self, parsed_args):
        # On recupere la sous-commande choisie et on traite la commande en fonction
        if 'which' in parsed_args:
            command = parsed_args.which
            print(str.upper(command) + " configuration choosed")
            if(command == 'lrs'):
                self.__setConfigLRS()
            elif(command == 'store'):
                if parsed_args.database is None:
                    print('Please specified one of these database softwares\n' +
                          'with the following option -db ' +
                          str(self.db_softwares)
                    )
                else:
                    self.__setConfigStore(parsed_args.database)
        else:
            print('Nothing specified, nothing added.\n' +
                'Maybe you wanted to say : xapi config "." ?\nSee help for information'
            )

    # CONFIGURATION DE LA CONNEXION AU LRS
    def __setConfigLRS(self):
        """Fonction permettant de récupérer les informations
            de connexion à la base LRS
        """
        # On récupère les informations de connexion à la base LRS
        parametersLRS = {
            'endpoint': input("ENDPOINT URL (http://example.com/ws/xapi) : "),
            'xApiVersion': input("XAPI VERSION : "),
            'username': input("USERNAME BASIC HTTP AUTHENTICATION : "),
            'password': getpass("PASSWORD BASIC HTTP AUTHENTICATION : ")
        }

        # Test ping
        ping = testDb.testPingDb('lrs', parametersLRS)
        if ping:
            # Enregistrement des paramètres dans un fichier
            self.__saveConfig(parametersLRS)

    # CONFIGURATION DE LA CONNEXION AU STORE DATA BASE
    def __setConfigStore(self, db_software):

        # On se place dans le répertoire qui sauvegarde les parametres de connexion
        os.chdir(os.environ['HOME'] + '/.config_xapi')
        if db_software == 'elasticsearch':
            config_store = self.__setElasticSearchConnexion()

        # Test des paramètres de connexion
        if testDb.testPingDb('store', config_store):
            print('Connexion parameters valid')
            # Enregistrement dans le fichier
            self.__saveConfig(config_store)
        else:
            print('Connexion parameters invalid\n')

    # CONFIGURATION DE LA CONNEXION A ELASTICSEARCH
    def __setElasticSearchConnexion(self):
        # On récupère les informations de connexion à la base de données
        res = input(
            "Do you want to use default parameters ?\n" +
            "host : localhost\n" +
            "port : 9200\n" +
            "index : statements_xapi\n Proceed (y/n) : "
        )

        # Paramètres par défaut
        config_store = {
                'db': 'elasticsearch',
                'host': 'localhost',
                'port': 9200,
                'use_ssl': False,
                'index': 'statements_xapi',
                'username': None,
                'password': None
        }
        while str.lower(res) not in ['y', 'n']:
                res = input("Insert [y, Y] to continue or [n, N] to set your configuration: ")
        # Paramètres utilisateurs
        if str.lower(res) == 'n':
            host = input('Host (Default : localhost) : ')
            if host != '':
                config_store['host'] = host

            port = input('Port (Default : 9200) : ')
            if port is not '':
                config_store['port'] = int(port)
            if port == '443':
                config_store['use_ssl'] = True

            index = input('Index used to store xApi data (Default : statements_xApi ): ')
            if index is not '':
                config_store['index'] = index

            username = input("Username for basic authentication (Leave it blank if None) : ")
            password = getpass("Password for basic authentication (Leave it blank if None) : ")
            if username and password is not '':
                config_store['username'] = username
                config_store['password'] = password

        # Retour des paramètres de configuration
        return config_store

    # VÉRIFICATION D'UN NOM FICHIER DE CONFIG
    def __verifFileName(self, filename):
        """Vérifie si le nom de fichier existe déjà
        
        Si il existe déjà, l'utilisateur aura le choix d'écraser le contenu du fichier
        de configuration qui a le même nom ou non
        
        Arguments:
            filename {string} -- nom du fichier de configuration
        
        Returns:
            number -- Retourne 1 si il veut écraser le contenu du fichier qui porte déja le nom passé en paramètre
                      Retourne 2 si il veut insérer un autre nom de fichier
                      Retourne 0 si il n'existe pas de fichier portant ce nom
        """
        # On se place dans le répertoire qui sauvegarde les parametres de connexion
        os.chdir(os.environ['HOME'] + '/.config_xapi')

        # Vérification de l'existence du nom de fichier de config
        if os.path.exists(filename + '.bin'):
            print('This file name is already used\n')
            res = input('Do you want to continue ? [y or n] :\n')
            while str.lower(res) not in ['y', 'n']:
                res = input("Insert [y, Y] to continue or [n, N] to cancel ")
            if str.lower(res) == 'y':
                return 1
            else:
                return 2
        else:
            return 0

    # ENREGISTREMENT DES CONFIGS DANS UN FICHIER
    def __saveConfig(self, config):
        file_name = input('Insert your config file name: ')
        while self.__verifFileName(file_name) == 2:
            file_name = input('Insert an another config file name: ')
        file = open(file_name + '.txt', 'w')
        config = json.dump(config, file, indent=1)
        file.close()
        print("Connexion parameters saved in : " + os.path.abspath(file_name + '.txt'))
