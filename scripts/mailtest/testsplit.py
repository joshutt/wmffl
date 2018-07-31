import sys
from time import time
from whrandom import randint
from socket import gethostname
from email import *

data = ""
for line in sys.stdin :
    data = "%s%s"%(data, line)
#print data


msg = Parser.Parser().parsestr(data)

#print msg['Subject']
#print msg['From']
#print msg['To']
#print msg.keys()

specList = {}
controlFile = open("/home/joshutt/football/scripts/mailtest/controlList", "r")
lines = controlFile.readlines()
for line in lines :
    controlSpecs = line.split()
    addr = controlSpecs[0]
    folder = controlSpecs[1]
    specList[addr] = folder

controlFile.close()

folderId = ".Other"
for spec in specList :
    if (msg['To'] == spec) :
       folderId = specList[spec] 


newName = "%d.JPU%d.%s" % (int(time()), randint(1, 10000000), gethostname()) 
path = "/home/joshutt/mail/wmffl.com/josh/%s/new"%folderId


#sys.exit(0)
fp = open("%s/%s"%(path, newName), "w")
fp.write(data)
fp.close()
