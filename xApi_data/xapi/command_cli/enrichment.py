# Importation des modules nécessaires
import logging
import os

from cliff.command import Command
from xapi.executor import enrichStatements

class Enrich(Command):
    """
    La commande enrich permet d'enrichir \nles statements LRS dans le store\n
    """

    log = logging.getLogger(__name__)

    # Fonction paramétrant les arguments de la commande
    def get_parser(self, prog_name):
        parser = super(Enrich, self).get_parser(prog_name)

        # Arguments de la commandes
        parser.add_argument('data', help='type of data', choices=['basic_all', 'basic_update', 'time'])
        parser.add_argument('lrs', help='filename of the lrs config')
        parser.add_argument('store', help='filename of the store config')
        return parser

    # Fonction déclenché par l'appel de la commande
    def take_action(self, parsed_args):
        # On vérifie si les fichiers de configuration existent
        if not os.path.exists(parsed_args.lrs):
            print('LRS configuration file is not configured\n' +
                'To retrieve the statements, please configure the file \nwith this command : xapi config lrs'
            )
        elif not os.path.exists(parsed_args.store):
            print('Store configuration file is not configured\n' + 
                'To save the statements retrieved in LRS, please configure the file with this command : xapi config store'
            )
        else:
            enrichStatements( data=parsed_args.data, lrs=parsed_args.lrs, store=parsed_args.store)