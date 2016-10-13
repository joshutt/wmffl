#!/usr/local/bin/python

import sys, cgi, urllib, httplib


def message() :
    print "Content-type: text/html\n\n"
    print """
<HTML>
<HEAD>
<TITLE>URL Front</TITLE>
</HEAD>

<BODY BGCOLOR="white">
    <FORM ACTION="newtest.cgi" METHOD="POST">
    <INPUT TYPE="Text" NAME="url">
    <INPUT TYPE="Submit" VALUE="Load URL">
    </FORM>
</BODY>
</HTML>
"""


form = cgi.FieldStorage()
if (form.has_key('url')):
	address = form['url'].value
else :
    message()
    sys.exit(0)
urlhandle = urllib.urlopen(address)
lines = urlhandle.readlines()
urlhandle.close()
print "Content-type: text/html\n\n"
for line in lines :
    print line

print "Thats all"

