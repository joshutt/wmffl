#!/bin/bash

echo 
echo "---------------------------------"
echo "Update Players"
echo `date`
echo "---------------------------------"
php /home/joshutt/football/scripts/updateplayers/grabfile.php
php /home/joshutt/football/scripts/updateplayers/parsefile.php
echo "---------------------------------"
