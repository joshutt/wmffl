#!/bin/sh

ROOT_PATH='/home/joshutt/football/scripts/updateplayers'
cd $ROOT_PATH
date > datecheck.txt
php grabfile.php

cd newversion
php checkfile.php
RETVAL=$?

#echo $RETVAL

if [ $RETVAL -eq 1 ]  
then 
    php loadPlayers.php
    php updateNFLroster.php
    mysql -u joshutt_footbal -pwmaccess joshutt_oldwmffl < queries.sql
fi
