from lrs_xApi_data import lrs_data
"""Ce script permet de mettre à jour la base de données Elasticsearch en local port 9000
   en ajoutant les statements de la base LRS qui ne sont pas encore dans ES
"""

def main():
    # AJOUT DES INFORMATIONS DE CONNEXION
    version = "1.0.0"
    endpoint = "http://trax.isae-supaero.fr/trax/ws/xapi"
    username = "ghautecoeur"
    password = "91c37937-124f-4f15-b2f4-a60b9d8d3388"

    lrs_data_supaero = lrs_data(endpoint, version, username, password)

    # Récupération des statements
    lrs_data_supaero.updateStatementsStore()


if __name__ == "__main__":
    main()
