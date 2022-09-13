#!/bin/bash

words=("ada" "turing" "dijkstra" "lamport" "berners-lee" "shannon" "babbage")
length=${#words[@]}
random_index=$(($RANDOM % ${length}))
echo "[INFO] Staging to moodle ${words[${random_index}]}"
rm -R $4/src && cp -r $3/src $4
cd $3 && npm run dev && cd ${OLDPWD}
error_code=$?
if [ ${error_code} -ne 0 ]
then
    exit ${error_code}
fi
rm -R $2/hybridmeter/* && cp -R $1 $2 && rm -R $2/hybridmeter/vue
#rsync -r --dry-run --exclude "vue" $1 $2 TODO : debug rsync to use it, we have broken pipe issue
error_code=$?
if [ ${error_code} -eq 0 ]
then
    echo "[SUCCESS] Successfully staged to moodle ${words[${random_index}]}"
    exit 0
else
    exit ${error_code}
fi