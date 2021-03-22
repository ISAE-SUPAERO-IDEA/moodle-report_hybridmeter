#!/bin/bash
import_file(){
	chemintemp=$1;
	echo "Fichier temporaire ${chemintemp} prêt ($(wc -l ${chemintemp}|cut -d' ' -f1) lignes), importation lancée"
	i=0
	while read -r LINE;
	do
	 i=$(($i+1))
	 if [ "$(($i%1000))" -eq 0 ]
	 then
	 	echo "$i records de ${chemintemp} importés"
	 fi
	 #|sed -e 's/\\/\\\\/g')
	 source=$(echo "$LINE"|jq '._source')
	 docid=$(expr -- "$(echo "$LINE"|jq '._id')" : '^"\(.*\)"$')
	 #printf '%s' $LINE | ./JSON.sh
	 #source=`printf '%s' $LINE | JSON.sh | egrep '\["_source"\]' | sed -E 's/\["_source"\](.*)/\1/g'`
	 #echo "$source"|sed -e 's/\\/\\\\/g'>/tmp/bonalors
	 #docid=`printf '%s' $LINE | JSON.sh | egrep '\["_id"\]' | sed -E 's/\["_id"\]([[:space:]]*)(.*)/\2/g' | sed 's/"//g'`

	 if [ "${verbose}" -eq 1 ]
	 then	
	 	 echo -e "\n-----------------\n"
	 	 #printf '%s' $LINE | JSON.sh
		 echo "source"
		 #source=$(expr -- "${source}" : "^' *\([^ ].*[^ ]\) *'$")
		 #source="'${source}'"
		 echo $source 
		 echo "docid"
		 echo $docid
	 fi
	 curl -sS -X POST \
	  -H "Content-Type: application/json" \
	  -d @<(printf "%s" "$source") \
	  "${addresse}/_doc/${docid}">/dev/null
	 code_erreur=$?
	 if [ "${code_erreur}" -ne 0 ]
	 then
		echo "Erreur de requête, vérifiez votre connexion et l'adresse de l'index (code erreur : ${code_erreur})">&2
		#exit ${code_erreur}
		#echo "$source"
	 fi
	done < "${chemintemp}"
}

max(){
	premier=$1
	deuxieme=$2
	if [ "$premier" -gt "$deuxieme" ]
	then
		echo "$premier"
	else
		echo "$deuxieme"
	fi
}

help="toes.sh outil d'importation d'un dump elasticsearch dans un index elasticsearch\n
Dépendences : jq et curl\n\n
Format de la commande\n
toes.sh <lien de l'index> <chemin du dump>\n\n
Options :\n
-v verbose
-h help\n\n
Attention : vous ne pouvez pas donner de guid à ce script, il n'est pas suffisamment sécurisé !\n
"
erreur="Veuillez indiquer le lien de l'index et le chemin du dump (toes.sh <adresse de l'index> <chemin du dump>), plus d'info dans l'aide (toes.sh -h)"
if [ "$#" -lt 1 ]
then
	echo "${erreur}">&2
	exit 1
fi

verbose=0;
option=1
nb_threads=10

while [ "${option}" -eq 1 ]
do
	case "$1" in
	-h)
		printf "${help}";
		exit 0;
	;;
	-v)
		verbose=1
	;;
	-t)
		shift
		nb_threads=$1	
	;;
	-*)
		echo "Cette option n'existe pas, plus d'info dans l'aide (-h)">&2
		exit 1;
	;;
	*)
		option=0
		if [ "$#" -lt 2 ]
		then
			echo "${erreur}">&2
			exit 1
		else
			addresse=$1
			shift
			chemin=$1
		fi
	;;
	esac
	shift
done

if ! which jq>/dev/null
then
	echo "Vérifiez que jq soit bien installé en global sur votre machine pour pouvoir continuer (vous pouvez l'installer depuis votre gestionnaire de paquet officiel)">&2
	exit 2
fi

if ! which curl>/dev/null
then
	echo "Vérifiez que curl soit bien installé sur votre machine pour pouvoir continuer (vous pouvez l'installer depuis votre gestionnaire de paquet officiel \$ sudo apt-get install curl sur un système debian, ou bien \$ sudo dnf install curl sur un système redhat)">&2
	exit 2
fi

if [ ! -f "${chemin}" ]
then
	echo "Le fichier de dump ne semble pas exister à ce chemin, vérifiez le chemin puis reessayez">&2
	exit 3
elif [ "$(expr -- ${chemin} : "^.*\.gz$")" -ne 0 ]
then
	echo "Decompression du fichier..."
	gunzip "${chemin}"
	chemin=$(expr -- ${chemin} : "^\(.*\)\.gz$")
fi

echo "Découpage du fichier pour ameliorer les performances..."

nb_lignes=$(wc -l ${chemin}|cut -d' ' -f1)
nb_lignes_split=$((${nb_lignes}/${nb_threads}))
nb_threads_minus=$((${nb_threads}-1))
i=0
#while read -r LIRE;
#do
#	i=$(($i+1))
#	number_file=$(max "$((${i}/${nb_lignes}))" "${nb_threads_minus}")
#	echo "$i"
#	echo "$number_file"
#	#printf "%s" "${LIRE}"
#	printf "%s\n" "${LIRE}">>"/tmp/oklm${number_file}"
#done < "${chemin}"
for i in $(seq 1 "${nb_threads}")
do
	echo "fichier ${i}"
	head -n"$((${nb_lignes_split}*${i}+10))" "${chemin}"|tail -n"$((${nb_lignes_split}+10))">"/tmp/oklm${i}"
	import_file "/tmp/oklm${i}"&
done

#nb_threads_minus=$((${nb_threads}-1))
#format_number=$(printf "${nb_threads_minus}"|wc -c)
#echo "format_numberlo ${format_number}"
#split -n "${nb_threads}" "${chemin}" -d "/tmp/transfert_toes"


echo "Lancement de l'importation..."
for i in $(seq 1 "${nb_threads}")
do
	#printf "%0${format_number}" "${i}"
	import_file "/tmp/oklm${i}"&
	# $(printf "%0${format_number}d" "${i}")"&
done
