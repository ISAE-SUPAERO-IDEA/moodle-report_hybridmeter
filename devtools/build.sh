#!/bin/bash
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



