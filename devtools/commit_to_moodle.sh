#!/bin/bash

words=("ada" "turing" "dijkstra" "lamport" "berners-lee" "shannon" "babbage")
length=${#words[@]}
random_index=$(($RANDOM % ${length}))
echo "committing to moodle ${words[${random_index}]}"
# TODO : pack the js using webpack &&
rsync -r --exclude "vue" $1 $2
exit $?