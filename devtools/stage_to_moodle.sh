#!/bin/bash

words=("ada" "turing" "dijkstra" "lamport" "berners-lee" "shannon" "babbage")
length=${#words[@]}
random_index=$(($RANDOM % ${length}))
echo "[INFO] Staging to moodle ${words[${random_index}]}"
cd $3 && npm run dev && cd ${OLDPWD}
error_code=$?
if [ ${error_code} -ne 0 ]
then
    exit ${error_code}
fi
rsync -r --exclude "vue" $1 $2
error_code=$?
if [ ${error_code} -eq 0 ]
then
    echo "[SUCCESS] Successfully staged to moodle ${words[${random_index}]}"
    exit 0
else
    exit ${error_code}
fi