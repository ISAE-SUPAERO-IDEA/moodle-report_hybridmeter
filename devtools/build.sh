#!/bin/bash
#
# Hybryd Meter
# Copyright (C) 2020 - 2024  ISAE-Supaéro
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

set -e
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
export HYBRIDMETER=$DIR/../hybridmeter
export VUE=$HYBRIDMETER/vue
export TMP=/tmp
export TMP_HYBRIDMETER=$TMP/report_hybridmeter
export TMP_HYBRIDMETER_ZIP=$TMP/report_hybridmeter.zip

# Compile vue
cd $VUE
npm install && npm run build

# Prepare files
rm -rf $TMP_HYBRIDMETER
cp -r $HYBRIDMETER $TMP_HYBRIDMETER
rm -rf $TMP_HYBRIDMETER/vue

# Build zip
cd $TMP
rm -rf $TMP_HYBRIDMETER_ZIP
zip -r $TMP_HYBRIDMETER_ZIP report_hybridmeter



