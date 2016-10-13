#!/bin/sh

FILE=$1
echo $FILE

FORMAT=$2
echo $FORMAT

DATEA=`date +$FORMAT`
#DATE=`date +%Y-%m-%d`
echo $DATEA
exit

cd /home/joshutt/football/scripts/logs

gzip $FILE
mv $FILE.gz $FILE-$DATEA.gz
