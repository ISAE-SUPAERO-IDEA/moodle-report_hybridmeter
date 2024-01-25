#!/bin/bash

#
# Hybrid Meter
# Copyright (C) 2020 - 2024  ISAE-SUPAERO
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU Affero General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU Affero General Public License for more details.
#
# You should have received a copy of the GNU Affero General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#

words=("ada" "turing" "dijkstra" "lamport" "berners-lee" "shannon" "babbage")
length=${#words[@]}
random_index=$(($RANDOM % ${length}))
echo "[INFO] Staging to moodle ${words[${random_index}]}"
echo $7
if [[ ! $7 == *.php ]]
then
    if [ -d $4/src ]
    then
        rm -R $4/src
    fi
    cp -r $3/src $4/src
    if [ $5 -eq 1 ]
    then
        cd $3 && npm run dev && cd ${OLDPWD}
    elif [ $6 -eq 1 ]
    then
        cd $3 && npm run sass && cd ${OLDPWD}
    fi
    error_code=$?
    if [ ${error_code} -ne 0 ]
    then
        exit ${error_code}
    fi
fi
#rm -R $2/hybridmeter/* && cp -R $1 $2 && rm -R $2/hybridmeter/vue
rsync -r --exclude "vue" $1 $2
error_code=$?
if [ ${error_code} -eq 0 ]
then
    echo "[SUCCESS] Successfully staged to moodle ${words[${random_index}]}"
    exit 0
else
    exit ${error_code}
fi