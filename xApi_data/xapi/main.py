"""CE MODULE PYTHON VA PERMMTRE LA CRÉATION D'UNE APPLICATION CLI
   AFIN D'AMÉLIORER L'UTILISATION DES SCRIPTS
"""

import sys
import os

from cliff.app import App
from cliff import complete
from cliff.commandmanager import CommandManager


class xApi(App):
    """Création du module de commandes
    La classe va contenir l'ensemble des commandes possibles à
    exécuter

    Extends:
        App
    """

    # CONSTRUCTEUR
    def __init__(self):

        # Initialisation de l'application de commandes
        super().__init__(
            description='Application permettant une connexion à une base ' +
            'LRS et à une autre base de données permettant le transfert de traces xApi',
            version='1.0',
            command_manager=CommandManager('xapi'),
            deferred_help=True
        )
        self.command_manager.add_command('complete', complete.CompleteCommand)

        # On vérifie si le répertoire qui sauvegarde les fichiers existe
        if not os.path.exists(os.environ['HOME'] + '/.config_xapi'):
            os.makedirs(os.environ['HOME'] + '/.config_xapi')

    # INITIALISATION DE L'APPLICATION
    def initialize_app(self, argv):
        # Instanciation de la commande qui va être exécuté
        self.LOG.debug('initialize_app --> Initialisation de la commande')

    # PRÉPARATION DE LA COMMANDE
    def prepare_to_run_command(self, cmd):
        self.LOG.debug('prepare_to_run_command %s', cmd.__class__.__name__)

    # NETTOYAGE DE L'APPLICATION
    def clean_up(self, cmd, result, err):
        self.LOG.debug('clean_up %s', cmd.__class__.__name__)
        if err:
            self.LOG.debug('got an error %s', err)


# FONCTION MAIN QUI VA EXÉCUTER LA COMMANDE QUE L'UTILISATEUR A INSÉRÉ
def main(argv=sys.argv[1:]):
    app_xApi = xApi()
    return app_xApi.run(argv)


if __name__ == '__main__':
    # Retourne un numéro de signal selon le statut de l'exécution
    sys.exit(main(sys.argv[1:]))
