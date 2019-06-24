# Importation des modules nécessaires
import logging
import os

from cliff.command import Command
from xapi.add_statements_lrs import addEnrichmentStatements


class AdvancedEnrich(Command):
    """
    La commande adavanced_enrich permet d'enrichir les traces plus en profondeur\n
    """

    log = logging.getLogger(__name__)

    # Fonction paramétrant les arguments de la commande
    def get_parser(self, prog_name):
        parser = super(AdvancedEnrich, self).get_parser(prog_name)

        parser.add_argument('data', help='type of data', choices=['time'])
        parser.add_argument('store', help='filename of the store config')
        return parser

    # Fonction déclenché par l'appel de la commande
    def take_action(self, parsed_args):
        # On vérifie si les fichiers de configuration existent
        if not os.path.exists(parsed_args.store):
            print('Store configuration file is not configured\n' + 
                'To save the statements retrieved in LRS, please configure the file with this command : xapi config store'
            )
        else:
            # On recupere l'argument choisit et on traite la commande en fonction
            addEnrichmentStatements(action=parsed_args.data, store=parsed_args.store)
