#!/bin/sh

rootpath=/home/joshutt/football/scripts/mailtest/maildeposit

for file in "$( find $rootpath -type f )"
do
    echo ${rootpath}
    echo ${file}
    #echo $file | sed "s/${rootpath}//"
    
done
