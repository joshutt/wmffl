#!/usr/local/bin/python

import urllib, httplib

//urlhandle = urllib.urlopen("http://12273:swWR$2bo@www.wmffl.com:911")
urlhandle = urllib.urlopen("http://wmffl:dH34s3B@100megs9.com/cpanel")
lines = urlhandle.readlines()
urlhandle.close()
print "Content-type: text/html\n\n"
for line in lines :
    print line


print "Thats all"

