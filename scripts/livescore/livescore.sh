#!/bin/sh

SCRIPT=$( dirname $0)
source $SCRIPT/../../conf/livescore.conf
FAILURE=1

if test -e $SCRIPT_DIR/tmpFile && `find "$SCRIPT_DIR/tmpFile" -mmin +5`
then
    exit 0
fi
touch $SCRIPT_DIR/tmpFile

WEEK=`/usr/local/bin/php $SCRIPT_DIR/getweek.php`
ZIPFILE="${DATA_DIR}/zip${WEEK}.zip"

echo 
echo "---------------------------------"
echo "Live Scores"
echo `date`
echo "Week: $WEEK"
echo "---------------------------------"

python $SCRIPT_DIR/getzipfile.py $ZIPFILE > $SCRIPT_DIR/tmpupdate
if [ `diff $SCRIPT_DIR/tmpupdate $FILE_DIR/update.inc | wc -l` -eq '0' ]
then
  echo "No Changes"
  echo "---------------------------------"
   rm $SCRIPT_DIR/tmpFile
  exit 0
fi

echo "Got Zip File"
if [ ! -e $ZIPFILE ]
then
    echo "Zip file not present"
    echo "---------------------------------"
    rm $SCRIPT_DIR/tmpFile
    exit 0
fi

/usr/bin/unzip -o $ZIPFILE indstats.nfl -d $DATA_DIR
if [ "$?" -eq "$FAILURE" ]
then
    echo "Unable to unzip"
    echo "---------------------------------"
    rm $SCRIPT_DIR/tmpFile
    exit 0
fi

echo "Unzipped File"
python $SCRIPT_DIR/newcrack.py $DATA_DIR/indstats.nfl $WEEK > $DATA_DIR/out.sql
retval=$?
if [ "$retval" -eq "$FAILURE" ]
then
    echo "Stats Not Ready"
    echo "---------------------------------"
    rm $SCRIPT_DIR/tmpFile
    exit 0
fi

echo "Parsed Stats"
mysql --defaults-file=$DB_DEFAULTS < $DATA_DIR/out.sql

echo "Read into Database"
php $SCRIPT_DIR/updatescores.php

mv $SCRIPT_DIR/tmpupdate $FILE_DIR/update.inc

echo "Updated Scores"
echo "---------------------------------"

rm $SCRIPT_DIR/tmpFile
