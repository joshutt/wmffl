#!/bin/sh

CFG_FILE="../../conf/wmffl.conf"
CFG_CONTENT=$(sed -r '/[^=]+=[^=]+/!d;s/\s+=\s/=/g' $CFG_FILE)
eval "$CFG_CONTENT"

cd $pyInjPath
source $injEnvDir/bin/activate

# update injury list
python injuryList.py

# clean up IR
python checkIR.py

# update COVID list
python covid.py