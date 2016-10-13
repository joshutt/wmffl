#!/bin/bash

echo 
echo "---------------------------------"
echo "Update Players"
echo `date`
echo "---------------------------------"
php /home/wmffl/public_html/devel/updateplayers/grabfile.php
php /home/wmffl/public_html/devel/updateplayers/parsefile.php
echo "---------------------------------"
