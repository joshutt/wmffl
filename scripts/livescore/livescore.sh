#!/bin/sh

SCRIPT_DIR='/home/joshutt/football/scripts/livescore'
DATA_DIR=$SCRIPT_DIR'/data'
FILE_DIR='/home/joshutt/football/activate'
FAILURE=1

if test -e $SCRIPT_DIR/tmpFile && `find "$SCRIPT_DIR/tmpFile" -mmin +5`
then
    exit 0
fi
touch $SCRIPT_DIR/tmpFile

echo 
echo "---------------------------------"
echo "Live Scores"
echo `date`
echo "---------------------------------"

python $SCRIPT_DIR/getzipfile.py $DATA_DIR/myzip.zip > $SCRIPT_DIR/tmpupdate
if [ `diff $SCRIPT_DIR/tmpupdate $FILE_DIR/update.inc | wc -l` -eq '0' ]
then
  echo "No Changes"
  echo "---------------------------------"
   rm $SCRIPT_DIR/tmpFile
  exit 0
fi

echo "Got Zip File"
if [ ! -e $DATA_DIR/myzip.zip ]
then
    echo "Zip file not present"
    echo "---------------------------------"
    rm $SCRIPT_DIR/tmpFile
    exit 0
fi
/usr/bin/unzip -o $DATA_DIR/myzip.zip indstats.nfl -d $DATA_DIR
if [ "$?" -eq "$FAILURE" ]
then
    echo "Unable to unzip"
    echo "---------------------------------"
    rm $SCRIPT_DIR/tmpFile
    exit 0
fi

echo "Unzipped File"
python $SCRIPT_DIR/newcrack.py $DATA_DIR/indstats.nfl > $DATA_DIR/out.sql
retval=$?
if [ "$retval" -eq "$FAILURE" ]
then
    echo "Stats Not Ready"
    echo "---------------------------------"
    rm $SCRIPT_DIR/tmpFile
    exit 0
fi

echo "Parsed Stats"
mysql --defaults-file=/home/joshutt/fb.conf joshutt_oldwmffl < $DATA_DIR/out.sql

echo "Read into Database"
php /home/joshutt/football/admin/updatescores.php

mv $SCRIPT_DIR/tmpupdate $FILE_DIR/update.inc

echo "Updated Scores"
echo "---------------------------------"

rm $SCRIPT_DIR/tmpFile
