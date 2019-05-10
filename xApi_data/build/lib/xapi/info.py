# Importation des modules nécessaires
import logging

from cliff.command import Command
from xapi.test_configdb import getInfoConnexionDb


class Info(Command):
    """
    Affiche les informations de configuration
    """

    log = logging.getLogger(__name__)

    # Fonction paramétrant les arguments de la commande
    def get_parser(self, prog_name):
        parser = super(Info, self).get_parser(prog_name)

        # Argument de la commande
        parser.add_argument('filename', help='filename of the config')
        return parser

    # Fonction déclenché par l'appel de la commande
    def take_action(self, parsed_args):
        # Affichage des informations de configuration
        getInfoConnexionDb(parsed_args.filename)
