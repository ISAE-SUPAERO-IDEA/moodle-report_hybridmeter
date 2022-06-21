#!/bin/bash

words=("ada" "turing" "dijkstra" "lamport" "berners-lee" "shannon" "babbage")
length=${#words[@]}
random_index=$(($RANDOM % ${length}))
echo "committing to moodle ${words[${random_index}]}"
cd $3 && npm run dev && cd ${OLDPWD}
rsync -r --exclude "vue" $1 $2
exit $?