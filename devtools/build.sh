#!/bin/bash
set -e
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
export HYBRIDMETER=$DIR/../hybridmeter
export VUE=$HYBRIDMETER/vue
export TMP=/tmp
export TMP_HYBRIDMETER=$TMP/hybridmeter
cd $VUE
npm install && npm run build
rm -rf $TMP/$TMP_HYBRIDMETER
rm -rf $TMP/$TMP_HYBRIDMETER.zip
cp -r $HYBRIDMETER $TMP_HYBRIDMETER
rm -rf $TMP/hybridmeter/vue
zip -r $TMP/report_hybridmeter.zip $TMP_HYBRIDMETER/*



